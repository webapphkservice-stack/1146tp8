<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销提现模型（轻量版，直查 dealer_withdraw）
 */
class Withdraw
{
    /**
     * 提交提现（冻结佣金 + 写明细）
     * @param array $dealer dealer_user 行（含 user_id / money）
     * @param array $formData
     * @return bool
     */
    public static function submit($dealer, $formData)
    {
        $wxappId = BaseModel::getWxappId();
        $money = (float)($formData['money'] ?? 0);
        $payType = (int)($formData['pay_type'] ?? 10);
        $data = [
            'user_id'         => $dealer['user_id'],
            'money'           => $money,
            'pay_type'        => $payType,
            'alipay_name'     => $formData['alipay']['name'] ?? ($formData['alipay_name'] ?? ''),
            'alipay_account'  => $formData['alipay']['account'] ?? ($formData['alipay_account'] ?? ''),
            'bank_name'       => $formData['bank']['name'] ?? ($formData['bank_name'] ?? ''),
            'bank_account'    => $formData['bank']['account'] ?? ($formData['bank_account'] ?? ''),
            'bank_card'       => $formData['bank']['card'] ?? ($formData['bank_card'] ?? ''),
            'apply_status'    => 10,
            'wxapp_id'        => $wxappId,
            'create_time'     => time(),
            'update_time'     => time(),
        ];
        return (bool)Db::transaction(function () use ($data, $dealer, $money) {
            Db::name('dealer_withdraw')->insertGetId($data);
            Db::name('dealer_user')->where('user_id', $dealer['user_id'])
                ->inc('freeze_money', $money)->dec('money', $money)->update();
            return true;
        });
    }

    /**
     * 提现明细列表
     * @param int  $userId
     * @param int  $status -1=全部 10/20/30/40
     * @return array
     */
    public static function getList($userId, $status = -1)
    {
        $q = Db::name('dealer_withdraw')
            ->where('user_id', $userId)
            ->where('wxapp_id', BaseModel::getWxappId());
        if (is_numeric($status) && $status != -1) {
            $q->where('apply_status', $status);
        }
        $rows = $q->order('create_time', 'desc')->select()->toArray();
        foreach ($rows as &$r) {
            $r['money'] = (float)$r['money'];
            $r['apply_status_text'] = self::statusText($r['apply_status']);
        }
        return $rows;
    }

    private static function statusText($s)
    {
        $map = [10 => '审核中', 20 => '审核通过', 30 => '驳回', 40 => '已打款'];
        return $map[$s] ?? '';
    }
}
