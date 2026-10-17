<?php
// Google 登录配置（web_client_id 仅作兜底；
// 实际校验优先使用 setting 表中的 google.client_id / web_client_id）
return [
    // 留空则由 Setting::getItem('google') 提供
    'web_client_id' => '',
];
