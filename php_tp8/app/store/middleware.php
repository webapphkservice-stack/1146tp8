<?php
// store 应用中间件定义文件

return [
    // Session 初始化：商家后台为服务端渲染，登录态基于 Session（与 TP5 一致），
    // 必须启用；仅作用于 store 应用，不影响 api 等 JSON API 应用。
    \think\middleware\SessionInit::class,
];
