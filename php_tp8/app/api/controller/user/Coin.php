<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\CoinOrder;
use app\api\model\RechargePlan as PlanModel;
use app\api\service\VirtualPayDeliver;
use app\common\service\wechat\VirtualPayment;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Log;

/**
 * 代币充值（微信小程序虚拟支付 currency 模式）
 * 用户端调起 wx.requestVirtualPayment({mode:'currency'}) 充值代币，
 * 回调后累加 user.coin；后续可用代币开通 VIP。
 * 移植自 TP5：application/api/controller/user/Coin.php
 * 路由：GET /api/user.coin/index、POST /api/user.coin/buy、GET /api/user.coin/order（需登录）
 */
class Coin extends Controller
{
    /**
     * 代币充值套餐列表（复用 recharge_plan）
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function index()
    {
        $this->getUser(true);
        $plans = PlanModel::getList();
        return $this->renderSuccess(['plans' => $plans]);
    }

    /**
     * 创建代币充值订单并返回虚拟支付签名参数（mode:'currency'）
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function buy()
    {
        $user   = $this->getUser(true);
        $planId = $this->request->post('plan_id');
        // 客户端平台：android / ios（必须与实际设备一致）
        $platform = $this->request->post('platform', 'android');

        $plan = PlanModel::where('plan_id', (int)$planId)
            ->where('is_delete', 0)
            ->find();
        if (!$plan) {
            return $this->renderError('套餐不存在');
        }
        $plan = $plan->toArray();

        $coinOrder = new CoinOrder;
        $orderNo   = 'CO' . date('YmdHis') . substr(uniqid(), -6);   // 前缀 CO，便于回调区分
        $coinOrder->save([
            'order_no'    => $orderNo,
            'user_id'     => $user['user_id'],
            'plan_id'     => $plan['plan_id'],
            'coin_num'    => $plan['money'],
            'money'       => $plan['money'],
            'pay_status'  => 10,
            'pay_type'    => 60,
            'wxapp_id'    => $this->getWxappId(),
            'create_time' => time(),
        ]);

        // 取用户 session_key（虚拟支付签名必需，登录时缓存于 token 对应的会话中）
        $token      = $this->request->param('token');
        $session    = $token ? Cache::get($token) : null;
        $sessionKey = is_array($session) ? ($session['session_key'] ?? '') : '';

        $virtual  = new VirtualPayment($this->getWxappId());
        $synthesizedPlan = [
            'plan_id'   => $plan['plan_id'],
            'plan_name' => $plan['plan_name'] ?? ('代币' . $plan['money']),
            'price'     => $plan['money'],
        ];

        try {
            $payment = $virtual->buildClientParams(
                $user,
                $synthesizedPlan,
                $orderNo,
                $platform,
                $sessionKey,
                'currency',
                'coin_'
            );
        } catch (\app\common\exception\BaseException $e) {
            return $this->renderError($e->getMessage());
        }

        return $this->renderSuccess(['payment' => $payment], '下单成功');
    }

    /**
     * 我的代币充值记录
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function order()
    {
        $user = $this->getUser(true);
        $list = CoinOrder::where('user_id', '=', $user['user_id'])
            ->where('pay_status', '=', 20)
            ->order('create_time', 'desc')
            ->select();
        return $this->renderSuccess(['list' => $list]);
    }

    /**
     * 代币充值结果主动查询确认（前端调起 wx.requestVirtualPayment({mode:'currency'}) 成功后轮询）
     *
     * 与 Notify::virtual 回调共用 VirtualPayDeliver::handleCoin()，
     * 保证「微信主动推」和「前端主动查」两条路径的金额核对、幂等、状态更新完全一致。
     *
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function confirmPay()
    {
        $user    = $this->getUser(true);
        $orderNo = $this->request->param('order_no', '');
        if ($orderNo === '') {
            return $this->renderError('缺少订单号');
        }

        // 1. 订单归属校验（防越权）
        $order = Db::name('coin_order')->where('order_no', $orderNo)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['user_id'] !== (int)$user['user_id']) {
            return $this->renderError('无权操作此订单');
        }

        // 2. 幂等：已充值直接返回
        if ((int)$order['pay_status'] === 20) {
            return $this->renderSuccess(['paid' => true], '已支付');
        }

        // 3. 取用户 openid + session_key（微信 xpay/query_order 必需）
        $openid = Db::name('user')->where('user_id', $user['user_id'])->value('open_id') ?: '';
        $token  = $this->request->param('token');
        $sess   = $token ? Cache::get($token) : null;
        $skey   = is_array($sess) ? ($sess['session_key'] ?? '') : '';
        if ($openid === '' || $skey === '') {
            return $this->renderError('用户登录态已失效，请重新登录');
        }

        try {
            $wxQuery = (new VirtualPayment((int)$order['wxapp_id']))
                ->queryOrder($openid, $orderNo);
        } catch (\Throwable $e) {
            Log::write('[confirmCoin] queryOrder 异常：' . $e->getMessage(), 'error');
            return $this->renderError('查询微信支付状态失败');
        }

        // xpay/query_order 返回结构：{ code, msg, data: { order_id, pay_state, ... } }
        $payState = $wxQuery['data']['pay_state'] ?? null;
        if ($payState !== 2) {
            return $this->renderSuccess([
                'paid'      => false,
                'pay_state' => $payState,
                'msg'       => $wxQuery['msg'] ?? '尚未支付',
            ]);
        }

        // 4. 微信确认已支付 → 统一充值发货
        $transactionId = $wxQuery['data']['wx_order_id'] ?? '';
        $paidAmount    = (int)($wxQuery['data']['pay_price'] ?? 0);
        [$ok, $msg] = (new VirtualPayDeliver)->handleCoin($orderNo, $transactionId, $paidAmount);
        if (!$ok) {
            return $this->renderError($msg ?: '代币充值失败');
        }
        return $this->renderSuccess([
            'paid'    => true,
            'coin'    => (int)$order['coin_num'],
        ], '充值成功');
    }
}
