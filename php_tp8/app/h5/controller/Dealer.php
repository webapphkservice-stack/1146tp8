<?php
namespace app\h5\controller;

use app\api\model\dealer\Setting as SettingModel;
use app\api\model\dealer\User as DealerUser;
use app\api\model\dealer\Apply as DealerApply;
use app\api\model\dealer\Withdraw as DealerWithdraw;
use app\api\model\dealer\Referee as DealerReferee;
use app\api\model\dealer\Order as DealerOrder;
use think\facade\Db;

/**
 * H5 - 分销中心（对齐小程序 pages/dealer/*）
 *
 * 路由：
 *   /h5/dealer/index.html          分销中心（未成为分销商则引导申请）
 *   /h5/dealer/apply.html          申请成为分销商（表单 / 审核中）
 *   /h5/dealer/applySubmit.html    提交申请（POST）
 *   /h5/dealer/team.html           我的团队
 *   /h5/dealer/order.html          分销订单
 *   /h5/dealer/withdraw.html       提现明细
 *   /h5/dealer/withdrawApply.html  申请提现（表单）
 *   /h5/dealer/withdrawSubmit.html 提交提现（POST）
 *   /h5/dealer/qrcode.html         推广二维码 / 分享链接
 */
class Dealer extends Controller
{
    /** 分销中心 */
    public function index()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user   = $this->currentUser();
        $dealer = DealerUser::detail($user['user_id']);

