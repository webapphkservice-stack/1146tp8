<?php
namespace app\api\controller;

use app\common\controller\Controller;
use app\api\service\OrderStateMachine;
use app\api\service\PayNotifyVerifier;
use app\common\enum\order\PayType;

/**
 * Stripe 支付 Webhook 通知（第 7 批）
 * 路由：POST /api/notify/stripe（无需登录态，网关直连）
 *
 * 说明：php_tp8 未引入 Stripe PHP SDK，此处为「轻量收口」实现：
 *   - 解析 Stripe Webhook JSON 事件，识别 payment_intent.succeeded；
 *   - 从 metadata.order_no 取商户订单号，调用订单状态机完成支付成功改单（幂等）；
 *   - 成功返回 200，失败返回 400（JSON）。
 * TODO（生产接入 Stripe 后）：在处理前增加 \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret) 验签。
 */
class NotifyStripe extends Controller
{
    // 回调为网关直连，无 wxapp_id 上下文，跳过基类 wxapp_id 校验
    public function initialize()
    {
        $this->request = request();
    }

    public function order()
    {
        $payload = $this->request->getInput();
        $sigHeader = $this->request->header('Stripe-Signature');
        // 先解析以定位 wxapp_id / 订单号
        $peek = json_decode($payload, true);
        $peekObj = $peek['data']['object'] ?? [];
        $orderNo = $peekObj['metadata']['order_no'] ?? $peekObj['metadata']['out_trade_no'] ?? '';
        $wxappId = $orderNo ? PayNotifyVerifier::resolveWxappId($orderNo) : 10001;
        $secret = PayNotifyVerifier::getStripeSecret($wxappId);
        // 生产验签：Stripe Webhook HMAC 校验（依赖 stripe-php SDK）
        try {
            $event = PayNotifyVerifier::stripe($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return $this->stripeReturn(400, '签名校验失败');
        }
        $event = json_decode(json_encode($event), true);
        if (empty($event) || empty($event['type'])) {
            return $this->stripeReturn(400, '无效事件');
        }
        // 仅处理支付成功事件；其余事件（如 payment_intent.created）正常返回 200
        if ($event['type'] !== 'payment_intent.succeeded') {
            return $this->stripeReturn(200, '忽略事件');
        }
        $object = $event['data']['object'] ?? [];
        $orderNo = $object['metadata']['order_no']
            ?? $object['metadata']['out_trade_no']
            ?? '';
        $transactionId = $object['id'] ?? '';
        if (empty($orderNo)) {
            return $this->stripeReturn(400, '缺少订单号');
        }
        // 充值订单（RC 前缀）走独立收口：余额到账 + 流水
        if (strpos($orderNo, 'RC') === 0) {
            $rp = new \app\api\service\RechargePaySuccess();
            if (!$rp->paySuccess($orderNo, $transactionId)) {
                return $this->stripeReturn(400, $rp->getError() ?: '充值处理失败');
            }
            return $this->stripeReturn(200, 'OK');
        }
        // VIP开通订单：识别后独立收口（开通会员 + 赠送运费券）
        if (\think\facade\Db::name('vip_order')->where('order_no', $orderNo)->count()) {
            $vp = new \app\api\service\VipPaySuccess();
            if (!$vp->paySuccess($orderNo, $transactionId)) {
                return $this->stripeReturn(400, $vp->getError() ?: 'VIP处理失败');
            }
            return $this->stripeReturn(200, 'OK');
        }
        $sm = new OrderStateMachine;
        if (!$sm->paySuccess($orderNo, PayType::STRIPE, $transactionId)) {
            return $this->stripeReturn(400, $sm->getError() ?: '处理失败');
        }
        return $this->stripeReturn(200, 'OK');
    }

    /**
     * 返回 Stripe Webhook 响应
     */
    private function stripeReturn($code, $msg = '')
    {
        $body = $code === 200 ? '{}' : json_encode(['error' => $msg]);
        return response($body, $code)->contentType('application/json; charset=utf-8');
    }
}
