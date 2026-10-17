<?php
namespace app\common\library\pay;

use app\common\exception\BaseException;
use think\facade\Log;
use WeChatPay\Builder;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Formatter;
use WeChatPay\Util\PemUtil;

/**
 * 微信支付 v3（官方 SDK wechatpay/wechatpay ^1.4）
 *
 * 与 v2（WxPay）的关键差异：
 *  1. 接口为 REST/JSON，不再是 XML；下单地址 v3/pay/transactions/{app|jsapi}
 *  2. 签名算法由 MD5/HMAC 变为 RSA-SHA256（signType = RSA），需商户 API 证书私钥
 *  3. 异步通知为 JSON，业务数据 AES-256-GCM 加密在 resource 中，需用 APIv3 密钥解密
 *  4. 应答签名由平台证书/微信支付公钥验证（Builder 自动完成，回调需自行验签）
 *
 * 配置均由 PayConfig::wechatV3() 注入：
 *   mchid / serial_no / private_key / platform_cert|platform_pubkey(+id) / apiv3_key
 */
class WxPayV3
{
    /** 通知验签容差（秒），超出则判定为重放 */
    const NOTIFY_TOLERANCE = 300;

    /** @var array */
    private $config;

    /** @var \OpenSSLAsymmetricKey|resource|null 商户 API 证书私钥 */
    private $privateKey;

    /** @var \OpenSSLAsymmetricKey|resource|null 微信支付平台公钥 */
    private $platformKey;

    /** @var string 平台证书序列号 / 微信支付公钥 ID */
    private $platformSerial = '';

    /** @var \WeChatPay\BuilderChainable|null */
    private $instance;

