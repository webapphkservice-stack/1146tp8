<?php
// admin 应用（总后台）模板设置
// 说明：为支持总后台登录页的服务端渲染（液态玻璃登录页），
// 此处沿用 store 应用一致的原生 PHP 模板配置（view_suffix=php），
// 但总后台绝大部分接口仍是 JSON API，故关闭 layout，仅登录页渲染独立模板。
return [
    // 模板引擎类型
    'type'          => 'Think',
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀（原生 PHP 模板）
    'view_suffix'   => 'php',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 标签标记（避免与模板内的 JS/CSS 花括号冲突）
    'tpl_begin'     => '{{',
    'tpl_end'       => '}}',
    'taglib_begin'  => '{{',
    'taglib_end'    => '}}',
    // 总后台接口以 JSON 为主，不套全局 layout
    'layout_on'     => false,
];
