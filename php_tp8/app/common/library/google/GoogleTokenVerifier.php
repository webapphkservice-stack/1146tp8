<?php
namespace app\common\library\google;

use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\SignatureInvalidException;
use think\facade\Cache;

/**
 * 复用生产版逻辑（firebase/php-jwt v7+ API）：
 *   - JWT::decode($jwt, $keySet)  不再接收 $allowedAlgs，算法由 Key 对象携带
 *   - JWK::parseKeySet($keys, 'RS256')  传默认算法
 *
 * POC 仅增加：$jwksFile 静态属性，允许从本地文件读取 JWKS（避免内置服务器同端口递归死锁）。
 * 生产环境保持 $jwksFile = ''，走 Google 官方地址。
 */
class GoogleTokenVerifier
{
    private const GOOGLE_JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    /** @var string 本地 JWKS 文件路径（POC 自测用，生产留空） */
    public static $jwksFile = '';

    /** @var string JWKS 地址（生产默认 Google 官方） */
    public static $jwksUrl = self::GOOGLE_JWKS_URL;

    /**
     * 校验 Google id_token，返回 payload（含 sub）
     * @param string $idToken
     * @param string|array $allowedClientIds 允许的一个或多个 client_id（支持逗号分隔字符串）
     */
    public static function verify(string $idToken, $allowedClientIds): object
    {
        $lastErr = null;
        foreach ([false, true] as $forceRefresh) {
            $keys = self::getJwks($forceRefresh);
            try {
                $keySet  = JWK::parseKeySet($keys, 'RS256');
                $payload = JWT::decode($idToken, $keySet);
                self::assertClaims($payload, $allowedClientIds);
                return $payload;
            } catch (\Exception $e) {
                $lastErr = $e;
            }
        }
        throw new Exception('Google 登录验证失败：' . $lastErr->getMessage());
    }

    private static function getJwks(bool $forceRefresh): array
    {
        $cacheKey = 'google_jwks';
        if (self::$jwksFile !== '') {
            $json = @file_get_contents(self::$jwksFile);
        } else {
            if (!$forceRefresh) {
                $cached = Cache::get($cacheKey);
                if ($cached) {
                    return $cached;
                }
            }
            $json = @file_get_contents(self::$jwksUrl);
        }
        if ($json === false || $json === '') {
            throw new Exception('获取 Google 公钥失败');
        }
        $keys = json_decode($json, true);
        if (empty($keys['keys'])) {
            throw new Exception('Google 公钥格式异常');
        }
        if (self::$jwksFile === '') {
            Cache::set($cacheKey, $keys, 3600);
        }
        return $keys;
    }

    private static function assertClaims($payload, $allowedClientIds): void
    {
        $iss = $payload->iss ?? '';
        if (!in_array($iss, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new Exception('iss 不匹配');
        }
        // 允许一个或多个 client_id（逗号分隔字符串或数组）
        $allowed = is_array($allowedClientIds)
            ? $allowedClientIds
            : array_filter(array_map('trim', explode(',', (string) $allowedClientIds)));
        $aud = $payload->aud ?? null;
        $audArr = is_array($aud) ? $aud : [$aud];
        if (empty($allowed) || !array_intersect($audArr, $allowed)) {
            throw new Exception('aud 不匹配');
        }
        if (empty($payload->sub)) {
            throw new Exception('sub 为空');
        }
    }
}