    /**
     * @param array $config PayConfig::wechatV3()
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    // -------------------- 下单 --------------------

    /**
     * 统一下单（APP / JSAPI / MWEB）
     *
     * @param string      $orderNo    商户订单号（写入 attach，回调据此定位订单）
     * @param float       $totalFee   金额（元，内部换算为分）
     * @param string      $openid     JSAPI 必填；MWEB/APP 忽略
     * @param string      $body       商品描述
     * @param string|null $outTradeNo 实际提交给微信的商户单号（好友代付等场景）
     * @param string      $tradeType  APP|JSAPI|MWEB，为空时按是否有 openid 自动选择
     * @param array       $sceneInfo  MWEB 场景信息：{payer_client_ip, h5_info:{type}}，由上层传入
     * @return array JSAPI: {appId,timeStamp,nonceStr,package,signType,paySign,prepay_id}
     *               APP:   {appid,partnerid,prepayid,package,noncestr,timestamp,sign,signType}
     *               MWEB:  {mweb_url, out_trade_no, total_fee}（前端 location.href 跳转即可）
     * @throws BaseException
     */
    public function unifiedOrder($orderNo, $totalFee, $openid = '', $body = '', $outTradeNo = null, $tradeType = '', $sceneInfo = [])
    {
        $this->checkConfig();
        $tradeType = strtoupper((string)$tradeType);
        if ($tradeType === '') {
            $tradeType = $openid !== '' ? 'JSAPI' : 'APP';
        }
        if (!in_array($tradeType, ['JSAPI', 'APP', 'MWEB'], true)) {
            throw new BaseException(['msg' => '微信支付 v3 不支持的下单类型：' . $tradeType]);
        }
        if ($tradeType === 'JSAPI' && $openid === '') {
            throw new BaseException(['msg' => '微信支付 JSAPI 下单缺少 openid']);
        }

        $appId = $tradeType === 'JSAPI'
            ? ($this->config['mp_app_id'] ?: $this->config['app_id'])
            : $this->config['app_id'];

        $payload = [
            'appid'        => $appId,
            'mchid'        => $this->config['mchid'],
            'description'  => $body !== '' ? $body : $orderNo,
            'out_trade_no' => $outTradeNo ?: $orderNo,
            'attach'       => $orderNo,
            'notify_url'   => $this->config['notify_url'],
            'amount'       => [
                'total'    => (int)round($totalFee * 100), // 单位：分
                'currency' => 'CNY',
            ],
        ];
        if ($tradeType === 'JSAPI') {
            $payload['payer'] = ['openid' => $openid];
        }
        if ($tradeType === 'MWEB') {
            // H5 支付场景信息：payer_client_ip 前端 IP，h5_info.type 微信内浏览器 / 外部浏览器
            $payload['scene_info'] = [
                'payer_client_ip' => $sceneInfo['payer_client_ip'] ?? ($this->clientIp() ?: '127.0.0.1'),
                'h5_info'         => [
                    'type' => $sceneInfo['h5_info']['type'] ?? 'Wap',
                ],
            ];
        }

        $endpoint = 'v3/pay/transactions/' . strtolower($tradeType);
        Log::write('微信v3下单请求[' . $endpoint . ']：' . json_encode($payload, JSON_UNESCAPED_UNICODE), 'info');

        try {
            $resp = $this->client()->chain($endpoint)->post(['json' => $payload]);
        } catch (\Throwable $e) {
            // Builder 验签失败 / 网络异常均以 Throwable 抛出
            throw new BaseException(['msg' => '微信支付 v3 请求失败：' . $e->getMessage()]);
        }

        $status = $resp->getStatusCode();
        $raw    = (string)$resp->getBody();
        $result = json_decode($raw, true) ?: [];

        // MWEB 成功返回 { h5_url }，无 prepay_id
        if ($tradeType === 'MWEB') {
            if ($status < 200 || $status >= 300 || empty($result['h5_url'])) {
                Log::write('微信v3 MWEB 下单失败：HTTP ' . $status . ' ' . $raw, 'error');
                throw new BaseException(['msg' => '微信支付 H5 下单失败：' . ($result['message'] ?? $raw ?: 'HTTP ' . $status)]);
            }
            Log::write('微信v3 MWEB 下单结果：' . json_encode($result, JSON_UNESCAPED_UNICODE), 'info');
            return [
                'mweb_url'     => $result['h5_url'],
                'out_trade_no' => $outTradeNo ?: $orderNo,
                'total_fee'    => $totalFee,
            ];
        }

        // JSAPI / APP 成功返回 prepay_id
        if ($status < 200 || $status >= 300 || empty($result['prepay_id'])) {
            Log::write('微信v3下单失败：HTTP ' . $status . ' ' . $raw, 'error');
            throw new BaseException(['msg' => '微信支付 v3 下单失败：' . ($result['message'] ?? $raw ?: 'HTTP ' . $status)]);
        }
        Log::write('微信v3下单结果：' . json_encode($result, JSON_UNESCAPED_UNICODE), 'info');

        return $tradeType === 'JSAPI'
            ? $this->jsapiParams($appId, $result['prepay_id'])
            : $this->appParams($appId, $result['prepay_id']);
    }

    /**
     * 取客户端 IP（供 MWEB scene_info 使用）
     */
    private function clientIp()
    {
        try {
            $ip = request()->ip();
        } catch (\Throwable $e) {
            $ip = '';
        }
        if (!$ip || $ip === '0.0.0.0') {
            $ip = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        }
        return $ip;
    }

    /**
     * 按商户订单号查询交易（对账 / 补单）
     * @param string $outTradeNo
     * @return array
     * @throws BaseException
     */
    public function query($outTradeNo)
    {
        $this->checkConfig();
        try {
            $resp = $this->client()
                ->chain('v3/pay/transactions/out-trade-no/' . rawurlencode($outTradeNo))
                ->get(['query' => ['mchid' => $this->config['mchid']]]);
        } catch (\Throwable $e) {
            throw new BaseException(['msg' => '微信支付 v3 查询失败：' . $e->getMessage()]);
        }
        return json_decode((string)$resp->getBody(), true) ?: [];
    }

    // -------------------- 异步通知 --------------------

