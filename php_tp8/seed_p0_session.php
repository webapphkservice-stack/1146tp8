<?php
// P0 端到端测试专用：注入登录态缓存（token=p0token123 -> user_id=10010）
// 说明：既有 seed_session.php 硬编码 user_id=10022，而该用户在当前测试库中不存在，
//       会导致 UserModel::detail() 返回 null，故本脚本改用真实存在的 10010。
// 用法：php seed_p0_session.php
require __DIR__ . '/vendor/autoload.php';
$app = new think\App(__DIR__);
$app->initialize();

\think\facade\Cache::set('p0token123', [
    'user_id'  => 10011,
    'open_id'  => '',
    'wxapp_id' => 10001,
], 0);

$user = \think\facade\Db::name('user')->where('user_id', 10011)->find();
echo "session seeded ok, user_id=10011, exists=" . ($user ? 'yes' : 'no') . "\n";
