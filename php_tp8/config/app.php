<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    // 旧版 TP5 首页应用 home 映射到 h5（兼容旧链接 index.php?s=/home/...）。
    // 注意：think-multi-app 会把「app_map 的 value」视为不可直连（404 app not exists），
    // 故 h5 必须同时作为 key 自映射，否则 s=/h5/... 直连全部 404。
    'app_map'          => ['home' => 'h5', 'h5' => 'h5'],
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
