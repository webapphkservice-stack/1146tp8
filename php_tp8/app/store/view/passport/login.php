<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title><?= htmlspecialchars($siteName) ?> - 商家后台登录</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <base href="/"/>
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <link rel="icon" type="image/png" href="/assets/common/i/favicon.ico"/>
    <link rel="stylesheet" href="/assets/common/plugins/layer/theme/gpt/layer.css"/>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* 登录背景轮播层（双缓冲交叉淡入） */
        .bg-layer {
            position: fixed;
            inset: 0;
            z-index: -1;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0;
            transition: opacity 1.4s ease;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* 上下留安全边距: 平板竖屏视口较小时,登录框不会溢出顶部; 同时抵消 body::before top:-140px 的影响 */
            padding: 24px 0 calc(72px + env(safe-area-inset-bottom, 0px));
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            color: #fff;
            /* 背景兜底（实际背景由 JS 切换的 .bg-layer 呈现） */
            background: linear-gradient(135deg, #8a6d2e 0%, #c6a348 50%, #e9d3a4 100%);
            /* iOS 视口高度动态变化时保持完整可视 */
            -webkit-text-size-adjust: 100%;
            -webkit-overflow-scrolling: touch;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            z-index: 0;
            pointer-events: none;
        }

        body::before {
            width: 520px;
            height: 520px;
            background: rgba(165, 180, 252, 0.7);
            top: -140px;
            left: -100px;
        }

        body::after {
            width: 460px;
            height: 460px;
            background: rgba(124, 58, 237, 0.65);
            bottom: -140px;
            right: -80px;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 400px;
            max-width: 92vw;
            padding: 44px 40px 36px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(22px) saturate(160%);
            -webkit-backdrop-filter: blur(22px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow:
                0 24px 60px rgba(10, 30, 60, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.55),
                inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }

        /* ===== 登录/注册双面板舞台 ===== */
        .auth-stage {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: stretch;
            justify-content: flex-start;  /* login 始终贴 stage 左边缘，register absolute 才能精准对齐 */
            width: 400px; /* 初始只显示登录框 */
            transition: width 0.5s cubic-bezier(.22,1,.36,1);
            overflow: visible;
            /* 高度自适应内容；整体居中由 body flex 负责 */
            margin: auto;  /* 在 body flex 中保持居中,同时避免溢出视口边缘 */
            /* 平板竖屏/小屏兜底: 限制舞台不超出视口高度,需要时内部面板可滚动 */
            max-height: calc(100vh - 120px);
        }
        .auth-stage.is-open { width: 812px; /* 400 + 12(gap) + 400 */ }

        .auth-panel {
            position: relative;
            width: 400px;
            max-width: 92vw;
            padding: 44px 40px 36px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(22px) saturate(160%);
            -webkit-backdrop-filter: blur(22px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow:
                0 24px 60px rgba(10, 30, 60, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.55),
                inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        }

        /* 登录面板：在 flex 流里，保持自身高度不被 register 影响 */
        .auth-panel--login { z-index: 2; flex: none; height: auto; }

        /* 注册面板：**永远**相对舞台 absolute，不进入 flex 流 → 无法影响登录框高度 */
        .auth-panel--register {
            position: absolute;
            top: 0;
            left: 400px;        /* 初始藏在舞台右缘之外 */
            margin-left: 12px;
            opacity: 0;
            transform: translateX(40px) scale(.92);
            pointer-events: none;
            transition:
                left    .5s cubic-bezier(.22,1,.36,1),
                opacity .45s cubic-bezier(.22,1,.36,1),
                transform .5s cubic-bezier(.22,1,.36,1);
        }
        .auth-stage.is-open .auth-panel--register {
            left: 412px;          /* = 400(login) + 12(gap) */
            opacity: 1;
            transform: translateX(0) scale(1);
            pointer-events: auto;
        }

        /* 移动端窄屏：改为垂直堆叠 + 向下滑出 */
        @media (max-width: 900px) {
            .auth-stage { width: 100%; max-width: 400px; flex-direction: column; height: auto; }
            .auth-stage.is-open { width: 100%; max-width: 400px; }
            .auth-panel { width: 100%; max-width: 400px; }
            .auth-panel--register {
                position: static;
                opacity: 0;
                transform: translateY(40px) scale(.92);
                margin: 18px 0 0;
            }
            .auth-stage.is-open .auth-panel--register {
                position: static;
                left: auto;
                opacity: 1;
                transform: translateY(0) scale(1);
                pointer-events: auto;
            }
        }

        /* 平板竖屏兜底: 视口高度 < 800px 时进一步压缩舞台 */
        @media (max-height: 800px) {
            .auth-stage { max-height: calc(100vh - 60px); }
            .auth-panel, .login-card { padding: 32px 32px 28px; }
            .brand { margin-bottom: 18px; }
            .brand-text { font-size: 20px; }
            .auth-panel__header { margin-bottom: 18px; }
            .form-tabs { margin-bottom: 16px; }
            .form-group { margin-bottom: 14px; }
            .login-form input, #btn-submit, #btn-register { height: 42px; font-size: 14px; }
            .auth-actions { margin-top: 12px; }
        }

        /* 面板头部 */
        .auth-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 26px;
        }
        .auth-panel__title {
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
            margin: 0;
        }
        .auth-panel__close {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.12);
            color: #fff;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease;
        }
        .auth-panel__close:hover {
            background: rgba(255,255,255,0.25);
            transform: rotate(90deg);
        }

        /* "注册商户"按钮 + "忘记密码"：同一行 */
        .auth-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 18px;
        }
        .auth-actions .register-toggle { margin-top: 0; }

        .auth-forgot {
            font-size: 13px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 8px 4px;
            border-bottom: 1px solid transparent;
            transition: color .2s, border-color .2s;
        }
        .auth-forgot:hover {
            color: #fff;
            border-bottom-color: rgba(255,255,255,0.7);
        }

        /* "注册商户"按钮 */
        .register-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 16px;
            padding: 8px 18px;
            border-radius: 22px;
            border: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #fff;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .register-toggle:hover {
            background: rgba(255,255,255,0.24);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }
        .register-toggle svg { width: 14px; height: 14px; }

        .brand {
            text-align: center;
            margin-bottom: 26px;
        }

        .brand-text {
            color: #fff;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 1px;
            /* 主题色文字阴影 + 底层黑色柔阴影，层次更立体 */
            text-shadow:
                0 0 18px var(--theme-shadow-glow, rgba(198, 163, 72, 0.55)),
                0 2px 10px rgba(0, 0, 0, 0.35);
        }

        .form-tabs {
            display: flex;
            margin-bottom: 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.28);
        }

        .form-tab {
            flex: 1;
            text-align: center;
            padding: 12px 0;
            cursor: pointer;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.78);
            transition: all 0.3s;
            border-bottom: 2px solid transparent;
        }

        .form-tab.active {
            color: #fff;
            border-bottom: 2px solid #fff;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .login-form input {
            width: 100%;
            height: 46px;
            padding: 0 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.25s ease;
        }

        .login-form input::placeholder {
            color: rgba(255, 255, 255, 0.78);
        }

        .login-form input:focus {
            border-color: rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.26);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4);
        }

        #btn-submit, #btn-register {
            width: 100%;
            height: 46px;
            margin-top: 4px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 12px;
            /* 磨砂玻璃按钮：主题色半透明 + 背景模糊 */
            background: rgba(79, 70, 229, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 2px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 8px 24px rgba(10, 30, 60, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }

        #btn-submit:hover, #btn-register:hover {
            background: rgba(79, 70, 229, 0.6);
            transform: translateY(-2px);
        }

        #btn-submit:active, #btn-register:active {
            transform: translateY(0);
        }

        #btn-submit:disabled, #btn-register:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .form-switch {
            text-align: center;
            margin-top: 14px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.78);
        }

        .form-switch a {
            color: #fff;
            cursor: pointer;
            text-decoration: underline;
        }

        /* ---- 主题色圆点 ---- */
        .theme-switcher {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 14px;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.18);
        }

        .theme-switcher-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            letter-spacing: 0.5px;
        }

        .theme-dots {
            display: flex;
            gap: 10px;
        }

        .theme-dot {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.4);
            transition: all 0.25s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .theme-dot:hover {
            transform: scale(1.18);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .theme-dot.active {
            border-color: #fff;
            transform: scale(1.15);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3),
                        0 0 0 3px rgba(255, 255, 255, 0.25);
        }

        .theme-dot.active::after {
            content: "";
            position: absolute;
            inset: 50%;
            width: 6px;
            height: 6px;
            margin: -3px 0 0 -3px;
            border-radius: 50%;
            background: #fff;
        }
    </style>
