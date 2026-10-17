<?php
// 临时脚本：为端到端测试注入登录态缓存（token=testtoken123 -> user_id=10022）
require __DIR__ . '/vendor/autoload.php';
$app = new think\App(__DIR__);
$app->initialize();
\think\facade\Cache::set('testtoken123', [
    'user_id'  => 10022,
    'open_id'  => '',
    'wxapp_id' => 10001,
], 0);
echo "session seeded ok\n";
