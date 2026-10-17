<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title><?= $storeName ?? '百信优选' ?></title>
    <meta name="format-detection" content="telephone=no"/>
    <base href="/"/>
    <?php
    // 系统标题栏背景色（取自装修首页样式），供底部程序坞（Dock）配色，全站统一
    $h5TitleBg = '#ffffff';
    try {
        $homePage = \app\common\model\WxappPage::getHomePage();
        $h5Style  = $homePage['page_data']['page']['style'] ?? [];
        if (!empty($h5Style['titleBackgroundColor'])) {
            $h5TitleBg = $h5Style['titleBackgroundColor'];
        }
    } catch (\Throwable $e) {
    }
    ?>

<?php
    // 登录态 + 购物车总数（供 Dock 角标）
    $h5CartNum = 0;
    try {
        $token = $_SERVER['HTTP_H5_TOKEN'] ?? $_GET['token'] ?? $_COOKIE['h5_token'] ?? '';
        if ($token !== '') {
            $u = \think\facade\Cache::get('h5_user_token_' . $token);
            if (is_array($u) && !empty($u['user_id'])) {
                $h5CartNum = \app\api\model\Cart::getTotalNumByUser((int)$u['user_id']);
            }
        }
    } catch (\Throwable $e) { $h5CartNum = 0; }
?>
<script>window.__h5CartNum = <?= (int)$h5CartNum ?>;</script>
    <link rel="stylesheet" href="/assets/h5/css/style.css?v=<?=time()?>"/>
    <style>:root { --h5-title-bg: <?= htmlspecialchars($h5TitleBg, ENT_QUOTES) ?>; }</style>
    <script src="/assets/common/js/vue3.min.js"></script>
</head>
<body class="h5-body">
    <div id="h5-app" v-cloak>
        {__CONTENT__}
    </div>

    <?php
    // 底部导航栏当前激活项
    $tabUri = request()->url();
    $tabActive = 'home';
    if (strpos($tabUri, '/h5/category') === 0) {
        $tabActive = 'category';
    } elseif (strpos($tabUri, '/h5/cart') === 0) {
        $tabActive = 'cart';
    } elseif (strpos($tabUri, '/h5/user') === 0) {
        $tabActive = 'user';
    }
    ?>
    <nav class="h5-tabbar">
        <a href="/h5/page/index.html" class="h5-tabbar__item<?= $tabActive === 'home' ? ' is-active' : '' ?>" aria-label="首页" title="首页">
            <svg class="h5-tabbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.6 12 3.2l9 7.4"/><path d="M5.4 9.4V20.6h13.2V9.4"/><path d="M9.6 20.6v-5.4h4.8v5.4"/></svg>
        </a>
        <a href="/h5/category/index.html" class="h5-tabbar__item<?= $tabActive === 'category' ? ' is-active' : '' ?>" aria-label="分类" title="分类">
            <svg class="h5-tabbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.2" y="3.2" width="7.2" height="7.2" rx="1.6"/><rect x="13.6" y="3.2" width="7.2" height="7.2" rx="1.6"/><rect x="3.2" y="13.6" width="7.2" height="7.2" rx="1.6"/><rect x="13.6" y="13.6" width="7.2" height="7.2" rx="1.6"/></svg>
        </a>
        <a href="/h5/cart/index.html" class="h5-tabbar__item<?= $tabActive === 'cart' ? ' is-active' : '' ?> h5-tabbar__item--cart" aria-label="购物车" title="购物车">
            <svg class="h5-tabbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9.4" cy="20" r="1.4"/><circle cx="17.6" cy="20" r="1.4"/><path d="M2.6 3.4h2.6l2.4 12.2h10.5l2.1-8.6H6.2"/></svg>
            <?php if ($h5CartNum > 0): ?>
            <span class="h5-tabbar__badge" id="h5CartBadge"><?= (int)$h5CartNum > 99 ? '99+' : (int)$h5CartNum ?></span>
            <?php endif; ?>
        </a>
        <a href="javascript:;" class="h5-tabbar__item<?= $tabActive === 'user' ? ' is-active' : '' ?>" data-nav="user" aria-label="我的" title="我的">
            <svg class="h5-tabbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4.4 20.8c0-3.8 3.4-6.2 7.6-6.2s7.6 2.4 7.6 6.2"/></svg>
        </a>
    </nav>

    <script src="/assets/h5/js/app.js?v=<?=time()?>"></script>
</body>
</html>