<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\Vip as VipModel;
use app\common\model\VipOrder;
use app\common\model\Setting;
use app\common\enum\order\PayType;
use think\facade\Db;

/**
 * VIP会员
 * Class Vip
 * @package app\api\controller\user
 */
class Vip extends Controller
{
    /**
     * VIP会员中心
     * @return array
     */
    public function index()
    {
        $user = $this->getUser(true);
        $model = new VipModel;
        return $this->renderSuccess([
            // 用户信息
            'userInfo' => $user,
            // 会员套餐
            'plans'    => $model->getPlans(),
            // 会员状态
            'vip'      => $model->getStatus($user),
            // 会员设置(会员日/双倍积分/退货包运费等)
            'setting'  => Setting::getItem('vip'),
        ]);
    }

    /**
     * 开通/续费VIP（余额支付 / 微信支付 / 支付宝支付 / Stripe / 虚拟支付）
     * @return array
     */
    public function buy()
    {
        $user = $this->getUser(true);
        $planId   = $this->request->post('plan_id');
        $payType  = $this->request->post('pay_type', PayType::ALIPAY);
        $platform = $this->request->post('platform', 'android'); // 虚拟支付设备平台 / H5 走 'h5'
        $redirect = $this->request->post('redirect_url', '');    // MWEB 回跳 URL
        $model = new VipModel;
        $result = $model->buy($user, $planId, $payType, $platform, $redirect);
        // 失败
        if ($result === false || is_string($result)) {
            return $this->renderError(is_string($result) ? $result : '操作失败');
        }
        // 余额支付：已同步开通成功
        if ($result === true) {
            return $this->renderSuccess([
                'vip' => $model->getStatus($user),
            ], '开通成功');
        }
        // 第三方支付：返回支付参数，由前端调起收银台
        return $this->renderSuccess([
            'payment' => $result,
        ], '下单成功');
    }

    /**
     * 开通记录
     * @return array
     */
    public function order()
    {
        $user = $this->getUser(true);
        $model = new VipOrder;
        return $this->renderSuccess([
            'list' => $model->getListByUser($user['user_id']),
        ]);
    }

    /**
     * 虚拟支付结果主动查询确认（前端调起 wx.requestVirtualPayment 成功后轮询此接口）
     *
     * 逻辑：调微信 xpay/query_order 查订单状态 → 已支付则 VirtualPayDeliver 开通 VIP
     * 与 Notify::virtual 回调共用同一套发货逻辑，保证两条路径结果一致。
     *
     * @return array
     */
    public function confirmPay()
    {
        $user    = $this->getUser(true);
        $orderNo = $this->request->param('order_no', '');
        if ($orderNo === '') {
            return $this->renderError('缺少订单号');
        }

        // 1. 订单归属校验（防越权：用户 A 不能确认用户 B 的订单）
        $order = VipOrder::where('order_no', $orderNo)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['user_id'] !== (int)$user['user_id']) {
            return $this->renderError('无权操作此订单');
        }

        // 2. 幂等：已开通直接返回成功
        if ((int)$order['pay_status'] === 20) {
            return $this->renderSuccess(['paid' => true], '已支付');
        }

        // 3. 微信侧查询（需要 openid + session_key，用户态签名必需）
        $openid = Db::name('user')->where('user_id', $user['user_id'])->value('open_id') ?: '';
        $token  = $this->request->param('token');
        $sess   = $token ? \think\facade\Cache::get($token) : null;
        $skey   = is_array($sess) ? ($sess['session_key'] ?? '') : '';
        if ($openid === '' || $skey === '') {
            return $this->renderError('用户登录态已失效，请重新登录');
        }

        try {
            $wxQuery = (new \app\common\service\wechat\VirtualPayment($order['wxapp_id']))
                ->queryOrder($openid, $orderNo);
        } catch (\Throwable $e) {
            \think\facade\Log::write('[confirmVip] queryOrder 异常：' . $e->getMessage(), 'error');
            return $this->renderError('查询微信支付状态失败');
        }
        // xpay/query_order 返回结构：{ code, msg, data: { order_id, pay_state, ... } }
        $payState = $wxQuery['data']['pay_state'] ?? null;
        if ($payState !== 2) {
            // 2 = 已支付；其余状态（0=未支付/1=已创建）返回未成功
            return $this->renderSuccess([
                'paid'     => false,
                'pay_state' => $payState,
                'msg'      => $wxQuery['msg'] ?? '尚未支付',
            ]);
        }

        // 4. 微信确认已支付 → 统一发货
        $transactionId = $wxQuery['data']['wx_order_id'] ?? '';
        $paidAmount    = (int)($wxQuery['data']['pay_price'] ?? 0);
        [$ok, $msg] = (new \app\api\service\VirtualPayDeliver)->handleVip($orderNo, $transactionId, $paidAmount);
        if (!$ok) {
            return $this->renderError($msg ?: '开通失败');
        }
        return $this->renderSuccess(['paid' => true], '开通成功');
    }
}
