<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title><?= $setting['store']['values']['name'] ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <base href="/"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="assets/common/i/favicon.ico"/>
    <meta name="apple-mobile-web-app-title" content="<?= $setting['store']['values']['name'] ?>"/>

    <!-- 内联覆盖：确保 root font-size + iconfont 统一 -->
    <style>
        html { font-size: 14px !important; }
        .iconfont { font-size: 16px !important; line-height: 1; }
        .widget-card .iconfont { font-size: 22px !important; }
        .outline-left img, .widget-outline .outline-left img { width: 44px !important; height: 44px !important; object-fit: contain; }
        h1 { font-size: 22px !important; font-weight: 700; }
        h2 { font-size: 18px !important; font-weight: 700; }
        h3 { font-size: 16px !important; font-weight: 600; }
        h4 { font-size: 15px !important; font-weight: 600; }
    </style>
    <link rel="stylesheet" href="assets/common/css/tailwind.css?v=<?=time()?>"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?=time()?>"/>
    <link rel="stylesheet" href="assets/common/css/iconfont-font_783249.css?v=<?=time()?>">
    <link rel="stylesheet" href="assets/common/css/iconfont-business.css?v=<?=time()?>"/>
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="assets/common/js/jquery4-polyfill.js"></script>
    <script src="assets/common/js/iconfont-font_783249.js"></script>

    <!-- 主题跟随 -->
    <script>
    (function () {
        var THEMES = {
            gold: { primary:'#c6a348', rgb:'198,163,72', hover:'#a8873a', soft:'rgba(198,163,72,0.12)', border:'rgba(198,163,72,0.35)', ring:'rgba(198,163,72,0.5)', grad:'linear-gradient(135deg,#e9d3a4,#c6a348,#8a6d2e)' },
            blue: { primary:'#285793', rgb:'40,87,147', hover:'#1e4478', soft:'rgba(40,87,147,0.12)', border:'rgba(40,87,147,0.35)', ring:'rgba(40,87,147,0.5)', grad:'linear-gradient(135deg,#2a4d7e,#285793,#3b73b8)' },
            mint: { primary:'#99bcac', rgb:'153,188,172', hover:'#7fa494', soft:'rgba(153,188,172,0.12)', border:'rgba(153,188,172,0.35)', ring:'rgba(153,188,172,0.5)', grad:'linear-gradient(135deg,#6f9484,#86a898,#99bcac)' }
        };
        var saved = 'gold';
        try { saved = localStorage.getItem('login_theme') || 'gold'; } catch(e){}
        var t = THEMES[saved] || THEMES.gold;
        var r = document.documentElement.style;
        r.setProperty('--primary', t.primary);
        r.setProperty('--primary-rgb', t.rgb);
        r.setProperty('--primary-hover', t.hover);
        r.setProperty('--primary-soft', t.soft);
        r.setProperty('--primary-border', t.border);
        r.setProperty('--primary-ring', t.ring);
        r.setProperty('--primary-grad', t.grad);
    })();
    </script>
    <script>
        BASE_URL = '<?= isset($base_url) ? $base_url : '' ?>';
        STORE_URL = '<?= isset($store_url) ? $store_url : '' ?>';
    </script>
    <link rel="stylesheet" href="assets/common/plugins/element-plus/index.css?v=<?=time()?>"/>
    <!-- top-subnav 样式（内联，在所有外部 CSS 之后确保覆盖） -->
    <style>

                .top-subnav {
            position: fixed !important;
            top: calc(56px + var(--layout-gap, 16px)) !important;
            left: calc(240px + var(--layout-gap, 16px) * 2) !important;
            right: var(--layout-gap, 16px) !important;
            height: 44px !important;
            z-index: 95 !important;
            background: var(--surface, rgba(255,255,255,0.88)) !important;
            backdrop-filter: blur(20px) saturate(140%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(140%) !important;
            border: 1px solid rgba(255,255,255,0.6) !important;
            border-radius: 14px !important;
            box-shadow: 0 2px 12px rgba(30,41,59,0.06), 0 8px 24px -12px rgba(30,41,59,0.12) !important;
            display: flex !important;
            align-items: center !important;
            padding: 0 16px !important;
            margin: 0 !important;
            visibility: visible !important;
            opacity: 1 !important;
            clip: auto !important;
        }
        .top-subnav-inner {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            width: 100% !important;
            overflow: visible !important;
        }
        .top-subnav-title {
            flex-shrink: 0 !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            color: var(--primary, #6366f1) !important;
            padding: 4px 12px !important;
            border-radius: 8px !important;
            background: var(--primary-soft, rgba(99,102,241,0.1)) !important;
            letter-spacing: 0.5px !important;
        }
        .top-subnav-tabs {
            display: flex !important;
            align-items: center !important;
            gap: 2px !important;
            /* 不能 auto，会同时把纵向也裁剪，导致三级下拉菜单不显示 */
            overflow: visible !important;
            flex: 1 !important;
            scrollbar-width: none !important;
        }
        .top-subnav-tabs::-webkit-scrollbar { display: none !important; }
        .top-subnav-tab {
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            padding: 6px 14px !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: var(--text-muted, #64748b) !important;
            white-space: nowrap !important;
            text-decoration: none !important;
            transition: all 0.15s ease !important;
            border: none !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        .top-subnav-tab:hover {
            color: var(--primary, #6366f1) !important;
            background: var(--primary-soft, rgba(99,102,241,0.1)) !important;
            border: 1px solid var(--primary-border) !important;
            padding: 5px 13px !important;
        }
        .top-subnav-tab.active {
            color: #fff !important;
            background: var(--primary-grad) !important;
            border: none !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.18) !important;
        }
        .top-subnav-tab .iconfont { font-size: 12px !important; margin-left: 2px !important; }
        .top-subnav-dropdown { position: relative !important; display: inline-block !important; }
        .top-subnav-menu {
            display: none !important;
            position: absolute !important;
            top: calc(100% + 6px) !important;
            left: 0 !important;
            min-width: 140px !important;
            background: var(--surface-strong, rgba(255,255,255,0.95)) !important;
            backdrop-filter: blur(20px) saturate(140%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(140%) !important;
            border: 1px solid rgba(255,255,255,0.6) !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06) !important;
            padding: 6px !important;
            z-index: 100 !important;
        }
        .top-subnav-dropdown:hover .top-subnav-menu,
        .top-subnav-dropdown.is-open .top-subnav-menu { display: block !important; }
        .top-subnav-menu a {
            display: block !important;
            padding: 7px 14px !important;
            font-size: 13px !important;
            color: var(--text, #0f172a) !important;
            border-radius: 6px !important;
            text-decoration: none !important;
        }
        .top-subnav-menu a:hover {
            color: var(--primary, #6366f1) !important;
            background: var(--primary-soft, rgba(99,102,241,0.1)) !important;
        }
        .top-subnav-menu a.active {
            color: var(--primary, #6366f1) !important;
            font-weight: 600 !important;
            background: var(--primary-soft, rgba(99,102,241,0.1)) !important;
        }
        .tpl-content-wrapper.has-subnav {
            margin-top: calc(56px + 44px + var(--layout-gap, 16px) * 2) !important;
        }
        .tpl-g.sidebar-fold .top-subnav { left: calc(64px + var(--layout-gap, 16px) * 2) !important; }
    
    </style>
</head>

<body data-type=""><div class="tpl-g">
    <!-- 头部 -->
    <header class="tpl-header">
        <div class="tpl-header-fluid">
            <div class="float-left tpl-header-shop">
                <i class="iconfont icon-shop" style="font-size:18px;margin-right:8px;color:var(--primary);"></i>
                <span class="tpl-header-shop-name"><?= $setting['store']['values']['name'] ?></span>
            </div>
            <div class="float-right tpl-header-navbar">
                <ul>
                    <li class="text-sm">
                        <a href="<?= $base_url ?>" target="_blank" title="访问站点首页">
                            <i class="iconfont icon-home"></i> 主页
                        </a>
                    </li>
                    <li class="tpl-header-button refresh-button" title="刷新">
                        <i class="iconfont icon-refresh"></i>
                    </li>
                    <li class="tpl-header-button switch-button" title="折叠/展开侧栏">
                        <i class="iconfont icon-menufold"></i>
                    </li>
                    <li class="text-sm tpl-header-navbar-welcome">
                        <a href="<?= url('store.user/renew') ?>">欢迎你，<span><?= $store['user']['user_name'] ?></span></a>
                    </li>
                    <li class="text-sm">
                        <a href="<?= url('passport/logout') ?>"><i class="iconfont icon-tuichu"></i> 退出</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- 左侧一级菜单 -->
    <div class="left-sidebar dis-flex">
        <?php $menus = $menus ?: []; ?>
        <?php $group = $group ?: 0; ?>
        <div class="sidebar-scroll">
            <ul class="sidebar-nav">
                <?php foreach ($menus as $key => $item): ?>
                    <li class="sidebar-nav-link">
                        <a href="<?= isset($item['index']) ? url($item['index']) : 'javascript:void(0);' ?>"
                           class="<?= $item['active'] ? 'active' : '' ?>">
                            <?php if (isset($item['is_svg']) && $item['is_svg'] == true): ?>
                                <svg class="icon sidebar-nav-link-logo" aria-hidden="true"><use xlink:href="#<?= $item['icon'] ?>"></use></svg>
                            <?php elseif (isset($item['icon']) && preg_match('/\.(png|jpe?g|gif|webp|svg)$/i', $item['icon'])): ?>
                                <img class="sidebar-nav-link-logo sidebar-nav-link-img" src="/assets/store/img/<?= htmlspecialchars($item['icon']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"/>
                            <?php else: ?>
                                <i class="<?= (isset($item['icon']) && strpos($item['icon'], 'am-icon') === 0) ? $item['icon'] . ' sidebar-nav-link-logo' : 'iconfont sidebar-nav-link-logo ' . (isset($item['icon']) ? $item['icon'] : '') ?>"></i>
                            <?php endif; ?>
                            <?= $item['name'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- 顶部二级/三级菜单 tab 栏 -->
    <?php $second = isset($menus[$group]['submenu']) ? $menus[$group]['submenu'] : []; ?>
    <?php if (!empty($second)) : ?>
    <div class="top-subnav">
        <div class="top-subnav-inner">
            <div class="top-subnav-title"><?= $menus[$group]['name'] ?></div>
            <nav class="top-subnav-tabs">
                <?php foreach ($second as $item) : ?>
                    <?php if (!isset($item['submenu'])): ?>
                        <a href="<?= url($item['index']) ?>"
                           class="top-subnav-tab <?= (isset($item['active']) && $item['active']) ? 'active' : '' ?>">
                            <?= $item['name']; ?>
                        </a>
                    <?php else: ?>
                        <div class="top-subnav-dropdown">
                            <a href="javascript:void(0);"
                               class="top-subnav-tab top-subnav-tab-with-arrow <?= $item['active'] ? 'active' : '' ?>">
                                <?= $item['name']; ?>
                                <i class="iconfont icon-caret"></i>
                            </a>
                            <div class="top-subnav-menu">
                                <?php foreach ($item['submenu'] as $third) : ?>
                                    <a class="<?= $third['active'] ? 'active' : '' ?>"
                                       href="<?= url($third['index']) ?>">
                                        <?= $third['name']; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <!-- 内容区域 -->
    <div class="tpl-content-wrapper <?= empty($second) ? 'no-sidebar-second' : 'has-subnav' ?>">
        {__CONTENT__}
    </div>

</div>

<script src="assets/common/plugins/layer/layer.js"></script>
<script src="assets/common/js/jquery.form.min.js"></script>
<script>
$(function () {
  $('.js-tabs').each(function () {
    var $t = $(this);
    $t.find('ul > li > a[href="javascript:;"]').on('click', function (e) {
      e.preventDefault();
      var $a = $(this), target = $a.data('target');
      $t.find('ul > li > a').removeClass('border-blue-500 text-blue-600').addClass('border-transparent text-gray-500 hover:text-gray-700');
      $a.removeClass('border-transparent text-gray-500 hover:text-gray-700').addClass('border-blue-500 text-blue-600');
      $t.find('.js-tab-panel').addClass('hidden');
      $t.find(target).removeClass('hidden');
    });
  });

  $('.top-subnav-dropdown > .top-subnav-tab-with-arrow').on('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).closest('.top-subnav-dropdown').toggleClass('is-open');
  });
  $(document).on('click', function () {
    $('.top-subnav-dropdown.is-open').removeClass('is-open');
  });
});
</script>
<script src="assets/common/js/webuploader.html5only.js"></script>
<script src="assets/common/js/art-template.js"></script>
<script src="assets/store/js/app.js?v=<?=time()?>"></script>
<script src="assets/store/js/file.library.js?v=<?=time()?>"></script>
</body>
</html>
