<?php
namespace app\api\service;

use think\facade\Db;
use think\facade\Log;
use app\common\enum\order\PayType;
use app\common\exception\BaseException;
use app\common\library\pay\PayConfig;
use app\common\library\pay\WxPay;
use app\common\library\pay\WxPayV3;
use app\common\library\pay\AlipayTrade;
use app\common\library\pay\StripeClient;

/**
 * 支付参数构建（P0：接入真实支付渠道）
 *
 * 三个渠道全部为原生实现（curl + openssl），无需安装 WxPay / 支付宝 aop / Stripe SDK：
 *  - WECHAT：WxPay::unifiedOrder() 调「统一下单」获取 prepay_id 并二次签名
 *  - ALIPAY：AlipayTrade::appPay() 本地生成已签名 order_string（sdkExecute，不请求网关）
 *  - STRIPE：StripeClient::createPaymentIntent() 调 REST API 获取 client_secret
 *
 * 各 pay_type 返回的 payment 结构（与 RN payment/index.ts、stripe.ts 对齐，不得随意变更）：
 *  - WECHAT(20) : { wxConfig:{appId,timeStamp,nonceStr,package,signType,paySign[,partnerId,prepayId]}, out_trade_no, total_fee }
 *  - ALIPAY(40) : { out_trade_no, total_amount, subject, order_string }
 *  - STRIPE(50) : { clientSecret, publishableKey, merchantName, customerId, ephemeralKeySecret, out_trade_no, total_fee }
 *  - BALANCE(10): []（余额支付无第三方参数，同步扣款并改单）
 *  - FRIEND(30) : { out_trade_no, total_fee, friend_pay_url }
 *
 * 注意：配置缺失或网关报错时抛 BaseException（code=0），RN 端会提示具体原因，
 *       避免返回无效参数后在原生 SDK 侧出现更难排查的失败。
 */
class Payment
{
    /**
     * 生成支付参数（供 user.order/pay、recharge/submit、order.friendpay/pay 返回）
     * @param int    $payType
     * @param array  $order 订单原始行（含 order_no / pay_price / wxapp_id / user_id）
     * @param array  $user  当前用户
     * @param string $outTradeNo 实际提交给支付网关的商户单号，null 表示用 $order['order_no']。
     *                           好友代付场景必须传临时单号：此时 $order['order_no'] 仍是真实订单号，
     *                           会被写入 attach，回调时据此还原真实订单（见 Notify::resolveOrderNo）。
     * @param string $platform  支付平台标识：android / ios / h5。H5 场景下微信支付走 MWEB（返回 mweb_url），
     *                          其余场景按是否有 openid 自动选择 JSAPI 或 APP。
     * @param string $redirectUrl MWEB 支付完成后的回跳 URL（前端支付结果页）。
     *                            微信返回的 h5_url 会拼接 &redirect_url= 编码后的值，
     *                            用户支付完毕自动跳回该页。为空时读取配置 pay.notify_url.h5_redirect。
     * @return array
     * @throws BaseException
     */
    public function getParams($payType, $order, $user = [], $outTradeNo = null, $platform = '', $redirectUrl = '')
    {
        switch ($payType) {
            case PayType::WECHAT:
                return $this->wechatParams($order, $user, $outTradeNo, $platform, $redirectUrl);
            case PayType::ALIPAY:
                return $this->alipayParams($order);
            case PayType::STRIPE:
                return $this->stripeParams($order);
            case PayType::BALANCE:
                return []; // 余额支付无第三方参数
            case PayType::FRIEND:
                return $this->friendParams($order);
            default:
                throw new BaseException('不支持的支付方式');
        }
    }

