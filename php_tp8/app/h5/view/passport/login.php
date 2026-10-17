<?php
/**
 * H5 登录页 —— 液态玻璃风格
 * URL: /h5/passport/login
 */
$bg = $bgImage ?: '';

// 跟随系统标题栏背景色（供登录按钮配色）
$h5TitleBg = '#8eba85';
try {
    $homePage = \app\common\model\WxappPage::getHomePage();
    $style    = $homePage['page_data']['page']['style'] ?? [];
    if (!empty($style['titleBackgroundColor'])) {
        $h5TitleBg = $style['titleBackgroundColor'];
    }
} catch (\Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>登录 - <?= htmlspecialchars($storeName) ?></title>
    <link rel="icon" type="image/png" href="/assets/common/i/favicon.ico"/>
    <style>:root { --h5-title-bg: <?= htmlspecialchars($h5TitleBg, ENT_QUOTES) ?>; }</style>
    <style>
        /* ========== 全屏 ========== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC",
                         "Hiragino Sans GB", "Microsoft YaHei", sans-serif;
            color: #fff;
            overflow: hidden;
            background: #0f172a; /* fallback */
        }

        /* ========== 随机背景图 ========== */
        .login-bg {
            position: fixed;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            transition: background-image 0.4s ease;
            animation: bg-kenburns 18s ease-in-out infinite alternate;
        }
        @keyframes bg-kenburns {
            0%   { transform: scale(1.05); }
            100% { transform: scale(1.12); }
        }
        /* 覆盖渐变遮罩 —— 确保玻璃层之上的文字可读 */
        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at top, rgba(59,130,246,.25), transparent 55%),
                linear-gradient(135deg, rgba(15,23,42,.55) 0%, rgba(15,23,42,.35) 100%);
            backdrop-filter: blur(0.5px);
        }

        /* ========== 页面容器 ========== */
        .login-wrap {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 20px;
        }

        /* ========== 液态玻璃卡片 ========== */
        .glass-card {
            width: 100%;
            max-width: 380px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(22px) saturate(180%);
            -webkit-backdrop-filter: blur(22px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 24px;
            padding: 38px 28px 30px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
            position: relative;
            overflow: hidden;
            animation: card-appear .6s cubic-bezier(.22,1,.36,1) both;
        }
        /* 玻璃高光 */
        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                135deg,
                rgba(255,255,255,0.15) 0%,
                rgba(255,255,255,0) 40%,
                rgba(255,255,255,0.12) 60%,
                rgba(255,255,255,0) 100%
            );
            pointer-events: none;
        }
        @keyframes card-appear {
            from { opacity: 0; transform: translateY(20px) scale(.97); }
            to   { opacity: 1; transform: none; }
        }

        /* ========== Logo / 标题 ========== */
        .glass-header {
            text-align: center;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }
        .glass-logo {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: rgba(255,255,255,.22);
            border: 1px solid rgba(255,255,255,.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,.18);
        }
        .glass-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: .5px;
            text-shadow: 0 2px 8px rgba(0,0,0,.35);
        }
        .glass-subtitle {
            font-size: 13px;
            opacity: .82;
            margin-top: 6px;
        }

        /* ========== 表单 ========== */
        .form-group {
            position: relative;
            margin-bottom: 16px;
            z-index: 1;
        }
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 16px;
            background: rgba(255,255,255,0.16);
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 14px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all .2s;
        }
        .form-input::placeholder {
            color: rgba(255,255,255,0.65);
        }
        .form-input:focus {
            background: rgba(255,255,255,0.24);
            border-color: rgba(255,255,255,0.55);
            box-shadow: 0 0 0 3px rgba(255,255,255,.15);
        }
        /* iOS 密码框特殊：防止自动填充变蓝 */
        .form-input:-webkit-autofill {
            -webkit-text-fill-color: #fff;
            -webkit-box-shadow: 0 0 0px 1000px rgba(59,130,246,.2) inset;
        }

        /* 密码可见切换 */
        .form-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: rgba(255,255,255,.8);
            cursor: pointer;
            transition: background .15s;
        }
        .form-toggle:hover { background: rgba(255,255,255,.15); }

        /* ========== 登录按钮（跟随系统标题栏背景色） ========== */
        .btn-login {
            width: 100%;
            height: 50px;
            margin-top: 6px;
            border: none;
            border-radius: 14px;
            background: color-mix(in srgb, var(--h5-title-bg) 90%, #000 10%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 6px 20px color-mix(in srgb, var(--h5-title-bg) 55%, transparent),
                        inset 0 1px 0 rgba(255,255,255,.25);
            transition: transform .15s, box-shadow .2s, filter .2s;
            z-index: 1;
            position: relative;
        }
        .btn-login:active {
            transform: scale(.98);
            box-shadow: 0 3px 10px color-mix(in srgb, var(--h5-title-bg) 45%, transparent);
        }
        .btn-login:disabled {
            filter: grayscale(.5);
            opacity: .7;
            cursor: not-allowed;
        }

        /* ========== 表单底部 ========== */
        .form-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
            padding: 0 2px;
            font-size: 13px;
            opacity: .85;
            z-index: 1;
            position: relative;
        }
        .form-footer a {
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,.35);
            padding-bottom: 1px;
            transition: border-color .2s;
        }
        .form-footer a:hover { border-color: #fff; }

        /* ========== 同意协议 ========== */
        .form-agree {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 14px;
            font-size: 12px;
            color: rgba(255,255,255,.7);
            line-height: 1.6;
            z-index: 1;
            position: relative;
            cursor: pointer;
            user-select: none;
        }
        .form-agree input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            margin-top: 2px;
            border: 1.5px solid rgba(255,255,255,.55);
            border-radius: 4px;
            background: rgba(255,255,255,.1);
            cursor: pointer;
            position: relative;
            transition: all .15s;
        }
        .form-agree input[type="checkbox"]:checked {
            background: color-mix(in srgb, var(--h5-title-bg) 85%, #000 15%);
            border-color: color-mix(in srgb, var(--h5-title-bg) 70%, #000 30%);
        }
        .form-agree input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 5px;
            width: 4px;
            height: 8px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .form-agree a {
            color: rgba(255,255,255,.95);
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,.35);
            padding-bottom: 1px;
        }
        .form-agree a:hover { border-color: #fff; }

        /* ========== Toast ========== */
        .toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,.7);
            color: #fff;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transition: opacity .25s;
            pointer-events: none;
        }
        .toast.show { opacity: 1; }

        /* ========== 手机适配 ========== */
        @media (max-width: 380px) {
            .glass-card { padding: 32px 22px 26px; border-radius: 20px; }
            .glass-title { font-size: 20px; }
        }
        @supports not (backdrop-filter: blur(22px)) {
            /* 不支持 backdrop-filter 的浏览器回退 */
            .glass-card {
                background: rgba(20, 30, 50, 0.65);
            }
        }
    </style>
