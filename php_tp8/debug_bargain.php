<?php
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use think\facade\Cache;
use app\api\service\bargain\Active as ActiveService;

$token = 'bargain_test_token';
Cache::set($token, ['wxapp_id' => 10001, 'user_id' => 10011, 'open_id' => '', 'google_id' => ''], 86400 * 7);

echo "=== getSetting ===\n";
try {
    $s = ActiveService::getSetting(10001);
    echo json_encode($s) . "\n";
} catch (\Throwable $e) {
    echo "EXC: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== getDetail ===\n";
try {
    $d = ActiveService::getDetail(2);
    echo json_encode($d) . "\n";
} catch (\Throwable $e) {
    echo "EXC: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== getGoodsBrief ===\n";
try {
    $g = ActiveService::getGoodsBrief(10002);
    echo json_encode($g) . "\n";
} catch (\Throwable $e) {
    echo "EXC: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== getWhetherPartake ===\n";
try {
    $w = ActiveService::getWhetherPartake(2, 10011);
    echo "taskId=$w\n";
} catch (\Throwable $e) {
    echo "EXC: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}
