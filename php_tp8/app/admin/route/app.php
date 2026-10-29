<?php
use think\facade\Route;

// admin 应用（总后台）路由。URL 自动带 /admin 前缀。
// 登录接口免鉴权，其余接口统一走 Auth 中间件（校验 Admin-Token）。

// 认证（免登录）
Route::post('passport/login', 'Passport/login');

// 需要登录态的后台接口
Route::group(function () {
    // 退出
    Route::post('passport/logout', 'Passport/logout');

    // 仪表盘概览
    Route::get('dashboard', 'Dashboard/index');

    // 小程序商城管理
    Route::get('store', 'Store/index');
    Route::get('store/recycle', 'Store/recycle');
    Route::get('store/detail', 'Store/detail');
    Route::post('store/add', 'Store/add');
    Route::get('store/enter', 'Store/enter');
    Route::post('store/recycle', 'Store/recycleWxapp');
    Route::post('store/recovery', 'Store/recoveryWxapp');
    Route::post('store/delete', 'Store/delete');

    // 基础设置
    Route::get('setting', 'Setting/get');
    Route::post('setting', 'Setting/save');

    // 网站信息（备案信息 / 联系信息）
    Route::get('setting.web/index', 'setting.Web/index');
    Route::post('setting.web/index', 'setting.Web/index');

    // 网站信息 logo 上传
    Route::post('setting.upload/image', 'setting.Upload/image');

    // 航班管理（后台维护 + Bright Data 配置 + 拉取调试）
    Route::get('flight/index', 'Flight/index');
    Route::post('flight/add', 'Flight/add');
    Route::post('flight/edit', 'Flight/edit');
    Route::post('flight/delete', 'Flight/delete');
    Route::get('flight/setting', 'Flight/setting');
    Route::post('flight/setting', 'Flight/setting');
    Route::post('flight/fetch', 'Flight/fetch');

    // 系统设置：清理缓存 / 环境检测 / 外送回写密钥
    Route::post('setting.cache/clear', 'setting.Cache/clear');
    Route::get('setting.science/index', 'setting.Science/index');
    Route::get('setting.delivery_notify/index', 'setting.DeliveryNotify/index');
    Route::post('setting.delivery_notify/index', 'setting.DeliveryNotify/index');

    // 商户后台权限管理（RBAC）
    Route::get('store.access/index', 'store.Access/index');
    Route::post('store.access/add', 'store.Access/add');
    Route::post('store.access/edit', 'store.Access/edit');
    Route::post('store.access/delete', 'store.Access/delete');

    // 外送回写任务队列
    Route::get('delivery.notify_job/index', 'delivery.NotifyJob/index');
    Route::post('delivery.notify_job/retry', 'delivery.NotifyJob/retry');

    // 超管账号维护（改账号/密码）
    Route::post('admin.user/renew', 'admin.User/renew');
})->middleware(\app\admin\middleware\Auth::class);