</head>
<body>
<div id="bg1" class="bg-layer"></div>
<div id="bg2" class="bg-layer"></div>
<div class="auth-stage" id="authStage">
    <!-- 登录面板 -->
    <div class="auth-panel auth-panel--login">
        <div class="brand">
            <h2 class="brand-text"><?= htmlspecialchars($siteName) ?></h2>
        </div>

        <div id="login-form-container" class="form-container active">
            <form id="login-form" class="login-form">
                <div class="form-group">
                    <input class="" name="User[user_name]" placeholder="请输入用户名" type="text" required value="admin">
                </div>
                <div class="form-group">
                    <input class="" name="User[password]" placeholder="请输入密码" type="password" required value="123456">
                </div>
                <div class="form-group">
                    <button id="btn-submit" type="submit">登 录</button>
                </div>
            </form>
        </div>

        <!-- "注册商户"按钮 + "忘记密码"链接，同一行 -->
        <div class="auth-actions">
            <button type="button" class="register-toggle" id="registerToggle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                注册商户
            </button>
            <a href="javascript:;" class="auth-forgot" id="forgotToggle">忘记密码</a>
        </div>
    </div>

    <!-- 注册面板（默认隐藏，点击后从右侧滑出，同高） -->
    <div class="auth-panel auth-panel--register">
        <div class="auth-panel__header">
            <h3 class="auth-panel__title">注册商户</h3>
            <button type="button" class="auth-panel__close" id="registerClose" aria-label="关闭">×</button>
        </div>

        <div id="register-form-container" class="form-container active">
            <form id="register-form" class="login-form">
                <div class="form-group">
                    <input type="text" name="store[store_name]" placeholder="请输入商城名称" required>
                </div>
                <div class="form-group">
                    <input type="text" name="store[user_name]" placeholder="请输入商家账户名" required>
                </div>
                <div class="form-group">
                    <input type="password" name="store[password]" placeholder="请输入密码" required>
                </div>
                <div class="form-group">
                    <input type="password" name="store[password_confirm]" placeholder="请确认密码" required>
                </div>
                <input type="hidden" name="store[sort]" value="100">
                <div class="form-group">
                    <button id="btn-register" type="submit">注 册</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 主题色切换：放到舞台外部、居中 -->