    /**
     * 校验回调的平台签名（含 5 分钟防重放）
     *
     * @param array  $headers 请求头（Wechatpay-* 四项，键名大小写不敏感）
     * @param string $body    原始请求体（必须取 php://input 原文，不能重编码）
     * @return bool
     */
    /**
     * 申请退款（微信支付 v3：POST /v3/refund/domestic/refunds）
     *
     * 说明：v3 退款以「商户退款单号 out_refund_no」做幂等键，
     * 同一 out_refund_no 重复提交微信只会处理一次，可用于重试而不会重复退钱。
     *
     * @param string $outTradeNo  原商户订单号
     * @param string $outRefundNo 商户退款单号（幂等键）
     * @param float  $totalFee    原订单总金额（元）
     * @param float  $refundFee   本次退款金额（元）
     * @param string $reason      退款原因
     * @return array 微信返回的退款单（含 refund_id / status / channel 等）
     * @throws BaseException
     */
    public function refund($outTradeNo, $outRefundNo, $totalFee, $refundFee, $reason = '')
    {
        $this->checkConfig();

        $payload = [
            'out_trade_no'  => (string)$outTradeNo,
            'out_refund_no' => (string)$outRefundNo,
            'reason'        => (string)$reason,
            'amount'        => [
                'refund'   => (int)round($refundFee * 100),   // 单位：分
                'total'    => (int)round($totalFee * 100),
                'currency' => 'CNY',
            ],
        ];
        // 退款结果通知地址（未配置则不传，微信允许不填）
        if (!empty($this->config['refund_notify_url'])) {
            $payload['notify_url'] = $this->config['refund_notify_url'];
        }

        $endpoint = 'v3/refund/domestic/refunds';
        Log::write('微信v3退款请求[' . $endpoint . ']：' . json_encode($payload, JSON_UNESCAPED_UNICODE), 'info');

        try {
            $resp = $this->client()->chain($endpoint)->post(['json' => $payload]);
        } catch (\Throwable $e) {
            // 验签失败 / 网络异常均以 Throwable 抛出
            throw new BaseException(['msg' => '微信支付 v3 退款请求失败：' . $e->getMessage()]);
        }

        $status = $resp->getStatusCode();
        $raw    = (string)$resp->getBody();
        $result = json_decode($raw, true) ?: [];

        if ($status < 200 || $status >= 300 || empty($result['refund_id'])) {
            Log::write('微信v3退款失败：HTTP ' . $status . ' ' . $raw, 'error');
            throw new BaseException(['msg' => '微信支付 v3 退款失败：' . ($result['message'] ?: $raw ?: 'HTTP ' . $status)]);
        }
        Log::write('微信v3退款结果：' . json_encode($result, JSON_UNESCAPED_UNICODE), 'info');
        return $result;
    }

    /**
     * 查询退款（微信支付 v3：GET /v3/refund/domestic/refunds/{out_refund_no}）
     * @param string $outRefundNo
     * @return array
     * @throws BaseException
     */
    public function queryRefund($outRefundNo)
    {
        $this->checkConfig();
        $endpoint = 'v3/refund/domestic/refunds/' . rawurlencode((string)$outRefundNo);
        try {
            $resp = $this->client()->chain($endpoint)->get();
        } catch (\Throwable $e) {
            throw new BaseException(['msg' => '微信支付 v3 退款查询失败：' . $e->getMessage()]);
        }
        $status = $resp->getStatusCode();
        $raw    = (string)$resp->getBody();
        $result = json_decode($raw, true) ?: [];
        if ($status < 200 || $status >= 300) {
            throw new BaseException(['msg' => '微信支付 v3 退款查询失败：' . ($result['message'] ?: $raw)]);
        }
        return $result;
    }

