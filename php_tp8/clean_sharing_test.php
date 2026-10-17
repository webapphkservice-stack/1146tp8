<?php
// 清理拼团测试数据 + 临时脚本（验证后删除）
date_default_timezone_set('Asia/Shanghai');
require __DIR__ . '/vendor/autoload.php';
$app = new think\App();
$app->initialize();
use think\facade\Db;

Db::execute('DELETE FROM yoshop_sharing_category');
Db::execute('DELETE FROM yoshop_sharing_goods');
Db::execute('DELETE FROM yoshop_sharing_goods_sku');
Db::execute('DELETE FROM yoshop_sharing_active');
Db::execute('DELETE FROM yoshop_sharing_active_users');
Db::execute('DELETE FROM yoshop_sharing_goods_image');

foreach (['prep_sharing_test.php', 'test_sharing.php', 'test_sharing_http.php'] as $f) {
    @unlink(__DIR__ . '/' . $f);
}
echo "cleaned sharing test data + temp files\n";