    /**
     * 微信支付：统一下单 + 二次签名 / 跳转 URL
     *
     * tradeType 决策逻辑（三元）：
     *   platform == 'h5' → MWEB（返回 mweb_url，前端 location.href 跳转）
     *   openid 不为空    → JSAPI （返回 prepay_id 二次签名，前端 wx.requestPayment 调起）
     *   其余             → APP   （返回 prepay_id 二次签名，原生 SDK 调起）
     *
     * @param array  $order
     * @param array  $user
     * @param string $outTradeNo
     * @param string $platform
     * @return array
     * @throws BaseException
     */
    private function wechatParams($order, $user = [], $outTradeNo = null, $platform = '', $redirectUrl = '')
    {
        $wxappId = (int)$order['wxapp_id'];
        $openid  = $this->getUserOpenid($order, $user);

        // H5 场景强制 MWEB（不需 openid，也不走 JSAPI/APP）
        if (strtolower($platform) === 'h5') {
            $tradeType = 'MWEB';
        } else {
            $tradeType = $openid !== '' ? 'JSAPI' : 'APP';
        }

        $sceneInfo = [
            'payer_client_ip' => request()->ip(),
        ];

        // 拼接 redirect_url（支付完成后自动跳回）
        if ($redirectUrl === '') {
            $redirectUrl = config('pay.notify_url.h5_redirect', env('APP_HOST', '') . '/h5/payment/result');
        }

        // v3（官方 SDK）优先；商户证书尚未配置时回退 v2，避免切换窗口收不了款
        if (PayConfig::wechatV3Ready($wxappId)) {
            return $this->wechatV3Params($order, $wxappId, $openid, $outTradeNo, $tradeType, $sceneInfo, $redirectUrl);
        }
        Log::warning('微信支付未配置 v3 商户证书，回退 v2(MD5) 下单，wxapp_id=' . $wxappId);
        return $this->wechatV2Params($order, $wxappId, $openid, $outTradeNo, $tradeType, $sceneInfo, $redirectUrl);
    }

    /**
     * 微信支付 v3 下单（官方 SDK wechatpay/wechatpay，RSA-SHA256 签名）
     * @param array  $order
     * @param int    $wxappId
     * @param string $openid
     * @param string $outTradeNo
     * @param string $tradeType  JSAPI|APP|MWEB
     * @param array  $sceneInfo  MWEB 场景信息
     * @param string $redirectUrl MWEB 回跳 URL
     * @return array
     * @throws BaseException
     */
    private function wechatV3Params($order, $wxappId, $openid, $outTradeNo = null, $tradeType = 'APP', $sceneInfo = [], $redirectUrl = '')
    {
        $config = PayConfig::wechatV3($wxappId);

        $result = (new WxPayV3($config))->unifiedOrder(
            $order['order_no'],
            (float)$order['pay_price'],
            $openid,
            $this->buildBody($order),
            $outTradeNo,
            $tradeType,
            $sceneInfo
        );

        // MWEB 直接返回，不走 formatWxConfig（字段结构完全不同）
        if ($tradeType === 'MWEB') {
            return [
                'mweb_url'     => $this->attachRedirectUrl($result['mweb_url'], $redirectUrl),
                'out_trade_no' => $result['out_trade_no'],
                'total_fee'    => $result['total_fee'],
                'trade_type'   => 'MWEB',
            ];
        }

        $wxConfig = $this->formatWxConfig($result, $tradeType);
        return [
            'wxConfig'     => $wxConfig,
            'out_trade_no' => $order['order_no'],
            'total_fee'    => $order['pay_price'],
            'trade_type'   => $tradeType,
        ] + $this->miniProgramFlatFields($wxConfig);
    }

    /**
     * 微信支付 v2 下单（旧版兼容路径，MD5 签名）
     * @param array  $order
     * @param int    $wxappId
     * @param string $openid
     * @param string $outTradeNo
     * @param string $tradeType  JSAPI|APP|MWEB
     * @param array  $sceneInfo  MWEB 场景信息
     * @param string $redirectUrl MWEB 回跳 URL
     * @return array
     * @throws BaseException
     */
    private function wechatV2Params($order, $wxappId, $openid, $outTradeNo = null, $tradeType = 'APP', $sceneInfo = [], $redirectUrl = '')
    {
        $config = PayConfig::wechat($wxappId);

        $result = (new WxPay($config))->unifiedOrder(
            $order['order_no'],
            (float)$order['pay_price'],
            $openid,
            $this->buildBody($order),
            $outTradeNo,
            $tradeType,
            $sceneInfo
        );

        // MWEB 直接返回
        if ($tradeType === 'MWEB') {
            return [
                'mweb_url'     => $this->attachRedirectUrl($result['mweb_url'], $redirectUrl),
                'out_trade_no' => $result['out_trade_no'],
                'total_fee'    => $result['total_fee'],
                'trade_type'   => 'MWEB',
            ];
        }

        $wxConfig = $this->formatWxConfig($result, $tradeType);
        return [
            'wxConfig'     => $wxConfig,
            'out_trade_no' => $order['order_no'],
            'total_fee'    => $order['pay_price'],
            'trade_type'   => $tradeType,
        ] + $this->miniProgramFlatFields($wxConfig);
    }

