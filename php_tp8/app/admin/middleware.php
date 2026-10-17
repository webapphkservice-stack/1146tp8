<?php
// admin 应用中间件定义文件

return [
    // Session 初始化：enter() 接口给 store 应用写入免登录态依赖 Session 持久化
    \think\middleware\SessionInit::class,
];