<div class="theme-switcher" style="position: fixed; bottom: calc(36px + env(safe-area-inset-bottom, 0px)); left: 0; right: 0; z-index: 2;">
    <span class="theme-switcher-label">主题色</span>
    <div class="theme-dots">
        <span class="theme-dot active" data-theme="gold"   style="background: rgb(198, 163, 72);"   title="金棕"></span>
        <span class="theme-dot"            data-theme="blue"   style="background: rgb(40, 87, 147);"    title="深蓝"></span>
        <span class="theme-dot"            data-theme="mint"   style="background: rgb(153, 188, 172);" title="青灰"></span>
    </div>
</div>
</body>
<script src="/assets/common/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script src="/assets/common/plugins/layer/layer.js?v=<?= $version ?>"></script>
<script src="/assets/common/js/jquery.form.min.js"></script>
<script>
    $(function () {
        /* ---------- 主题色切换 ---------- */
        var THEMES = {
            gold: {
                primary:      'rgb(198, 163, 72)',
                primaryHex:   '#c6a348',
                bgGradient:   'linear-gradient(135deg, #b8943f 0%, #8a6d2e 50%, #c6a348 100%)',
                blurBefore:   'rgba(214, 186, 110, 0.65)',
                blurAfter:    'rgba(139, 108, 46, 0.6)',
                btnBg:        'rgba(198, 163, 72, 0.55)',
                btnBgHover:   'rgba(198, 163, 72, 0.72)',
                focusRing:    'rgba(198, 163, 72, 0.4)',
                shadowGlow:   'rgba(198, 163, 72, 0.35)'
            },
            blue: {
                primary:      'rgb(40, 87, 147)',
                primaryHex:   '#285793',
                bgGradient:   'linear-gradient(135deg, #1a3d6e 0%, #285793 50%, #3b73b8 100%)',
                blurBefore:   'rgba(84, 134, 196, 0.65)',
                blurAfter:    'rgba(26, 61, 110, 0.6)',
                btnBg:        'rgba(40, 87, 147, 0.55)',
                btnBgHover:   'rgba(40, 87, 147, 0.72)',
                focusRing:    'rgba(84, 134, 196, 0.4)',
                shadowGlow:   'rgba(40, 87, 147, 0.35)'
            },
            mint: {
                primary:      'rgb(153, 188, 172)',
                primaryHex:   '#99bcac',
                bgGradient:   'linear-gradient(135deg, #6f9484 0%, #86a898 50%, #99bcac 100%)',
                blurBefore:   'rgba(180, 208, 195, 0.65)',
                blurAfter:    'rgba(102, 140, 122, 0.55)',
                btnBg:        'rgba(153, 188, 172, 0.55)',
                btnBgHover:   'rgba(153, 188, 172, 0.72)',
                focusRing:    'rgba(153, 188, 172, 0.4)',
                shadowGlow:   'rgba(153, 188, 172, 0.35)'
            }
        };

        function applyTheme(name) {
            var t = THEMES[name];
            if (!t) return;
            document.documentElement.style.setProperty('--primary', t.primary);
            document.documentElement.style.setProperty('--primary-light', t.primaryHex);

            var style = document.documentElement.style;
            style.setProperty('--theme-bg', t.bgGradient);
            style.setProperty('--theme-blur-before', t.blurBefore);
            style.setProperty('--theme-blur-after', t.blurAfter);
            style.setProperty('--theme-btn-bg', t.btnBg);
            style.setProperty('--theme-btn-bg-hover', t.btnBgHover);
            style.setProperty('--theme-focus-ring', t.focusRing);
            style.setProperty('--theme-shadow-glow', t.shadowGlow);

            $('body').css({
                background: t.bgGradient,
                transition: 'background 0.55s ease'
            });
            // 用伪元素替换 body::before/after 的颜色 —— 直接改 head 里注入的 style
            $('body').css('background', t.bgGradient);

            // 按钮
            $('#btn-submit, #btn-register').css('background', t.btnBg);
            $('.login-form input:focus').css('box-shadow', '0 0 0 3px ' + t.focusRing);

            // 伪元素：动态注入一小段 style 覆盖 body::before / ::after
            $('#theme-pseudo').remove();
            var pseudoStyle =
                '<style id="theme-pseudo">' +
                'body::before { background: ' + t.blurBefore + ' !important; }' +
                'body::after  { background: ' + t.blurAfter  + ' !important; }' +
                '#btn-submit:hover, #btn-register:hover { background: ' + t.btnBgHover + ' !important; }' +
                '.login-form input:focus { box-shadow: 0 0 0 3px ' + t.focusRing + ' !important; }' +
                '</style>';
            $('head').append(pseudoStyle);

            try { localStorage.setItem('login_theme', name); } catch (e) {}
        }

        // 初始化：读 localStorage，默认 gold
        var saved = 'gold';
        try { saved = localStorage.getItem('login_theme') || 'gold'; } catch (e) {}
        $('.theme-dot').removeClass('active');
        $('.theme-dot[data-theme="' + saved + '"]').addClass('active');
        applyTheme(saved);

        // 点击切换
        $('.theme-dot').on('click', function () {
            var name = $(this).data('theme');
            $('.theme-dot').removeClass('active');
            $(this).addClass('active');
            applyTheme(name);
        });

        // 登录背景轮播切换（双缓冲交叉淡入，每 6 秒切换）
        try {
        (function () {
            var imgs = [
                '/assets/common/img/login/serge-taeymans-semr62rBJO0-unsplash.jpg',
                '/assets/common/img/login/andrea-caramello-PatGIkZnQcE-unsplash.jpg',
                '/assets/common/img/login/jason-mavrommatis-GPPAjJicemU-unsplash.jpg',
                '/assets/common/img/login/nicolas-hans-xGVjRRmnHlQ-unsplash.jpg',
                '/assets/common/img/login/red-zeppelin-0jJttj_0Qsw-unsplash.jpg',
                '/assets/common/img/login/samuel-harrington-u1YBSpjc2Lk-unsplash.jpg',
                '/assets/common/img/login/troy-olson-6TIH8Nv3KXw-unsplash.jpg'
            ];
            var layers = [document.getElementById('bg1'), document.getElementById('bg2')];
            if (!layers[0] || !layers[1]) return;
            var curLayer = 0, imgIdx = 0;
            imgs.forEach(function (s) { var i = new Image(); i.src = s; });
            function paint(layerIdx, imgI, op) {
                if (!layers[layerIdx]) return;
                layers[layerIdx].style.backgroundImage = "url('" + imgs[imgI] + "')";
                layers[layerIdx].style.opacity = op;
            }
            paint(curLayer, imgIdx, 1);
            paint(1 - curLayer, imgIdx, 0);
            setInterval(function () {
                imgIdx = (imgIdx + 1) % imgs.length;
                var nextLayer = 1 - curLayer;
                paint(nextLayer, imgIdx, 1);
                paint(curLayer, imgIdx, 0);
                curLayer = nextLayer;
            }, 6000);
        })();
        } catch (e) {}

        // 打开/关闭注册面板
        var $stage = $('#authStage');
        $('#registerToggle').on('click', function () {
            $stage.addClass('is-open');
        });
        $('#registerClose').on('click', function () {
            $stage.removeClass('is-open');
        });
        // ESC 键也可关闭
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') $stage.removeClass('is-open');
        });

        // 忘记密码（暂未实现后端，先提示联系管理员）
        $('#forgotToggle').on('click', function () {
            layer.msg('请联系管理员重置密码', {icon: 0, time: 1800});
        });

        // 登录表单提交
        var $loginForm = $('#login-form');
        $loginForm.submit(function () {
            var $btn_submit = $('#btn-submit');
            $btn_submit.attr("disabled", true);
            $loginForm.ajaxSubmit({
                type: "post",
                dataType: "json",
                url: '',
                success: function (result) {
                    $btn_submit.attr('disabled', false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {icon: 1, time: 1500, anim: 1}, function () {
                            window.location = result.url;
                        });
                        return true;
                    }
                    layer.msg(result.msg, {icon: 2, time: 1500, anim: 6});
                }
            });
            return false;
        });

        // 注册表单提交
        var $registerForm = $('#register-form');
        $registerForm.submit(function () {
            var $btn_register = $('#btn-register');
            $btn_register.attr("disabled", true);
            $registerForm.ajaxSubmit({
                type: "post",
                dataType: "json",
                url: '<?= url("passport/register") ?>',
                success: function (result) {
                    $btn_register.attr('disabled', false);
                    if (result.code === 1) {
                        layer.msg(result.msg, {icon: 1, time: 1500, anim: 1}, function () {
                            $stage.removeClass('is-open');
                            $registerForm[0].reset();
                        });
                        return true;
                    }
                    layer.msg(result.msg, {icon: 2, time: 1500, anim: 6});
                }
            });
            return false;
        });
        // URL 参数自动填入并直接 POST 登录（绕开 ajaxSubmit，用 $.ajax 保证可靠）
        (function () {
            try {
                var qs = location.search.substring(1).split('&');
                for (var i = 0; i < qs.length; i++) {
                    var p = qs[i].split('=');
                    if (!p[1]) continue;
                    var key = decodeURIComponent(p[0]);
                    var v = decodeURIComponent(p[1].replace(/\+/g, ' '));
                    if (key === 'User[user_name]') $('input[name="User[user_name]"]').val(v);
                    if (key === 'User[password]') $('input[name="User[password]"]').val(v);
                }
                var $u = $('input[name="User[user_name]"]');
                var $p = $('input[name="User[password]"]');
                if ($u.val() && $p.val()) {
                    try { history.replaceState(null, '', location.pathname); } catch (e) {}
                    $.ajax({
                        url: location.pathname,
                        type: 'POST',
                        dataType: 'json',
                        data: { 'User[user_name]': $u.val(), 'User[password]': $p.val() },
                        success: function (r) {
                            if (r && r.code === 1 && r.data && r.data.token) {
                                try { localStorage.setItem('admin_token', r.data.token); } catch (e) {}
                                window.location.href = '/admin/index/index?token=' + encodeURIComponent(r.data.token);
                            } else {
                                layer.msg((r && r.msg) || '登录失败', {icon: 2, time: 1500, anim: 6});
                            }
                        },
                        error: function () {
                            layer.msg('网络错误，请重试', {icon: 2, time: 1500, anim: 6});
                        }
                    });
                }
            } catch (e) {}
        })();
    });
</script>
</html>
