<?php
namespace app\common\library\pay;

use app\common\exception\BaseException;
use think\facade\Log;

/**
 * 支付宝交易接口封装（App 支付）
 *
 * 移植自 php_my common/library/alipay/AlipayTrade.php，并修正了原实现的一个关键缺陷：
 *
 * 【原实现问题】原 appPay() 走 AlipayClient::execute() 请求支付宝网关，再从
 * alipay_trade_app_pay_response 中取 order_string。但支付宝网关对该接口并不返回
 * order_string 字段，导致原生 SDK 拿到空串无法拉起支付。
 *
 * 【现实现】改用官方推荐的 sdkExecute：在本地完成「公共参数 + biz_content + RSA2 签名」
 * 的拼接并直接返回已签名的请求串，无需请求网关。原生 SDK 用该串即可调起支付宝收银台。
 * 交易号 trade_no 在支付成功后由异步通知带回（写入 order.transaction_id）。
 */
class AlipayTrade
{
    /** @var AlipayClient 原生网关调用（查询/关闭/退款，暂保留） */
    private $client;

    /** @var \Alipay\EasySDK\Kernel\Factory|null 官方 EasySDK 实例 */
    private $factory;

    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new AlipayClient($config);
    }

    /**
     * 官方 EasySDK 实例（alipaysdk/easysdk）
     *
     * 说明：EasySDK 的 Factory 为进程内单例，单个请求只服务一个 wxapp_id，
     * 因此每次调用前重新 setOptions 即可；FPM 下每请求重置静态状态，无串号风险。
     * @return \Alipay\EasySDK\Kernel\Factory
     * @throws BaseException
     */
    private function easySdk()
    {
        if ($this->factory !== null) {
            return $this->factory;
        }
        if (!class_exists(\Alipay\EasySDK\Kernel\Factory::class)) {
            throw new BaseException(['msg' => '支付宝 EasySDK 未安装，请执行 composer require alipaysdk/easysdk']);
        }
        $sdk  = config('pay.alipay_sdk', []);
        $opts = new \Alipay\EasySDK\Kernel\Config();
        $opts->protocol           = $sdk['protocol'] ?? 'https';
        $opts->gatewayHost        = $sdk['gateway_host'] ?? 'openapi.alipay.com';
        $opts->signType           = $this->config['sign_type'] ?: ($sdk['sign_type'] ?? 'RSA2');
        $opts->appId              = $this->config['app_id'];
        $opts->merchantPrivateKey = $this->config['private_key'];
        $opts->alipayPublicKey    = $this->config['public_key'];
        $opts->notifyUrl          = $this->config['notify_url'] ?? '';
        if (!empty($sdk['ignore_ssl'])) {
            $opts->ignoreSSL = true;
        }
        $this->factory = \Alipay\EasySDK\Kernel\Factory::setOptions($opts);
        return $this->factory;
    }

    /**
     * App 支付下单（本地生成已签名请求串）
     *
     * 现实现：优先走官方 EasySDK payment()->app()->pay()（本地 RSA2 签名，不请求网关），
     * 异常时回退原生 sdkExecute，保证下单链路不中断（收不了款是最严重的故障）。
     *
     * @param string $outTradeNo 商户订单号
     * @param float  $totalAmount 金额（元）
     * @param string $subject 订单标题
     * @param array  $extra 其他 biz_content 参数（原生回退路径使用；EasySDK 路径暂不支持透传）
     * @return array{order_string:string,out_trade_no:string,total_amount:string,subject:string}
     * @throws BaseException
     */
    public function appPay($outTradeNo, $totalAmount, $subject, array $extra = [])
    {
        if (empty($outTradeNo)) {
            throw new BaseException(['msg' => '商户订单号不能为空']);
        }
        if ($totalAmount <= 0) {
            throw new BaseException(['msg' => '订单金额必须大于0']);
        }
        if (empty($subject)) {
            throw new BaseException(['msg' => '订单标题不能为空']);
        }

        $notifyUrl = $extra['notify_url'] ?? ($this->config['notify_url'] ?? '');
        unset($extra['notify_url']);

        $bizContent = array_merge([
            'out_trade_no' => $outTradeNo,
            'total_amount' => (string)round($totalAmount, 2),
            'subject'      => mb_substr($subject, 0, 256),
            'product_code' => 'QUICK_MSECURITY_PAY',
        ], $extra);

        $amount  = (string)round($totalAmount, 2);
        $title   = mb_substr($subject, 0, 256);
        $orderString = '';
        try {
            $orderString = (string)($this->easySdk()->payment()->app()->pay($title, $outTradeNo, $amount)->body ?? '');
        } catch (\Throwable $e) {
            Log::write('支付宝 EasySDK 下单失败，回退原生签名：' . $e->getMessage(), 'error');
        }
        if ($orderString === '') {
            // 回退：与接入 EasySDK 之前的生产行为保持一致
            $orderString = $this->client->sdkExecute('alipay.trade.app.pay', $bizContent, $notifyUrl ?: null);
        }

        return [
            'order_string' => $orderString,
            'out_trade_no' => $outTradeNo,
            'total_amount' => $amount,
            'subject'      => $title,
        ];
    }

    /**
     * 校验支付宝异步通知签名（官方 EasySDK RSA2 验签）
     * @param array $params 回调 POST 参数（含 sign / sign_type）
     * @return bool
     */
    public function verifyNotify(array $params)
    {
        if (empty($params['sign'])) {
            return false;
        }
        try {
            $this->easySdk();
            return (bool)\Alipay\EasySDK\Kernel\Factory::payment()->common()->verifyNotify($params);
        } catch (\Throwable $e) {
            Log::write('支付宝 EasySDK 验签失败：' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 查询订单支付结果（走网关）
     * @param string|null $outTradeNo
     * @param string|null $tradeNo
     * @return array
     * @throws BaseException
     */
    public function query($outTradeNo = null, $tradeNo = null)
    {
        if (empty($outTradeNo) && empty($tradeNo)) {
            throw new BaseException(['msg' => '商户订单号和支付宝交易号不能同时为空']);
        }
        $bizContent = [];
        if ($outTradeNo) {
            $bizContent['out_trade_no'] = $outTradeNo;
        }
        if ($tradeNo) {
            $bizContent['trade_no'] = $tradeNo;
        }
        return $this->client->execute('alipay.trade.query', $bizContent);
    }

    /**
     * 关闭订单（走网关）
     * @param string|null $outTradeNo
     * @param string|null $tradeNo
     * @return array
     * @throws BaseException
     */
    public function close($outTradeNo = null, $tradeNo = null)
    {
        if (empty($outTradeNo) && empty($tradeNo)) {
            throw new BaseException(['msg' => '商户订单号和支付宝交易号不能同时为空']);
        }
        $bizContent = [];
        if ($outTradeNo) {
            $bizContent['out_trade_no'] = $outTradeNo;
        }
        if ($tradeNo) {
            $bizContent['trade_no'] = $tradeNo;
        }
        return $this->client->execute('alipay.trade.close', $bizContent);
    }

    /**
     * 退款（走网关，需证书时请在 config 中补充 app_cert_sn 等参数）
     * @param string|null $outTradeNo
     * @param string|null $tradeNo
     * @param float       $refundAmount
     * @param string|null $outRequestNo
     * @param string|null $refundReason
     * @return array
     * @throws BaseException
     */
    public function refund($outTradeNo = null, $tradeNo = null, $refundAmount = 0, $outRequestNo = null, $refundReason = null)
    {
        if (empty($outTradeNo) && empty($tradeNo)) {
            throw new BaseException(['msg' => '商户订单号和支付宝交易号不能同时为空']);
        }
        $bizContent = ['refund_amount' => (string)round($refundAmount, 2)];
        if ($outTradeNo) {
            $bizContent['out_trade_no'] = $outTradeNo;
        }
        if ($tradeNo) {
            $bizContent['trade_no'] = $tradeNo;
        }
        if ($outRequestNo) {
            $bizContent['out_request_no'] = $outRequestNo;
        }
        if ($refundReason) {
            $bizContent['refund_reason'] = $refundReason;
        }
        return $this->client->execute('alipay.trade.refund', $bizContent);
    }
}
