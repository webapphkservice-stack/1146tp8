<?php
// 临时：准备砍价测试数据（验证后删除）
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use think\facade\Db;

$wxappId = 10001;
Db::execute('DELETE FROM yoshop_bargain_active');
Db::execute('DELETE FROM yoshop_bargain_task');
Db::execute('DELETE FROM yoshop_bargain_task_help');
Db::execute('DELETE FROM yoshop_bargain_setting');

$activeId = Db::name('bargain_active')->insertGetId([
    'goods_id'      => 10002,
    'start_time'    => time() - 100,
    'end_time'      => time() + 86400,
    'expiryt_time'  => 86400,
    'floor_price'   => 30.00,
    'peoples'       => 3,
    'is_self_cut'   => 1,
    'is_floor_buy'  => 0,
    'share_title'   => '砍价测试',
    'prompt_words'  => '快来砍价',
    'actual_sales'  => 2,
    'initial_sales' => 3,
    'sort'          => 1,
    'status'        => 1,
    'is_delete'     => 0,
    'wxapp_id'      => $wxappId,
    'create_time'   => time(),
    'update_time'   => time(),
]);
Db::name('bargain_setting')->insertAll([
    ['key' => 'bargain_title', 'describe' => '', 'values' => '砍价规则', 'wxapp_id' => $wxappId, 'update_time' => time()],
    ['key' => 'bargain_desc', 'describe' => '', 'values' => '邀请好友帮忙砍价', 'wxapp_id' => $wxappId, 'update_time' => time()],
]);
file_put_contents('/tmp/bargain_active_id.txt', $activeId);
echo "prepared bargain activeId=$activeId\n";
