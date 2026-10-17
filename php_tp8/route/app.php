<?php
use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

// 多应用模式下，应用专属路由请放到 app/<app>/route/ 目录（由 think-multi-app 自动加载），
// 全局 route/ 目录仅保留真正跨应用的路由。例如：
//   POST /api/user/googleLogin  -> app/api/controller/User@googleLogin  （见 app/api/route/app.php）
//   GET  /api/jwks.json         -> app/api/controller/User@jwks          （见 app/api/route/app.php）