</head>
<body>

    <?php if ($bg): ?>
    <div class="login-bg" style="background-image: url('<?= htmlspecialchars($bg) ?>');"></div>
    <?php else: ?>
    <div class="login-bg" style="background: linear-gradient(135deg,#1e3a8a,#0f172a,#312e81);"></div>
    <?php endif; ?>

    <div class="login-wrap">
        <form class="glass-card" id="login-form" autocomplete="off">
            <div class="glass-header">
                <div class="glass-logo">🛒</div>
                <div class="glass-title"><?= htmlspecialchars($storeName) ?></div>
                <div class="glass-subtitle">欢迎回来，请登录你的账号</div>
            </div>

            <div class="form-group">
                <input type="text" class="form-input" name="account" id="account"
                       placeholder="手机号 / 用户名" maxlength="30" autocomplete="username"/>
            </div>

            <div class="form-group">
                <input type="password" class="form-input" name="password" id="password"
                       placeholder="密码" maxlength="32" autocomplete="current-password"/>
                <span class="form-toggle" id="togglePwd" title="显示/隐藏">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">登 录</button>

            <label class="form-agree">
                <input type="checkbox" id="agreeBox"/>
                <span>我已阅读并同意
                    <a href="/h5/passport/agreement">《用户协议》</a>
                    和
                    <a href="/h5/passport/privacy">《隐私政策》</a>
                </span>
            </label>

            <div class="form-footer">
                <a href="/h5/passport/register">注册账号</a>
                <a href="javascript:alert('请联系管理员找回');">忘记密码？</a>
            </div>
        </form>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        // 密码可见切换
        document.getElementById('togglePwd').addEventListener('click', function () {
            var pwd = document.getElementById('password');
            pwd.type = (pwd.type === 'password') ? 'text' : 'password';
        });

        // Toast
        function toast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { t.classList.remove('show'); }, 2200);
        }

        // 登录提交
        document.getElementById('login-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btnLogin');
            var account = document.getElementById('account').value.trim();
            var password = document.getElementById('password').value.trim();
            if (!account || !password) { toast('请输入账号和密码'); return; }
            if (!document.getElementById('agreeBox').checked) { toast('请先同意用户协议和隐私政策'); return; }

            btn.disabled = true;
            btn.textContent = '登录中...';

            var fd = new FormData();
            fd.append('account', account);
            fd.append('password', password);

            fetch('/h5/passport/doLogin', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.textContent = '登 录';
                    if (data.code === 1) {
                        // 存 token 到 localStorage，跳转到用户中心
                        try { localStorage.setItem('h5_token', data.data.token); } catch (e) {}
                        toast('登录成功');
                        setTimeout(function () {
                            location.href = appendToken(data.data.redirect || '/h5/user/index', data.data.token);
                        }, 500);
                    } else {
                        toast(data.msg || '登录失败');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = '登 录';
                    toast('网络异常，请稍后再试');
                });
        });

        // 回车提交
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') document.getElementById('login-form').requestSubmit();
        });
    // 把 token 追加到 URL query 上（Cookie + localStorage + query 三重保险）
        function appendToken(url, token) {
            if (!token) return url;
            var sep = url.indexOf('?') >= 0 ? '&' : '?';
            // 已经带 ?token= 就跳过
            if (/[?&]token=/.test(url)) return url;
            return url + sep + 'token=' + encodeURIComponent(token);
        }
    </script>

</body>
</html>
