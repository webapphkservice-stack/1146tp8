<?php
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use think\facade\Cache;
use think\facade\Http;

$token = 'bargain_test_token';
Cache::set($token, ['wxapp_id' => 10001, 'user_id' => 10011, 'open_id' => '', 'google_id' => ''], 86400 * 7);

function dispatch($method, $path, $token, $post = [])
{
    $qs = "?wxapp_id=10001&token=$token";
    $uri = '/api/' . $path . $qs;
    $server = [
        'REQUEST_METHOD' => $method,
        'REQUEST_URI' => $uri,
        'PATH_INFO' => '/api/' . $path,
        'SCRIPT_NAME' => '/index.php',
        'QUERY_STRING' => ltrim($qs, '?'),
    ];
    if ($method === 'POST') {
        $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    }
    try {
        $resp = Http::withMethod($method)
            ->withHeader(['content-type' => 'application/x-www-form-urlencoded'])
            ->withServer($server)
            ->withPost($post)
            ->then(fn() => true)
            ->run();
        echo "STATUS=" . $resp->getCode() . "\n";
        echo substr($resp->getContent(), 0, 600) . "\n";
    } catch (\Throwable $e) {
        echo "DISPATCH EXC: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
}

echo "##### active/detail #####\n";
dispatch('GET', 'bargain/active/detail?active_id=2', $token);
echo "\n##### task/partake #####\n";
dispatch('POST', 'bargain/task/partake', $token, ['active_id' => 2, 'goods_sku_id' => '0']);
