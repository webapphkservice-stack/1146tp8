<?php
use think\facade\Route;

// POC 仅演示：api 应用下的登录接口 + 本地 JWKS（验签自测用）
Route::post('user/googleLogin', 'user/googleLogin');
Route::get('jwks.json', 'user/jwks');
