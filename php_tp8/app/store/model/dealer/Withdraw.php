<?php

namespace app\store\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商提现明细模型(后台，自包含)
 * Class Withdraw
 * @package app\store\model\dealer
 */
class Withdraw extends BaseModel
{
    protected $name = 'dealer_withdraw';

    // 提现状态
    const AUDIT_WAIT = 10;
    const AUDIT_PASS = 20;
    const AUDIT_REJECT = 30;
    const PAID = 40;

    /**
     * 打款方式
     * @var array
     */
    private static $payType = [
        10 => '微信',
        20 => '支付宝',
        30 => '银行卡',
    ];

    /**
     * 关联分销商用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\dealer\User', 'user_id', 'user_id');
    }

    /**
     * 获取器：打款方式
     * @param $value
     * @return mixed
     */
    public function getPayTypeAttr($value)
    {
        return ['text' => self::$payType[$value] ?? '未知', 'value' => $value];
    }

    /**
     * 获取器：审核时间
     * @param $value
     * @return false|string
     */
    public function getAuditTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : 0;
    }

    /**
     * 获取提现列表
     * @param null $userId
     * @param int $apply_status
     * @param int $pay_type
     * @param string $search
     * @return \think\Paginator
     */
    public function getList($userId = null, $apply_status = -1, $pay_type = -1, $search = '')
    {
        $this->withoutGlobalScope()
            ->alias('withdraw')
            ->field('withdraw.*, dealer.real_name, dealer.mobile, user.nickName, user.avatarUrl')
            ->join('user', 'user.user_id = withdraw.user_id')
            ->join('dealer_user dealer', 'dealer.user_id = withdraw.user_id')
            ->where('withdraw.wxapp_id', '=', self::getWxappId())
            ->where('dealer.wxapp_id', '=', self::getWxappId())
            ->order(['withdraw.create_time' => 'desc']);
        $userId > 0 && $this->where('withdraw.user_id', '=', $userId);
        !empty($search) && $this->where('dealer.real_name|dealer.mobile', 'like', "%$search%");
        $apply_status > 0 && $this->where('withdraw.apply_status', '=', $apply_status);
        $pay_type > 0 && $this->where('withdraw.pay_type', '=', $pay_type);
        return $this->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 详情
     * @param $id
     * @return null|\think\Model
     */
    public static function detail($id)
    {
        return static::with(['user'])->find($id);
    }

    /**
     * 提现审核
     * @param $data
     * @return bool
     */
    public function submit($data)
    {
        if ($data['apply_status'] == self::AUDIT_REJECT && empty($data['reject_reason'])) {
            $this->error = '请填写驳回原因';
            return false;
        }
        $this->transaction(function () use ($data) {
            $data['audit_time'] = time();
            $this->save($data);
            if ($data['apply_status'] == self::AUDIT_REJECT) {
                User::backFreezeMoney($this['user_id'], $this['money']);
            }
        });
        return true;
    }

    /**
     * 确认已打款
     * @param bool $verifyUserFreezeMoney
     * @return bool
     */
    public function money($verifyUserFreezeMoney = true)
    {
        if ($verifyUserFreezeMoney && !$this->verifyUserFreezeMoney($this['user_id'], $this['money'])) {
            return false;
        }
        return $this->transaction(function () {
            $this->save([
                'apply_status' => self::PAID,
                'audit_time' => time(),
            ]);
            User::totalMoney($this['user_id'], $this['money']);
            Capital::add([
                'user_id' => $this['user_id'],
                'flow_type' => 20,
                'money' => -$this['money'],
                'describe' => '申请提现',
            ]);
            return true;
        });
    }

    /**
     * 微信V2企业付款(已废弃，降级处理)
     * @return bool
     */
    public function wechatPay()
    {
        if (!$this->verifyUserFreezeMoney($this['user_id'], $this['money'])) {
            return false;
        }
        $this->error = '微信V2企业付款已废弃，请使用V3接口';
        return false;
    }

    /**
     * 微信V3商家转账到零钱(依赖 WxTransferV3，TP8 缺失则降级)
     * @return bool
     */
    public function wechatPayV3()
    {
        if (!$this->verifyUserFreezeMoney($this['user_id'], $this['money'])) {
            return false;
        }
        if (!class_exists('app\common\library\wechat\WxTransferV3')) {
            $this->error = '微信V3转账服务未配置，无法发起转账';
            return false;
        }
        try {
            $wxConfig = \app\common\model\Wxapp::getWxappCache(self::getWxappId());
            $user = $this['user']['user'] ?? null;
            if (!$user || empty($user['open_id'])) {
                $this->error = '分销商微信信息缺失';
                return false;
            }
            $outBillNo = 'TX' . $this['withdraw_id'] . date('YmdHis') . mt_rand(1000, 9999);
            $wxTransfer = new \app\common\library\wechat\WxTransferV3($wxConfig);
            $result = $wxTransfer->transfer(
                $outBillNo,
                $user['open_id'],
                $this['money'],
                '',
                '分销商提现',
                '分销商提现'
            );
            if (!empty($result['success'])) {
                $this->save([
                    'wx_bill_id' => $result['bill_id'] ?? '',
                    'wx_out_bill_no' => $result['out_bill_no'] ?? $outBillNo,
                    'wx_transfer_state' => $result['state'] ?? '',
                ]);
                $this->money(false);
                return true;
            }
        } catch (\Exception $e) {
            $this->error = '微信转账失败：' . $e->getMessage();
            return false;
        }
        return false;
    }

    /**
     * 查询微信转账状态
     * @return array
     */
    public function queryWechatTransfer()
    {
        if (empty($this['wx_out_bill_no'])) {
            return ['error' => '未找到微信转账单号'];
        }
        if (!class_exists('app\common\library\wechat\WxTransferV3')) {
            return ['error' => '微信V3转账服务未配置'];
        }
        try {
            $wxConfig = \app\common\model\Wxapp::getWxappCache(self::getWxappId());
            $wxTransfer = new \app\common\library\wechat\WxTransferV3($wxConfig);
            $result = $wxTransfer->queryTransfer($this['wx_out_bill_no']);
            $this->save(['wx_transfer_state' => $result['state'] ?? '']);
            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 验证已冻结佣金是否合法
     * @param $userId
     * @param $money
     * @return bool
     */
    public function verifyUserFreezeMoney($userId, $money)
    {
        $dealerUserInfo = User::detail($userId);
        if (empty($dealerUserInfo) || $dealerUserInfo['freeze_money'] < $money) {
            $this->error = '数据错误：已冻结的佣金不能小于提现的金额';
            return false;
        }
        return true;
    }

}