        return $this->fetch('dealer/index', [
            'storeName'   => $this->storeName(),
            'user'        => $user,
            'isDealer'    => !empty($dealer),
            'dealer'      => $dealer,
            'refereeName' => $dealer ? $this->refereeName($dealer['referee_id']) : '',
            'words'       => SettingModel::getItem('words'),
            'background'  => (string)(SettingModel::getItem('background')['index'] ?? ''),
        ]);
    }

    /** 申请页：表单 / 审核中 */
    public function apply()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user   = $this->currentUser();
        $dealer = DealerUser::detail($user['user_id']);

        // 携带 referee_id（分享链接进入）优先展示邀请人
        $refereeId = (int)$this->request->param('referee_id', 0);
        if ($refereeId <= 0) {
            $refereeId = (int)Db::name('dealer_referee')
                ->where('user_id', $user['user_id'])
                ->where('wxapp_id', self::$wxapp_id)
                ->value('dealer_id');
        }

        return $this->fetch('dealer/apply', [
            'storeName'     => $this->storeName(),
            'user'          => $user,
            'isDealer'      => !empty($dealer),
            'isApplying'    => DealerApply::isApplying($user['user_id']),
            'refereeName'   => $this->refereeName($refereeId),
            'refereeId'     => $refereeId,
            'words'         => SettingModel::getItem('words'),
            'condition'     => SettingModel::getItem('condition'),
            'license'       => (string)(SettingModel::getItem('license')['license'] ?? ''),
            'background'    => (string)(SettingModel::getItem('background')['apply'] ?? ''),
        ]);
    }

    /** 提交申请（POST，返回 JSON） */
    public function applySubmit()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }
        $user   = $this->h5User();
        $name   = trim((string)$this->request->post('name', ''));
        $mobile = trim((string)$this->request->post('mobile', ''));

        if ($name === '' || $mobile === '') {
            return json(['code' => 0, 'msg' => '请填写姓名和手机号']);
        }
        if (!preg_match('/^1\d{10}$/', $mobile)) {
            return json(['code' => 0, 'msg' => '请输入正确的 11 位手机号']);
        }
        if (DealerApply::submit($user, $name, $mobile)) {
            return json(['code' => 1, 'msg' => '提交成功', 'data' => ['redirect' => '/h5/dealer/apply.html']]);
        }
        return json(['code' => 0, 'msg' => '提交失败，请稍后再试']);
    }

    /** 我的团队 */
    public function team()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user   = $this->currentUser();
        $dealer = DealerUser::detail($user['user_id']);
        $level  = (int)$this->request->param('level', -1);

        return $this->fetch('dealer/team', [
            'storeName' => $this->storeName(),
            'isDealer'  => !empty($dealer),
            'dealer'    => $dealer,
            'level'     => $level,
            'list'      => $dealer ? DealerReferee::getList($user['user_id'], $level) : [],
            'words'     => SettingModel::getItem('words'),
        ]);
    }

    /** 分销订单 */
    public function order()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user    = $this->currentUser();
        $dealer  = DealerUser::detail($user['user_id']);
        $settled = (int)$this->request->param('settled', -1);

        return $this->fetch('dealer/order', [
            'storeName' => $this->storeName(),
            'isDealer'  => !empty($dealer),
            'settled'   => $settled,
            'myId'      => (int)$user['user_id'],
            'list'      => $dealer ? DealerOrder::getList($user['user_id'], $settled) : [],
            'words'     => SettingModel::getItem('words'),
        ]);
    }

    /** 提现明细 */
    public function withdraw()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user   = $this->currentUser();
        $dealer = DealerUser::detail($user['user_id']);
        $status = (int)$this->request->param('status', -1);

        return $this->fetch('dealer/withdraw', [
            'storeName' => $this->storeName(),
            'isDealer'  => !empty($dealer),
            'dealer'    => $dealer,
            'status'    => $status,
            'list'      => $dealer ? DealerWithdraw::getList($user['user_id'], $status) : [],
            'words'     => SettingModel::getItem('words'),
        ]);
    }

    /** 申请提现（表单） */
    public function withdrawApply()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user   = $this->currentUser();
        $dealer = DealerUser::detail($user['user_id']);

        return $this->fetch('dealer/withdrawApply', [
            'storeName'  => $this->storeName(),
            'isDealer'   => !empty($dealer),
            'dealer'     => $dealer,
            'setting'    => SettingModel::getItem('settlement'),
            'words'      => SettingModel::getItem('words'),
            'background' => (string)(SettingModel::getItem('background')['withdraw_apply'] ?? ''),
        ]);
    }

    /** 提交提现（POST，返回 JSON） */
    public function withdrawSubmit()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        if (!$this->request->isPost()) {
            return json(['code' => 0, 'msg' => '请求方式错误']);
        }
        $user   = $this->h5User();
        $dealer = DealerUser::detail($user['user_id']);
        if (empty($dealer)) {
            return json(['code' => 0, 'msg' => '您还不是分销商']);
        }

        $money = (float)$this->request->post('money', 0);
        if ($money <= 0) {
            return json(['code' => 0, 'msg' => '请输入正确的提现金额']);
        }
        if ($money > (float)$dealer['money']) {
            return json(['code' => 0, 'msg' => '提现金额不能大于可提现佣金']);
        }
        $setting  = SettingModel::getItem('settlement');
        $minMoney = (float)($setting['min_money'] ?? 0);
        if ($money < $minMoney) {
            return json(['code' => 0, 'msg' => '提现金额不能小于最低提现金额 ' . $minMoney]);
        }

        $payTypes = $setting['pay_type'] ?? [];
        $payType  = (int)$this->request->post('pay_type', 10);
        if (!empty($payTypes) && !in_array((string)$payType, array_map('strval', $payTypes), true)) {
            return json(['code' => 0, 'msg' => '提现方式不正确']);
        }

        $formData = [
            'money'         => $money,
            'pay_type'      => $payType,
            'alipay_name'   => trim((string)$this->request->post('alipay_name', '')),
            'alipay_account'=> trim((string)$this->request->post('alipay_account', '')),
            'bank_name'     => trim((string)$this->request->post('bank_name', '')),
            'bank_account'  => trim((string)$this->request->post('bank_account', '')),
            'bank_card'     => trim((string)$this->request->post('bank_card', '')),
        ];

        if (DealerWithdraw::submit($dealer, $formData)) {
            return json(['code' => 1, 'msg' => '提交成功', 'data' => ['redirect' => '/h5/dealer/withdraw.html']]);
        }
        return json(['code' => 0, 'msg' => '提交失败，请稍后再试']);
    }

    /** 推广二维码 / 分享链接 */
    public function qrcode()
    {
        if ($resp = $this->requireLogin()) {
            return $resp;
        }
        $user = $this->currentUser();

        return $this->fetch('dealer/qrcode', [
            'storeName' => $this->storeName(),
            'user'      => $user,
            // H5 分享链接：好友打开后进入申请页并自动带上邀请人
            'shareUrl'  => (string)url('dealer/apply', ['referee_id' => $user['user_id']]),
            'words'     => SettingModel::getItem('words'),
        ]);
    }

    // ==================== 内部工具 ====================

    /** 当前登录用户（合并 Cache 登录信息 + user 表昵称/头像） */
    private function currentUser(): array
    {
        $info = $this->h5User();
        $row  = Db::name('user')->where('user_id', $info['user_id'])->find();
        return [
            'user_id'    => (int)$info['user_id'],
            'nickName'   => $row['nickName'] ?? ($info['user_name'] ?? ''),
            'avatarUrl'  => $this->avatarUrl($row['avatarUrl'] ?? ''),
            'mobile'     => $row['mobile'] ?? ($info['mobile'] ?? ''),
        ];
    }

    /** 推荐人昵称（0 表示平台） */
    private function refereeName(int $refereeId): string
    {
        if ($refereeId <= 0) {
            return '平台';
        }
        $nick = Db::name('user')->where('user_id', $refereeId)->value('nickName');
        return $nick ?: ('用户' . $refereeId);
    }

    /** 头像相对路径 → 可访问 URL */
    private function avatarUrl(string $path): string
    {
        if ($path === '' || stripos($path, 'http') === 0) {
            return $path;
        }
        return '/' . ltrim($path, '/');
    }
}