<?php
// 第 0 批基建：MySQL 连接配置（取代 POC 的 SQLite）
// 凭据从 .env 读取，禁止把密码写进仓库。
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'type'     => 'mysql',
            'hostname' => env('DB_HOST', '127.0.0.1'),
            'database' => env('DB_NAME', 'yoshop_tp8'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASS', ''),
            'hostport' => env('DB_PORT', '3306'),
            'charset'  => 'utf8mb4',
            'prefix'   => 'mybaixin_',
            'debug'    => env('APP_DEBUG', true),
        ],
    ],
];
