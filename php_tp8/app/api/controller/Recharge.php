<?php
namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\RechargePlan;
use app\api\model\RechargeOrder;
use app\api\service\Payment;
use app\common\enum\order\PayType;
use app\common\model\Setting;

/**
 * 用户充值（第 8 批）
 * 路由：GET /api/recharge/index、POST /api/recharge/submit（需登录）
 */
class Recharge extends Controller
{
    public function index()
    {
        $user = $this->getUser(true);
        $setting = Setting::getItem('recharge');
        $planList = RechargePlan::getList();
        return $this->renderSuccess([
            'setting'   => ['is_open' => !empty($setting['is_open'])],
            'plan_list' => $planList,
            'user'      => [
                'balance' => (float)$user['balance'],
                'user_id' => $user['user_id'],
            ],
        ]);
    }

    public function submit()
    {
        $user = $this->getUser(true);
        $planId = (int)$this->request->param('planId', 0);
        $customMoney = $this->request->param('customMoney', 0);
        $order = RechargeOrder::createOrder($user, $planId, $customMoney);
        if (isset($order['error'])) {
            return $this->renderError($order['error']);
        }
        $payType   = (int)$this->request->param('payType', PayType::WECHAT);
        $platform  = trim((string)$this->request->param('platform', ''));
        $redirect  = trim((string)$this->request->param('redirect_url', ''));
        $payInfo = (new Payment())->getParams($payType, $order, $user, null, $platform, $redirect);
        return $this->renderSuccess($payInfo);
    }
}