    /**
     * 给微信 MWEB 返回的 mweb_url 拼接 redirect_url（支付完成后自动跳回前端结果页）
     *
     * 微信 H5 支付 mweb_url 格式通常为：
     *   https://wx.tenpay.com/cgi-bin/mmpayweb-bin/checkmweb?...&package=...
     * redirect_url 需要 URL 编码后作为 query 参数附加。
     *
     * @param string $mwebUrl  微信返回的原始 mweb_url
     * @param string $redirect 回跳 URL（前端结果页，如 https://oa.360lst.com/h5/payment/result?order_no=xxx）
     * @return string 已拼接 redirect_url 的 mweb_url（redirect 为空则原样返回）
     */
    private function attachRedirectUrl($mwebUrl, $redirect)
    {
        if ($mwebUrl === '' || $redirect === '') {
            return $mwebUrl;
        }
        $separator = strpos($mwebUrl, '?') === false ? '?' : '&';
        return $mwebUrl . $separator . 'redirect_url=' . urlencode($redirect);
    }

    /**
     * 生成微信小程序端 wxPayment 所需的顶层扁平字段（与 wxConfig 并存，互不影响）
     *
     * 小程序 app.js 的 wxPayment 按老后端 WxPay 契约读取 payment.timeStamp / prepay_id /
     * paySign / signType 等顶层键；RN 客户端读 wxConfig 嵌套结构，两套契约同时满足。
     * APP 结构（appid/partnerid/...）小程序无法调起，返回空数组即可。
     */
    private function miniProgramFlatFields(array $wxConfig)
    {
        if (!isset($wxConfig['prepayId'])) {
            return [];
        }
        return [
            'timeStamp' => $wxConfig['timeStamp'],
            'nonceStr'  => $wxConfig['nonceStr'],
            'package'   => $wxConfig['package'],
            'prepay_id' => $wxConfig['prepayId'],
            'prepayId'  => $wxConfig['prepayId'],
            'paySign'   => $wxConfig['paySign'],
            'signType'  => $wxConfig['signType'],
        ];
    }

    /**
     * 将统一下单结果统一为 RN 契约结构
     * APP 支付返回全小写字段（appid/partnerid/prepayid/...），JSAPI 返回驼峰字段，此处归一化
     * @param array  $result
     * @param string $tradeType
     * @return array
     */
    private function formatWxConfig(array $result, $tradeType)
    {
        if ($tradeType === 'JSAPI') {
            return [
                'appId'     => $result['appId'],
                'timeStamp' => $result['timeStamp'],
                'nonceStr'  => $result['nonceStr'],
                'package'   => $result['package'],
                'signType'  => $result['signType'],
                'paySign'   => $result['paySign'],
                'prepayId'  => $result['prepay_id'] ?? '',
            ];
        }
        return [
            'appId'     => $result['appid'],
            'partnerId' => $result['partnerid'],
            'prepayId'  => $result['prepayid'],
            'timeStamp' => $result['timestamp'],
            'nonceStr'  => $result['noncestr'],
            'package'   => $result['package'],
            // v3 为 RSA-SHA256，v2 为 MD5：由下单结果带回，避免两条链路混用签名方式
            'signType'  => $result['signType'] ?? 'MD5',
            'paySign'   => $result['sign'],
        ];
    }

    /**
     * 支付宝 App 支付：本地生成已签名订单串
     * @param array $order
     * @return array
     * @throws BaseException
     */
    private function alipayParams($order)
    {
        $config = PayConfig::alipay((int)$order['wxapp_id']);
        $trade  = new AlipayTrade($config);
        $result = $trade->appPay($order['order_no'], (float)$order['pay_price'], $this->buildBody($order));

        return [
            'out_trade_no' => $result['out_trade_no'],
            'total_amount' => $result['total_amount'],
            'subject'      => $result['subject'],
            // 原生 SDK 直接消费该串；为空说明签名/配置异常
            'order_string' => $result['order_string'],
        ];
    }