    public function notifyVerify(array $headers, $body)
    {
        $serial    = $this->header($headers, 'Wechatpay-Serial');
        $signature = $this->header($headers, 'Wechatpay-Signature');
        $timestamp = $this->header($headers, 'Wechatpay-Timestamp');
        $nonce     = $this->header($headers, 'Wechatpay-Nonce');

        if ($serial === '' || $signature === '' || $timestamp === '' || $nonce === '') {
            Log::write('微信v3回调缺少验签头', 'error');
            return false;
        }
        if (abs(Formatter::timestamp() - (int)$timestamp) > self::NOTIFY_TOLERANCE) {
            Log::write('微信v3回调时间戳超出容差：' . $timestamp, 'error');
            return false;
        }
        if ($serial !== '' && $this->platformSerial !== '' && !hash_equals($this->platformSerial, $serial)) {
            // 平台证书轮换期序列号可能变化（多证书场景），此处仅记录不拦截
            Log::write('微信v3回调证书序列号与本地不一致：' . $serial, 'info');
        }
        try {
            $message = Formatter::joinedByLineFeed((string)$timestamp, $nonce, (string)$body);
            // 注意：Rsa::verify 内部会自行 base64_decode，此处必须传请求头里的原文，不可提前解码
            return Rsa::verify($message, $signature, $this->platformPublicKey());
        } catch (\Throwable $e) {
            Log::write('微信v3回调验签异常：' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * 解密回调 resource（AES-256-GCM）
     * @param array $resource {algorithm, ciphertext, associated_data, nonce}
     * @return array 解密后的交易数据（含 out_trade_no / transaction_id / trade_state 等）
     * @throws BaseException
     */
    public function notifyDecrypt(array $resource)
    {
        $ciphertext = $resource['ciphertext'] ?? '';
        $nonce      = $resource['nonce'] ?? '';
        $aad        = $resource['associated_data'] ?? '';
        if ($ciphertext === '' || $nonce === '') {
            throw new BaseException(['msg' => '微信支付 v3 通知缺少 resource 密文']);
        }
        if (strlen($this->config['apiv3_key']) !== 32) {
            throw new BaseException(['msg' => '微信支付 APIv3 密钥无效（应为 32 位）']);
        }
        $plain = AesGcm::decrypt($ciphertext, $this->config['apiv3_key'], $nonce, $aad);
        $data  = json_decode($plain, true);
        if (!is_array($data)) {
            throw new BaseException(['msg' => '微信支付 v3 通知解密结果解析失败']);
        }
        return $data;
    }

    // -------------------- 内部：客户端与密钥 --------------------

    /**
     * 构建带自动验签的 API 客户端
     * @return \WeChatPay\BuilderChainable
     * @throws BaseException
     */
    private function client()
    {
        if ($this->instance === null) {
            $this->instance = Builder::factory([
                'mchid'      => $this->config['mchid'],
                'serial'     => $this->config['serial_no'],
                'privateKey' => $this->merchantPrivateKey(),
                'certs'      => [
                    $this->platformSerial() => $this->platformPublicKey(),
                ],
            ]);
        }
        return $this->instance;
    }

    /**
     * 商户 API 证书私钥
     * @return \OpenSSLAsymmetricKey|resource
     * @throws BaseException
     */
    private function merchantPrivateKey()
    {
        if ($this->privateKey === null) {
            $pem = $this->toPem($this->config['private_key'] ?? '', 'private_key');
            try {
                $this->privateKey = Rsa::from($pem, Rsa::KEY_TYPE_PRIVATE);
            } catch (\Throwable $e) {
                throw new BaseException(['msg' => '商户 API 私钥加载失败：' . $e->getMessage()]);
            }
        }
        return $this->privateKey;
    }

    /**
     * 微信支付平台公钥（平台证书模式或公钥模式）
     * @return \OpenSSLAsymmetricKey|resource
     * @throws BaseException
     */
    private function platformPublicKey()
    {
        if ($this->platformKey === null) {
            $this->loadPlatformKey();
        }
        return $this->platformKey;
    }

    /**
     * 平台证书序列号 / 公钥 ID
     * @return string
     * @throws BaseException
     */
    private function platformSerial()
    {
        if ($this->platformSerial === '') {
            $this->loadPlatformKey();
        }
        return $this->platformSerial;
    }

    /**
     * 加载平台验签公钥 + 序列号
     * @throws BaseException
     */
    private function loadPlatformKey()
    {
        $cert = trim((string)($this->config['platform_cert'] ?? ''));
        $pub  = trim((string)($this->config['platform_pubkey'] ?? ''));

        if ($pub !== '') { // 新模式：微信支付公钥 + 公钥 ID
            $pem = $this->toPem($pub, 'platform_pubkey');
            $key = openssl_pkey_get_public($pem);
            if ($key === false) {
                throw new BaseException(['msg' => '微信支付公钥加载失败']);
            }
            $this->platformKey    = $key;
            $this->platformSerial = trim((string)($this->config['platform_pubkey_id'] ?? ''));
            if ($this->platformSerial === '') {
                throw new BaseException(['msg' => '使用微信支付公钥模式时必须配置 platform_pubkey_id']);
            }
            return;
        }

        if ($cert === '') {
            throw new BaseException(['msg' => '微信支付 v3 配置缺失：platform_cert 或 platform_pubkey']);
        }
        $pem      = $this->toPem($cert, 'platform_cert');
        $certObj  = openssl_x509_read($pem);
        if ($certObj === false) {
            throw new BaseException(['msg' => '微信支付平台证书解析失败']);
        }
        $key = openssl_pkey_get_public($certObj);
        if ($key === false) {
            throw new BaseException(['msg' => '微信支付平台证书公钥提取失败']);
        }
        $this->platformKey    = $key;
        $this->platformSerial = PemUtil::parseCertificateSerialNo($certObj);
    }

    /**
     * 归一化证书入参：支持 PEM 文本、绝对路径、file:// 路径、相对项目根目录路径
     * @param string $raw
     * @param string $field 用于报错提示
     * @return string PEM 文本
     * @throws BaseException
     */
    private function toPem($raw, $field)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            throw new BaseException(['msg' => "微信支付 v3 配置缺失：{$field}"]);
        }
        if (strpos($raw, '-----BEGIN') === 0) {
            return $raw; // 已是 PEM 文本
        }
        $path = preg_replace('#^file://#', '', $raw);
        if (!is_file($path)) {
            // 允许相对项目根目录的写法，如 cert/wechatpay/apiclient_key.pem
            $rootPath = rtrim((string)app()->getRootPath(), '/');
            $try      = $rootPath . '/' . ltrim($path, '/');
            if (is_file($try)) {
                $path = $try;
            } else {
                throw new BaseException(['msg' => "微信支付证书文件不存在({$field})：{$path}"]);
            }
        }
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            throw new BaseException(['msg' => "微信支付证书读取失败({$field})：{$path}"]);
        }
        return $content;
    }

