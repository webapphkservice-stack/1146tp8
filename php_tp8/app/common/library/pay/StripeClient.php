<?php
namespace app\common\library\pay;

use app\common\exception\BaseException;
use think\facade\Log;

/**
 * Stripe 支付客户端（原生 REST 实现，零 SDK 依赖）
 *
 * 移植自 php_my common/library/stripe/StripeClient.php，并补齐 Webhook 验签：
 *  1. createPaymentIntent()：走 Stripe REST API 创建 PaymentIntent，返回 client_secret。
 *  2. verifyWebhookSignature()：原生实现 Stripe 签名校验，算法与 \Stripe\Webhook::constructEvent 一致：
 *       签名头格式  Stripe-Signature: t=<ts>,v1=<sig>[,v1=<sig2>]
 *       待签串      "{ts}.{payload}"
 *       摘要        HMAC-SHA256(待签串, webhook_secret)
 *       校验        hash_equals + 时间戳容差（默认 300s，防重放）
 *     因此 php_tp8 无需安装 stripe/stripe-php 即可完成真实验签。
 */
class StripeClient
{
    /** Stripe API 基址 */
    const API_BASE = 'https://api.stripe.com/v1/';

    /** 默认时间戳容差（秒） */
    const DEFAULT_TOLERANCE = 300;

    /** @var array secret_key / currency / api_version / publishable_key */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 创建 PaymentIntent
     * @param string $orderNo 商户订单号（写入 metadata 并作为幂等键）
     * @param float  $amount  金额（元，内部换算为最小货币单位）
     * @param array  $extra   附加参数（如 customer、description、automatic_payment_methods）
     * @return array PaymentIntent 对象（含 id / client_secret / status）
     * @throws BaseException
     */
    public function createPaymentIntent($orderNo, $amount, array $extra = [])
    {
        if (empty($this->config['secret_key'])) {
            throw new BaseException(['msg' => '请到 [后台-Stripe 配置] 填写 Secret Key']);
        }
        $params = [
            'amount'                            => (int)round($amount * 100),
            'currency'                          => strtolower($this->config['currency'] ?: 'myr'),
            'metadata[order_no]'                => (string)$orderNo,
            'automatic_payment_methods[enabled]' => 'true',
        ];
        foreach ($extra as $k => $v) {
            $params[$k] = $v;
        }
        return $this->request('payment_intents', $params, $orderNo);
    }

    /**
     * 查询 PaymentIntent
     * @param string $paymentIntentId
     * @return array
     * @throws BaseException
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        return $this->request('payment_intents/' . $paymentIntentId, [], null, 'GET');
    }

    /**
     * 校验 Stripe Webhook 签名（原生实现，等价于 \Stripe\Webhook::constructEvent）
     * @param string $payload    原始请求体（未经任何处理的字节串）
     * @param string $sigHeader  Stripe-Signature 请求头
     * @param string $secret     webhook signing secret
     * @param int    $tolerance  时间戳容差（秒）
     * @return array 校验通过返回解析后的事件数组
     * @throws BaseException 签名不合法/过期/配置缺失时抛出
     */
    public function verifyWebhookSignature($payload, $sigHeader, $secret, $tolerance = self::DEFAULT_TOLERANCE)
    {
        if (empty($secret)) {
            throw new BaseException(['msg' => 'Stripe Webhook Secret 未配置']);
        }
        if (empty($sigHeader)) {
            throw new BaseException(['msg' => '缺少 Stripe-Signature 请求头']);
        }
        $timestamp = -1;
        $signatures = [];
        foreach (explode(',', $sigHeader) as $item) {
            $parts = explode('=', trim($item), 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$key, $value] = $parts;
            if ($key === 't') {
                $timestamp = (int)$value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }
        if ($timestamp <= 0 || empty($signatures)) {
            throw new BaseException(['msg' => 'Stripe-Signature 格式不合法']);
        }
        if ($tolerance > 0 && abs(time() - $timestamp) > $tolerance) {
            throw new BaseException(['msg' => 'Stripe 签名时间戳超出容差范围（疑似重放）']);
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);
        $matched = false;
        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            throw new BaseException(['msg' => 'Stripe 签名校验失败']);
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['type'])) {
            throw new BaseException(['msg' => 'Stripe 事件解析失败']);
        }
        return $event;
    }

    // -------------------- 内部：REST 请求 --------------------

    /**
     * 调用 Stripe REST API
     * @param string      $path
     * @param array       $params
     * @param string|null $idempotencyKey
     * @param string      $method POST|GET
     * @return array
     * @throws BaseException
     */
    private function request($path, array $params, $idempotencyKey = null, $method = 'POST')
    {
        $url = self::API_BASE . $path;
        $headers = ['Stripe-Version: ' . ($this->config['api_version'] ?: '2023-10-16')];
        if ($method === 'GET') {
            if (!empty($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            }
        } elseif ($idempotencyKey) {
            $headers[] = 'Idempotency-Key: ' . $idempotencyKey;
        }

        $ch = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_USERPWD        => $this->config['secret_key'] . ':',
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }
        curl_setopt_array($ch, $options);

        $body     = curl_exec($ch);
        $error    = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // PHP 8.5 起 curl_close() 已废弃（8.0 之后无实际作用），调用会触发 deprecated 异常
        $ch = null;

        if ($error) {
            throw new BaseException(['msg' => 'Stripe 网络请求失败：' . $error]);
        }
        $data = json_decode((string)$body, true);
        if (!is_array($data)) {
            Log::write('Stripe 响应解析失败：' . substr((string)$body, 0, 500), 'error');
            throw new BaseException(['msg' => 'Stripe 响应解析失败']);
        }
        if ($httpCode >= 400) {
            $msg = $data['error']['message'] ?? 'Stripe 接口错误';
            throw new BaseException(['msg' => 'Stripe：' . $msg . '（HTTP ' . $httpCode . '）']);
        }
        return $data;
    }
}
