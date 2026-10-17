<?php
require __DIR__ . '/../vendor/autoload.php';
$app = new \think\App();
$app->initialize();
\app\common\model\BaseModel::setApiWxappId(10001);
try {
    $list = (new \app\common\model\VipOrder())->getList([]);
    echo "OK total=" . $list->total() . "\n";
} catch (\Throwable $e) {
    echo "ERR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
}
