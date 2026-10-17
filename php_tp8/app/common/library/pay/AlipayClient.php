<?php
namespace app\common\library\pay;

use app\common\exception\BaseException;
use think\facade\Log;

/**
 * 支付宝开放平台通用请求客户端（RSA2 签名，原生实现，零 SDK 依赖）
 *
 * 完整移植自 php_my common/library/alipay/AlipayClient.php，改动点：
 *  1. 命名空间改为 app\common\library\pay，异常用 app\common\exception\BaseException。
 *  2. 移除 openssl_free_key()（PHP 8 已废弃，调用会触发 deprecated）。
 *  3. 日志改用 think\facade\Log。
 *
 * 关键约定（踩坑记录，勿随意改动）：
 *  - 公共参数必须放在 URL 查询串中，否则网关报「验签出错，请确认 charset 参数放在了 URL 查询字符串中」。
 *  - 签名原文 = 「公共参数 + 业务参数」全集，且 sign_type 必须参与请求签名（与异步通知验签规则不同）。
 *  - 验签必须基于响应原始字节（未转码），网关异常时可能返回 GBK 页面，需先转 UTF-8 再 json_decode。
 */
class AlipayClient
{
    /** 支付宝网关 */
    const GATEWAY_URL = 'https://openapi.alipay.com/gateway.do';

    /** @var array app_id / private_key / public_key */
    private $config;

    /** @var string 最近一次响应原文 */
    private $lastResponse = '';

    /** @var int 最近一次 HTTP 状态码 */
    private $lastHttpCode = 0;

    /**
     * @param array $config
     * @throws BaseException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->checkConfig();
    }

    /**
     * 执行请求
     * @param string      $method      接口名，如 alipay.trade.app.pay
     * @param array       $bizContent  业务参数（放入 biz_content）
     * @param string|null $appAuthToken
     * @param string|null $notifyUrl
     * @param array       $flatParams  平级业务参数
     * @return array
     * @throws BaseException
     */
    public function execute($method, array $bizContent = [], $appAuthToken = null, $notifyUrl = null, array $flatParams = [])
    {
        $sysParams = [
            'app_id'    => $this->config['app_id'],
            'method'    => $method,
            'format'    => 'JSON',
            'charset'   => 'UTF-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0',
        ];
        if (!empty($notifyUrl)) {
            $sysParams['notify_url'] = $notifyUrl;
        }
        if (!empty($appAuthToken)) {
            $sysParams['app_auth_token'] = $appAuthToken;
        }

        $apiParams = [];
        if (!empty($bizContent)) {
            $apiParams['biz_content'] = json_encode($bizContent, JSON_UNESCAPED_UNICODE);
        }
        foreach ($flatParams as $key => $value) {
            if ($value !== '' && !is_null($value)) {
                $apiParams[$key] = $value;
            }
        }

        // 签名基于「公共参数 + 业务参数」全集（未做 URL 编码）
        $sysParams['sign'] = $this->generateSign(array_merge($sysParams, $apiParams));
        $requestUrl = self::GATEWAY_URL . '?' . $this->buildQuery($sysParams);

        $raw = $this->curlPost($requestUrl, $apiParams);
        $this->lastResponse = $raw;

        $response = $this->toUtf8($raw);
        $result   = json_decode($response, true);
        if (!is_array($result)) {
            Log::write('支付宝接口返回非JSON[' . $method . ']：' . $response, 'error');
            throw new BaseException([
                'msg' => '支付宝接口返回格式异常(HTTP ' . $this->lastHttpCode . ')：' . $this->safeSnippet($response),
            ]);
        }

        // error_response 需优先识别，否则会被下面的结构判断误报为「格式异常」而掩盖真因
        if (isset($result['error_response']) && is_array($result['error_response'])) {
            $err = $result['error_response'];
            $msg = $err['sub_msg'] ?? ($err['msg'] ?? '未知错误');
            throw new BaseException(['msg' => '支付宝接口错误：' . $msg]);
        }

        $responseKey = str_replace('.', '_', $method) . '_response';
        if (!isset($result[$responseKey]) || !is_array($result[$responseKey])) {
            throw new BaseException(['msg' => '支付宝接口返回格式异常：缺少 ' . $responseKey]);
        }
        $responseData = $result[$responseKey];

        if (isset($responseData['code']) && $responseData['code'] != '10000') {
            $msg = "支付宝接口错误：{$responseData['msg']}";
            if (!empty($responseData['sub_msg'])) {
                $msg .= "（{$responseData['sub_msg']}）";
            }
            throw new BaseException(['msg' => $msg]);
        }

        // 验签（error_response 不带 sign，故仅在有 sign 时校验）
        $responseJson = $this->extractResponseJson($raw, $responseKey);
        if (!empty($responseJson) && isset($result['sign']) && !$this->verifySign($responseJson, $result['sign'])) {
            throw new BaseException(['msg' => '支付宝返回结果验签失败']);
        }

        return $responseData;
    }

