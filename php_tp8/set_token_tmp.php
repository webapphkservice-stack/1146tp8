<?php
// 临时脚本：注入测试用登录 token（验证后删除）
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
$token = 'testtoken123';
$session = [
    'wxapp_id'  => 10001,
    'user_id'   => 10022,
    'open_id'   => '',
    'google_id' => '',
];
think\facade\Cache::set($token, $session, 86400 * 7);
echo "token set: {$token}\n";
