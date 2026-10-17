<?php
// 临时：验证砍价全链路（对齐 RN 契约）
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use app\api\service\bargain\Active as ActiveService;
use app\api\service\bargain\Task as TaskService;

$activeId = (int)trim(@file_get_contents('/tmp/bargain_active_id.txt')) ?: 1;
$userId = 10011;

echo "=== active/lists ===\n";
$list = ActiveService::getHallList(10001, [])['data'];
echo "count=" . count($list) . " first=" . json_encode($list[0] ?? null, JSON_UNESCAPED_UNICODE) . "\n";

echo "=== active/detail ===\n";
$a = ActiveService::getDetail($activeId);
echo "floor={$a['floor_price']} original={$a['original_price']} peoples={$a['peoples']} image=" . substr($a['image'], 0, 40) . " goods={$a['goods_name']} isPartake=" . ActiveService::getWhetherPartake($activeId, $userId) . "\n";

echo "=== task/lists before ===\n";
echo "count=" . count(TaskService::getMyList($userId)) . "\n";

echo "=== partake ===\n";
$res = TaskService::partake($userId, $activeId, '0');
echo json_encode($res) . "\n";
$taskId = $res['task_id'];

echo "=== task/detail after partake ===\n";
$d = TaskService::getTaskDetail($taskId);
echo "cut_money={$d['cut_money']} actual={$d['actual_price']} surplus={$d['surplus_money']} rate={$d['cut_rate']} peoples={$d['peoples']} cut_people={$d['cut_people']}\n";

echo "=== help_cut x3 (peoples=3) ===\n";
foreach ([10011, 10012, 10013] as $u) {
    $r = TaskService::helpCut($taskId, $u);
    echo "user$u => " . json_encode($r) . "\n";
    $d = TaskService::getTaskDetail($taskId);
    echo "  actual={$d['actual_price']} cut_people={$d['cut_people']} is_floor={$d['is_floor']} status={$d['status']}\n";
}
$r = TaskService::helpCut($taskId, 10011);
echo "dup user10011 => " . json_encode($r) . " (期望 error 已帮砍过)\n";

$d = TaskService::getTaskDetail($taskId);
echo "=== final: actual={$d['actual_price']}(期望30) cut_money={$d['cut_money']}(期望50) surplus={$d['surplus_money']}(期望0) is_floor={$d['is_floor']}(期望1) status={$d['status']}(期望2)\n";