    /**
     * JSAPI 调起支付参数（RSA 签名）
     * @param string $appId
     * @param string $prepayId
     * @return array
     * @throws BaseException
     */
    private function jsapiParams($appId, $prepayId)
    {
        $package   = 'prepay_id=' . $prepayId;
        $timeStamp = (string)Formatter::timestamp();
        $nonceStr  = Formatter::nonce();
        $message   = Formatter::joinedByLineFeed($appId, $timeStamp, $nonceStr, $package);

        return [
            'appId'     => $appId,
            'timeStamp' => $timeStamp,
            'nonceStr'  => $nonceStr,
            'package'   => $package,
            'signType'  => 'RSA',
            'paySign'   => Rsa::sign($message, $this->merchantPrivateKey()),
            'prepay_id' => $prepayId,
        ];
    }

    /**
     * APP 调起支付参数（RSA 签名，字段全小写以对齐原生 SDK）
     * @param string $appId
     * @param string $prepayId
     * @return array
     * @throws BaseException
     */
    private function appParams($appId, $prepayId)
    {
        $timeStamp = (string)Formatter::timestamp();
        $nonceStr  = Formatter::nonce();
        // APP 支付签名串顺序：appid / timestamp / noncestr / prepayid
        $message = Formatter::joinedByLineFeed($appId, $timeStamp, $nonceStr, $prepayId);

        return [
            'appid'     => $appId,
            'partnerid' => $this->config['mchid'],
            'prepayid'  => $prepayId,
            'package'   => 'Sign=WXPay',
            'noncestr'  => $nonceStr,
            'timestamp' => $timeStamp,
            'sign'      => Rsa::sign($message, $this->merchantPrivateKey()),
            'signType'  => 'RSA',
        ];
    }

    /**
     * 大小写不敏感地读取请求头
     * @param array  $headers
     * @param string $name
     * @return string
     */
    private function header(array $headers, $name)
    {
        $target = strtolower($name);
        foreach ($headers as $key => $value) {
            if (strtolower((string)$key) === $target) {
                return is_array($value) ? (string)reset($value) : (string)$value;
            }
        }
        return '';
    }

    /**
     * 校验必填配置
     * @throws BaseException
     */
    private function checkConfig()
    {
        $required = ['mchid', 'serial_no', 'private_key', 'apiv3_key', 'app_id', 'notify_url'];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                throw new BaseException(['msg' => "微信支付 v3 配置缺失：{$key}"]);
            }
        }
    }
}
