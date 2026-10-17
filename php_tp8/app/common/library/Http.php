<?php
namespace app\common\library;

/**
 * 极简 HTTP 客户端（移植自 TP5 common/library/Http.php 的 postJson）
 *
 * 仅提供项目实际用到的能力：POST JSON。
 * 注意：PHP 8.5 起 curl_close() 已弃用（调用会抛异常），这里不显式关闭句柄。
 */
class Http
{
    /**
     * POST JSON
     * @param string $url
     * @param array  $payload
     * @param int    $timeout 秒
     * @return array ['success' => bool, 'error' => string, 'http_code' => int, 'body' => string]
     */
    public static function postJson($url, array $payload = [], $timeout = 5)
    {
        $result = [
            'success'   => false,
            'error'     => '',
            'http_code' => 0,
            'body'      => '',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $body = curl_exec($ch);
        if ($body === false) {
            $result['error'] = curl_error($ch);
            unset($ch);
            return $result;
        }

        $result['http_code'] = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['body']      = (string)$body;
        $result['success']   = $result['http_code'] >= 200 && $result['http_code'] < 300;
        if (!$result['success'] && $result['error'] === '') {
            $result['error'] = 'HTTP ' . $result['http_code'];
        }

        unset($ch);
        return $result;
    }
}
