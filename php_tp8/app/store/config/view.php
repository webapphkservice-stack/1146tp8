<?php
// store 应用（商家后台）模板设置
// 说明：TP5 商家后台使用「原生 PHP 模板 + layout 布局」，为保持 182 个视图模板可原样迁移，
// 此处沿用 TP5 的模板配置（view_suffix=php、layout 开启、{{ }} 标签），
// 与全局 config/view.php（html 后缀）区分，仅作用于 store 应用。

return [
    // 模板引擎类型
    'type'          => 'Think',
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀（原生 PHP 模板，与 TP5 一致）
    'view_suffix'   => 'php',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 标签标记（避免与模板内的 JS/CSS 花括号冲突）
    'tpl_begin'     => '{{',
    'tpl_end'       => '}}',
    'taglib_begin'  => '{{',
    'taglib_end'    => '}}',
    // 布局模板（等价于 TP5 store/config.php 的 template.layout_*）
    'layout_on'     => true,
    'layout_name'   => 'layouts/layout',
];
