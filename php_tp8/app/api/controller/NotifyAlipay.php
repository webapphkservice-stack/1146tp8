<?php
namespace app\api\controller;

use app\common\controller\Controller;
use app\api\service\OrderStateMachine;
use app\api\service\PayNotifyVerifier;
use app\common\enum\order\PayType;

/**
 * 支付宝支付异步通知（第 7 批）
 * 路由：POST /api/notify/alipay（无需登录态，网关直连）
 *
 * 说明：php_tp8 未引入支付宝 aop SDK，此处为「轻量收口」实现：
 *   - 读取支付宝 form POST 参数，校验 trade_status 与 out_trade_no；
 *   - 调用订单状态机完成支付成功改单（幂等）；
 *   - 返回支付宝要求的纯文本回执（success / fail）。
 * TODO（生产接入支付宝 aop 后）：在处理前增加 $client->verifyNotifySign($data) 验签，
 *       逻辑对齐 php_my api/controller/NotifyAlipay::order()。
 */
class NotifyAlipay extends Controller
{
    // 回调为网关直连，无 wxapp_id 上下文，跳过基类 wxapp_id 校验
    public function initialize()
    {
        $this->request = request();
    }

    public function order()
    {
        $data = $this->request->post();
        if (empty($data)) {
            return $this->alipayReturn(false, '无通知参数');
        }
        $tradeNo     = $data['trade_no'] ?? '';
        $outTradeNo  = $data['out_trade_no'] ?? '';
        $tradeStatus = $data['trade_status'] ?? '';
        if (empty($outTradeNo)) {
            return $this->alipayReturn(false, '缺少商户订单号');
        }
        // 生产验签：支付宝 RSA2 签名校验（依赖支付宝公钥）
        $wxappId = PayNotifyVerifier::resolveWxappId($outTradeNo);
        if (!PayNotifyVerifier::alipay($data, $wxappId)) {
            return $this->alipayReturn(false, '签名校验失败');
        }
        // 仅处理终态；非终态（如 TRADE_CLOSED）直接返回 success，避免网关重试
        if (!in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
            return $this->alipayReturn(true);
        }
        // 充值订单（RC 前缀）走独立收口：余额到账 + 流水
        if (strpos($outTradeNo, 'RC') === 0) {
            $rp = new \app\api\service\RechargePaySuccess();
            if (!$rp->paySuccess($outTradeNo, $tradeNo)) {
                return $this->alipayReturn(false, $rp->getError() ?: '充值处理失败');
            }
            return $this->alipayReturn(true);
        }
        // VIP开通订单：识别后独立收口（开通会员 + 赠送运费券）
        if (\think\facade\Db::name('vip_order')->where('order_no', $outTradeNo)->count()) {
            $vp = new \app\api\service\VipPaySuccess();
            if (!$vp->paySuccess($outTradeNo, $tradeNo)) {
                return $this->alipayReturn(false, $vp->getError() ?: 'VIP处理失败');
            }
            return $this->alipayReturn(true);
        }
        $sm = new OrderStateMachine;
        if (!$sm->paySuccess($outTradeNo, PayType::ALIPAY, $tradeNo)) {
            return $this->alipayReturn(false, $sm->getError() ?: '处理失败');
        }
        return $this->alipayReturn(true);
    }

    /**
     * 返回支付宝要求的纯文本回执
     */
    private function alipayReturn($success, $msg = '')
    {
        $body = $success ? 'success' : 'fail' . ($msg ? ':' . $msg : '');
        return response($body)->contentType('text/plain; charset=utf-8');
    }
}
