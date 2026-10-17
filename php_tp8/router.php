<?php
// php -S 内建服务器路由（仅本地开发用，生产走 nginx/Apache）
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($uri, '/goods/detail') === 0) {
    parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $qs);
    header('Location: /h5/goods/detail.html?' . http_build_query($qs), true, 302);
    exit;
}
if (strpos($uri, '/goods/list') === 0) {
    parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $qs);
    header('Location: /h5/goods/list.html?' . http_build_query($qs), true, 302);
    exit;
}
return false;
