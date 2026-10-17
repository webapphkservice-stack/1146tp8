<?php
use think\facade\Route;

// task 应用路由（URL 自动带 /task 前缀）
// 说明：task 主体是命令行任务（config/console.php 注册的 command），
//       这里仅保留需要被外部以 URL 触发的入口（兼容旧 crontab 用 curl 调用）。
//       新部署建议直接用：php think delivery:notify

// 外送订单商户侧 webhook 回写队列消费（?key=xxx 可选密钥校验）
Route::get('delivery_notify/index', 'DeliveryNotify/index');
Route::post('delivery_notify/index', 'DeliveryNotify/index');
