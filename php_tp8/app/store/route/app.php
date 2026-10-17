<?php
use think\facade\Route;

// store 应用（商家后台）路由。URL 自动带 /store 前缀。
// 登录相关接口无需鉴权，其余接口统一走 Auth 中间件（校验 Store-Token）。

// 认证（免登录）
Route::post('passport/login', 'Passport/login');

// 需要登录态的后台接口
Route::group(function () {
    // 商家信息 / 退出
    Route::get('passport/detail', 'Passport/detail');
    Route::post('passport/logout', 'Passport/logout');

    // 订单管理（发货闭环）JSON API
    // 注意：必须走 api/ 前缀避让页面路由。TP8 会把 /order/detail/order_id/12 的额外段
    // 解析为参数后匹配到 'order/detail'，同名路由会截获后台页面请求（返回请先登录）。
    Route::get('api/order/lists', 'OrderApi/lists');
    Route::get('api/order/detail', 'OrderApi/detail');
    Route::post('api/order/delivery', 'OrderApi/delivery');
    Route::post('api/order/confirmCancel', 'OrderApi/confirmCancel');

    // 物流公司
    Route::get('express/lists', 'Express/lists');

    // 会员等级管理（B3）JSON API
    // 同样需 api/ 前缀避让页面路由：否则 'user.grade/index' 会截获后台页面请求
    Route::get('api/user.grade/index', 'user.GradeApi/index');
    Route::get('api/user.grade/detail', 'user.GradeApi/detail');
    Route::post('api/user.grade/add', 'user.GradeApi/add');
    Route::post('api/user.grade/edit', 'user.GradeApi/edit');
    Route::post('api/user.grade/delete', 'user.GradeApi/delete');
    Route::post('api/user.grade/state', 'user.GradeApi/state');

    // 数据选择器弹层（select.data.js 的 iframe 地址：/store/data.<type>/lists）
    Route::get('data.goods/lists', 'Data/goods');
    Route::get('data.user/lists', 'Data/user');
    Route::get('data.shop/lists', 'Data/shop');
    Route::get('data.sharp.goods/lists', 'Data/sharpGoods');
    Route::get('data.sharing.goods/lists', 'Data/sharingGoods');
    Route::get('data.bargain.goods/lists', 'Data/bargainGoods');

    // 文件库（file.library.js 的弹层接口）
    Route::post('upload/image', 'Upload/image');
    Route::get('upload.library/fileList', 'upload.Library/fileList');
    Route::post('upload.library/addGroup', 'upload.Library/addGroup');
    Route::post('upload.library/editGroup', 'upload.Library/editGroup');
    Route::post('upload.library/deleteGroup', 'upload.Library/deleteGroup');
    Route::post('upload.library/deleteFiles', 'upload.Library/deleteFiles');
    Route::post('upload.library/moveFiles', 'upload.Library/moveFiles');
})->middleware(\app\store\middleware\Auth::class);
