<?php
// POC 自测：生成本地 RSA 密钥对，写入 JWKS，并用私钥签发一个合法 id_token
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

$webClientId = '212689588897-8kagstio2acdc2lk08u41bksnk5r2sjv.apps.googleusercontent.com';
$kid = 'poc-key-1';

// 1. 生成 RSA 密钥对
$res = openssl_pkey_new([
    'digest_alg'       => 'sha256',
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'bits'             => 2048,
]);
openssl_pkey_export($res, $privateKey);
$details = openssl_pkey_get_details($res);

// 2. 构造 JWKS（n/e 为 base64url 编码的二进制大端整数）
$b64u = function ($bin) {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
};
$jwk = [
    'kty' => 'RSA',
    'n'   => $b64u($details['rsa']['n']),
    'e'   => $b64u($details['rsa']['e']),
    'kid' => $kid,
    'alg' => 'RS256',
    'use' => 'sig',
];
file_put_contents(__DIR__ . '/runtime/jwks.json', json_encode(['keys' => [$jwk]], JSON_PRETTY_PRINT));

// 3. 签发 id_token（aud 必须与上面 web_client_id 一致）
$payload = [
    'iss'  => 'accounts.google.com',
    'aud'  => $webClientId,
    'sub'  => '108765432109876543210',
    'iat'  => time(),
    'exp'  => time() + 3600,
    'email' => 'poc@example.com',
];
$token = JWT::encode($payload, $privateKey, 'RS256', $kid);

// 4. 输出 token，供 curl 使用
file_put_contents(__DIR__ . '/runtime/test_token.txt', $token);
echo $token . "\n";
echo "JWKS written to runtime/jwks.json\n";
