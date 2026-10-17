<?php
// 临时：PHP 内 curl 验证砍价 HTTP 端点（含登录 token 注入）
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use think\facade\Cache;

$token = 'bargain_test_token';
Cache::set($token, ['wxapp_id' => 10001, 'user_id' => 10011, 'open_id' => '', 'google_id' => ''], 86400 * 7);

function api($method, $path, $token, $post = null)
{
    $url = "http://127.0.0.1:8080/api/$path";
    $sep = strpos($url, '?') === false ? '?' : '&';
    if ($method === 'GET') {
        $url .= $sep . "wxapp_id=10001&token=$token";
    } else {
        $url .= "?wxapp_id=10001&token=$token";
    }
    $ch = curl_init($url);
    $opts = [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10];
    if ($method === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $post ?? [];
    }
    curl_setopt_array($ch, $opts);
    $r = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    if ($code === 500 && $r !== false) {
        static $dbg = 0;
        file_put_contents('/tmp/bargain_500_' . ($dbg++) . '.html', $r);
        $txt = trim(preg_replace('/\s+/', ' ', strip_tags($r)));
        foreach (['Call to', 'Undefined', 'Argument', 'Cannot', 'Class', 'Method'] as $kw) {
            if (preg_match('/' . $kw . '[^.]{0,200}/', $txt, $m)) {
                return "500 ERRMSG: " . $m[0];
            }
        }
        return "500 (no kw) " . substr($txt, 0, 300);
    }
    return "$code " . ($r === false ? "ERR:$err" : substr($r, 0, 400));
}

$activeId = (int)trim(@file_get_contents('/tmp/bargain_active_id.txt')) ?: 2;

echo "GET bargain/active/lists:\n" . api('GET', 'bargain/active/lists', $token) . "\n\n";
echo "GET bargain/active/detail:\n" . api('GET', "bargain/active/detail?active_id=$activeId", $token) . "\n\n";
echo "GET bargain/task/lists:\n" . api('GET', 'bargain/task/lists', $token) . "\n\n";

$partake = api('POST', 'bargain/task/partake', $token, ['active_id' => $activeId, 'goods_sku_id' => '0']);
echo "POST bargain/task/partake:\n" . $partake . "\n";
preg_match('/"task_id":(\d+)/', $partake, $m);
$taskId = $m[1] ?? 0;
echo " -> taskId=$taskId\n\n";

echo "GET bargain/task/detail:\n" . api('GET', "bargain/task/detail?task_id=$taskId", $token) . "\n\n";
echo "POST bargain/task/help_cut:\n" . api('POST', 'bargain/task/help_cut', $token, ['task_id' => $taskId]) . "\n";