    /**
     * Stripe：创建 PaymentIntent，返回 client_secret 供 RN PaymentSheet 使用
     * @param array $order
     * @return array
     * @throws BaseException
     */
    private function stripeParams($order)
    {
        $config  = PayConfig::stripe((int)$order['wxapp_id']);
        $client  = new StripeClient($config);
        $intent  = $client->createPaymentIntent($order['order_no'], (float)$order['pay_price']);
        if (empty($intent['client_secret'])) {
            throw new BaseException(['msg' => 'Stripe 创建支付意图失败']);
        }

        return [
            'clientSecret'       => $intent['client_secret'],
            'publishableKey'     => $config['publishable_key'],
            'merchantName'       => $config['merchant_name'],
            'customerId'         => !empty($order['user_id']) ? 'cus_' . $order['user_id'] : '',
            // 客户会话（Customer Session）未启用时为空，RN 端 initPaymentSheet 可忽略该字段
            'ephemeralKeySecret' => '',
            'out_trade_no'       => $order['order_no'],
            'total_fee'          => $order['pay_price'],
        ];
    }

    /**
     * 好友代付：下单时只建单、不发起支付，返回可分享给好友的代付链接
     *
     * 流程：下单人选「好友代付」-> 本方法返回 friend_pay_url
     *      -> 分享给好友 -> 好友打开代付页（order.friendpay/detail）
     *      -> 好友点支付（order.friendpay/pay，用临时商户单号真实下单）
     *      -> 支付成功回调由 Notify::resolveOrderNo() 还原真实订单号并完成流转
     *
     * @param array $order
     * @return array
     */
    private function friendParams($order)
    {
        $orderId = (int)($order['order_id'] ?? 0);
        return [
            'out_trade_no'   => $order['order_no'],
            'total_fee'      => $order['pay_price'],
            // 代付落地页：好友打开后展示订单并支付
            'friend_pay_url' => 'pages/order/friend-pay/index?order_id=' . $orderId,
            'order_id'       => $orderId,
        ];
    }

    // -------------------- 辅助方法 --------------------

    /**
     * 获取用户 openid（JSAPI 支付需要）
     * @param array $order
     * @param array $user
     * @return string
     */
    private function getUserOpenid($order, $user = [])
    {
        if (!empty($user['open_id'])) {
            return (string)$user['open_id'];
        }
        $userId = $order['user_id'] ?? ($user['user_id'] ?? 0);
        if (!$userId) {
            return '';
        }
        try {
            return (string)(Db::name('user')->where('user_id', $userId)->value('open_id') ?: '');
        } catch (\Throwable $e) {
            Log::write('获取用户 openid 失败：' . $e->getMessage(), 'error');
            return '';
        }
    }

    /**
     * 构建支付商品描述（微信 body / 支付宝 subject）
     * 主订单取前 3 个商品名拼接；充值订单按前缀区分
     * @param array $order
     * @return string
     */
    private function buildBody($order)
    {
        $orderNo = $order['order_no'] ?? '';
        if (strpos($orderNo, 'RC') === 0) {
            return '账户充值' . $orderNo;
        }
        try {
            $names = Db::name('order_goods')
                ->where('order_id', $order['order_id'] ?? 0)
                ->column('goods_name');
            $names = array_values(array_filter((array)$names));
            if (!empty($names)) {
                $desc = implode('、', array_slice($names, 0, 3));
                if (count($names) > 3) {
                    $desc .= ' 等';
                }
                // 微信 body 限制 128 字节，中文按 3 字节计，留余量截断
                if (mb_strlen($desc, 'UTF-8') > 42) {
                    $desc = mb_substr($desc, 0, 42, 'UTF-8') . '…';
                }
                return $desc;
            }
        } catch (\Throwable $e) {
            Log::write('构建支付描述失败：' . $e->getMessage(), 'error');
        }
        return '订单' . $orderNo;
    }
}
