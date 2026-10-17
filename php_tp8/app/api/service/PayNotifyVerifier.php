<?php
namespace app\api\service;

use think\facade\Db;
use app\common\library\pay\PayConfig;
use app\common\library\pay\WxPay;
use app\common\library\pay\WxPayV3;
use app\common\library\pay\AlipayTrade;
use app\common\library\pay\AlipayClient;
use app\common\library\pay\StripeClient;

/**
 * 第三方支付异步通知验签收口（P0：全部切换为原生验签，零 SDK 依赖）
 *
 * 三个网关统一在此完成「签名校验」，校验通过后再交给订单状态机 / 充值收口处理：
 *  - 微信支付 v2：MD5 签名校验（WxPay::verifyNotify，与统一下单共用同一套签名算法）
 *  - 支付宝：RSA2 验签（AlipayClient::verifyNotifySign）
 *  - Stripe：HMAC-SHA256 验签（StripeClient::verifyWebhookSignature，等价于官方 SDK 的
 *    \Stripe\Webhook::constructEvent，含时间戳容差防重放）
 *
 * 与 php_my 的算法对齐：
 *  - 微信：common/library/wechat/WxPay::notify() -> makeSign()
 *  - 支付宝：common/library/alipay/AlipayClient::verifyNotifySign()
 *  - Stripe：common/library/stripe/StripeClient.php（新增原生 Webhook 验签）
 */
class PayNotifyVerifier
{
    /**
     * 微信支付 v2 异步通知验签
     * @param array $data    解析后的通知数组（含 sign）
     * @param int   $wxappId 用于定位 API key
     * @return bool
     */
    public static function wxPay(array $data, $wxappId)
    {
        $config = PayConfig::wechat((int)$wxappId);
        if (empty($config['apikey'])) {
            return false;
        }
        return (new WxPay($config))->verifyNotify($data);
    }

    /**
     * 微信支付 v3 异步通知：验签 + 解密业务数据
     *
     * v3 回调体为 JSON：
     *   {id, create_time, event_type, resource_type, resource:{algorithm, ciphertext, associated_data, nonce}}
     * 处理顺序：
     *   1) 用「平台证书公钥 / 微信支付公钥」校验 Wechatpay-Signature（含 5 分钟防重放）
     *   2) 用 APIv3 密钥 AES-256-GCM 解密 resource，得到交易明文
     *
     * @param array  $headers 请求头（Wechatpay-Serial/Signature/Timestamp/Nonce）
     * @param string $body    原始请求体（必须取 php://input 原文，不能重新编码）
     * @param int    $wxappId
     * @return array|false 失败返回 false；成功返回解密后的交易数组（out_trade_no/transaction_id/trade_state 等）
     */
    public static function wechatV3(array $headers, $body, $wxappId)
    {
        if (!PayConfig::wechatV3Ready((int)$wxappId)) {
            return false;
        }
        try {
            $wxPay = new WxPayV3(PayConfig::wechatV3((int)$wxappId));
            if (!$wxPay->notifyVerify($headers, (string)$body)) {
                return false;
            }
            $payload = json_decode((string)$body, true);
            if (!is_array($payload) || empty($payload['resource'])) {
                return false;
            }
            $data = $wxPay->notifyDecrypt($payload['resource']);
            if (empty($data['out_trade_no'])) {
                return false;
            }
            return $data;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * 支付宝异步通知验签（RSA2）
     * 优先官方 EasySDK 验签，失败回退原生实现，避免单条链路异常导致回调整体不可用。
     * @param array $params
     * @param int   $wxappId
     * @return bool
     */
    public static function alipay(array $params, $wxappId)
    {
        if (empty($params['sign'])) {
            return false;
        }
        $config = PayConfig::alipay((int)$wxappId);
        if (empty($config['app_id']) || empty($config['public_key'])) {
            return false;
        }
        try {
            if ((new AlipayTrade($config))->verifyNotify($params)) {
                return true;
            }
        } catch (\Throwable $e) {
            // EasySDK 验签异常，继续回退原生验签
        }
        try {
            return (new AlipayClient($config))->verifyNotifySign($params);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Stripe Webhook 验签（原生 HMAC-SHA256，无需 stripe-php SDK）
     * @param string $payload    原始 body
     * @param string $sigHeader  Stripe-Signature 头
     * @param string $secret     webhook signing secret（按 wxapp_id 取）
     * @return array 校验通过返回解析后的事件数组
     * @throws \Exception 验签失败时抛出（控制器捕获后返回 400）
     */
    public static function stripe($payload, $sigHeader, $secret)
    {
        $config = PayConfig::stripe(0); // 仅需 api_version，secret 由参数传入
        $client = new StripeClient($config + ['secret_key' => $secret]);
        return $client->verifyWebhookSignature($payload, $sigHeader, $secret);
    }

    /**
     * 由订单号反查 wxapp_id（用于定位验签密钥）
     * @param string $orderNo
     * @return int
     */
    public static function resolveWxappId($orderNo)
    {
        if (strpos($orderNo, 'RC') === 0) {
            $id = Db::name('recharge_order')->where('order_no', $orderNo)->value('wxapp_id');
        } else {
            $id = Db::name('order')->where('order_no', $orderNo)->value('wxapp_id');
        }
        return (int)$id;
    }

    /**
     * 取 Stripe webhook secret（按 wxapp_id；默认读 setting 表 stripe 配置）
     * @param int $wxappId
     * @return string
     */
    public static function getStripeSecret($wxappId)
    {
        $config = PayConfig::stripe((int)$wxappId);
        return $config['webhook_secret'] ?: '';
    }
}