    /**
     * 生成本地已签名的请求串（App 支付专用，对应官方 SDK 的 sdkExecute）
     *
     * 与 execute() 的区别：不请求支付宝网关，直接在本地完成「公共参数 + biz_content + 签名」
     * 的拼接并返回 URL 编码后的完整串，原生 SDK 用该串即可调起收银台。
     * 这是支付宝 App 支付（alipay.trade.app.pay）的标准用法：网关对该接口并不返回 order_string。
     *
     * @param string      $method
     * @param array       $bizContent
     * @param string|null $notifyUrl
     * @param string|null $appAuthToken
     * @return string 形如 app_id=xxx&biz_content=xxx&charset=UTF-8&...&sign=xxx
     * @throws BaseException
     */
    public function sdkExecute($method, array $bizContent = [], $notifyUrl = null, $appAuthToken = null)
    {
        $params = [
            'app_id'    => $this->config['app_id'],
            'method'    => $method,
            'format'    => 'JSON',
            'charset'   => 'UTF-8',
            'sign_type' => 'RSA2',
            'timestamp' => date('Y-m-d H:i:s'),
            'version'   => '1.0',
        ];
        if (!empty($notifyUrl)) {
            $params['notify_url'] = $notifyUrl;
        }
        if (!empty($appAuthToken)) {
            $params['app_auth_token'] = $appAuthToken;
        }
        if (!empty($bizContent)) {
            $params['biz_content'] = json_encode($bizContent, JSON_UNESCAPED_UNICODE);
        }

        // 待签原文与签名均基于「未 URL 编码」的值（与 generateSign 内部规则一致）
        $params['sign'] = $this->generateSign($params);
        return $this->buildQuery($params);
    }

    /**
     * 生成 RSA2 签名
     * @param array $params
     * @return string
     * @throws BaseException
     */
    public function generateSign(array $params)
    {
        // 仅过滤 sign 与空值；sign_type 必须参与请求签名
        $filtered = [];
        foreach ($params as $key => $value) {
            if ($key === 'sign' || $value === '' || is_null($value)) {
                continue;
            }
            $filtered[$key] = $value;
        }
        ksort($filtered);

        $privateKey = $this->formatKey($this->config['private_key'], 'private');
        $res = openssl_get_privatekey($privateKey);
        if (!$res) {
            throw new BaseException(['msg' => '支付宝应用私钥格式错误']);
        }
        openssl_sign($this->getSignContent($filtered), $sign, $res, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * 验证支付宝签名
     * @param string $content 待验签原文
     * @param string $sign    base64 签名
     * @return bool
     * @throws BaseException
     */
    public function verifySign($content, $sign)
    {
        $publicKey = $this->formatKey($this->config['public_key'], 'public');
        $res = openssl_get_publickey($publicKey);
        if (!$res) {
            throw new BaseException(['msg' => '支付宝公钥格式错误']);
        }
        $result = openssl_verify($content, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

    /**
     * 验证异步通知签名（通知参数为 key=value 形式）
     * @param array $params
     * @return bool
     * @throws BaseException
     */
    public function verifyNotifySign(array $params)
    {
        if (empty($params['sign'])) {
            return false;
        }
        $sign = $params['sign'];
        unset($params['sign'], $params['sign_type']);

        $filtered = [];
        foreach ($params as $k => $v) {
            if ($v !== '' && !is_null($v)) {
                $filtered[$k] = $v;
            }
        }
        ksort($filtered);
        return $this->verifySign($this->getSignContent($filtered), $sign);
    }

    /**
     * 最近一次响应原文
     * @return string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    // -------------------- 内部方法 --------------------

    private function checkConfig()
    {
        foreach (['app_id', 'private_key', 'public_key'] as $key) {
            if (empty($this->config[$key])) {
                throw new BaseException(['msg' => "支付宝配置缺失：{$key}"]);
            }
        }
    }

    /**
     * 从原始响应中提取 _response 对应的原始 JSON 子串（验签需保持字段原貌）
     */
    private function extractResponseJson($response, $responseKey)
    {
        $pattern = '/"' . preg_quote($responseKey, '/') . '":(\{.*?\})(?:,"sign"|\})/s';
        return preg_match($pattern, $response, $matches) ? $matches[1] : '';
    }

    private function buildQuery(array $params)
    {
        $pairs = [];
        foreach ($params as $key => $value) {
            if ($value === '' || is_null($value)) {
                continue;
            }
            $pairs[] = $key . '=' . urlencode($value);
        }
        return implode('&', $pairs);
    }

    private function getSignContent(array $params)
    {
        $pairs = [];
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $pairs[] = "{$key}={$value}";
        }
        return implode('&', $pairs);
    }

    /**
     * 格式化密钥（自动补全 PEM 头尾）
     */
    private function formatKey($key, $type)
    {
        $key = trim((string)$key);
        if (strpos($key, '-----BEGIN') !== false) {
            return $key;
        }
        $key = str_replace(["\r", "\n", ' '], '', $key);
        $chunks = chunk_split($key, 64, "\n");
        if ($type === 'private') {
            return "-----BEGIN RSA PRIVATE KEY-----\n" . $chunks . "-----END RSA PRIVATE KEY-----\n";
        }
        return "-----BEGIN PUBLIC KEY-----\n" . $chunks . "-----END PUBLIC KEY-----\n";
    }

    /**
     * 安全转换为 UTF-8（网关异常页可能为 GBK，非法字节会导致 json_encode 抛异常）
     */
    private function toUtf8($text)
    {
        $text = (string)$text;
        if ($text === '' || mb_check_encoding($text, 'UTF-8')) {
            return $text;
        }
        $converted = @mb_convert_encoding($text, 'UTF-8', 'GB18030,GBK,BIG5,ISO-8859-1');
        if (is_string($converted) && $converted !== '' && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
        $ignored = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        return is_string($ignored) ? $ignored : '';
    }

    private function safeSnippet($text, $length = 200)
    {
        $text = $this->toUtf8($text);
        return $text === '' ? '(响应为空或编码无法识别)' : mb_substr($text, 0, $length, 'UTF-8');
    }

    private function curlPost($url, array $params)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded;charset=utf-8'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_ENCODING       => '', // 自动解压 gzip/deflate
        ]);
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // PHP 8.5 起 curl_close() 已废弃（8.0 之后无实际作用），调用会触发 deprecated 异常
        $ch = null;
        if ($error) {
            throw new BaseException(['msg' => '支付宝请求网络错误：' . $this->safeSnippet($error)]);
        }
        return (string)$response;
    }
}
