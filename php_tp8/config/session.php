<?php
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------

return [
    // session name
    'name'           => 'PHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 1440,
    // 前缀
    'prefix'         => '',
    // 多应用模式下强制公共存储目录（TP8 Session File driver 读的是 path 不是 save_path）
    // admin/store/api 三个应用共享同一个 PHPSESSID，enter() 跨应用免登录依赖此配置
    'path'           => __DIR__ . '/../runtime/session',
];
