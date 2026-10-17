<?php
/**
 * H5 注册页 —— 液态玻璃风格（与登录页共享视觉语言）
 * URL: /h5/passport/register
 */
$bg = $bgImage ?: '';

// 跟随系统标题栏背景色（供注册按钮配色）
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
    <title>注册 - <?= htmlspecialchars($storeName) ?></title>
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
            background: #0f172a;
        }

        /* ========== 随机背景图 ========== */
        .login-bg {
            position: fixed;
            inset: 0;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            animation: bg-kenburns 18s ease-in-out infinite alternate;
        }
        @keyframes bg-kenburns {
            0%   { transform: scale(1.05); }
            100% { transform: scale(1.12); }
        }
        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at bottom, rgba(236,72,153,.22), transparent 55%),
                linear-gradient(135deg, rgba(15,23,42,.55) 0%, rgba(15,23,42,.35) 100%);
        }

        /* ========== 容器 ========== */
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
            padding: 34px 28px 28px;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.25),
                inset 0 1px 0 rgba(255, 255, 255, 0.25);
            position: relative;
            overflow: hidden;
            animation: card-appear .6s cubic-bezier(.22,1,.36,1) both;
        }
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

        /* ========== 返回登录按钮 ========== */
        .glass-back {
            position: absolute;
            top: 14px;
            left: 16px;
            z-index: 2;
            color: rgba(255,255,255,.85);
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background .15s;
        }
        .glass-back:hover { background: rgba(255,255,255,.15); }

        /* ========== Logo / 标题 ========== */
        .glass-header {
            text-align: center;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            margin-top: 10px;
        }
        .glass-logo {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(255,255,255,.22);
            border: 1px solid rgba(255,255,255,.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,.18);
        }
        .glass-title {
            font-size: 21px;
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
            margin-bottom: 14px;
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

        /* 手机号输入前半段固定标签 */
        .form-input__prefix {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,.8);
            font-size: 14px;
            padding-right: 12px;
            border-right: 1px solid rgba(255,255,255,.25);
        }
        .form-input--with-prefix {
            padding-left: 58px;
        }

        /* ========== 按钮（跟随系统标题栏背景色） ========== */
        .btn-register {
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
        .btn-register:active {
            transform: scale(.98);
            box-shadow: 0 3px 10px color-mix(in srgb, var(--h5-title-bg) 45%, transparent);
        }
        .btn-register:disabled {
            filter: grayscale(.5);
            opacity: .7;
            cursor: not-allowed;
        }

        /* ========== 底部 ========== */
        .form-footer {
            text-align: center;
            margin-top: 16px;
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

        /* 协议文字 */
        .form-agree {
            text-align: center;
            margin-top: 14px;
            font-size: 12px;
            color: rgba(255,255,255,.65);
            z-index: 1;
            position: relative;
            line-height: 1.6;
        }
        .form-agree a {
            color: rgba(255,255,255,.9);
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,.3);
        }

        /* ========== Toast ========== */
        .toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0,0,0,.75);
            color: #fff;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 14px;
            z-index: 9999;
            opacity: 0;
            transition: opacity .25s;
            pointer-events: none;
            max-width: 80%;
        }
        .toast.show { opacity: 1; }

        @media (max-width: 380px) {
            .glass-card { padding: 30px 22px 24px; border-radius: 20px; }
            .glass-title { font-size: 19px; }
        }
        @supports not (backdrop-filter: blur(22px)) {
            .glass-card { background: rgba(20, 30, 50, 0.65); }
        }
    </style>
</head>
<body>

    <?php if ($bg): ?>
    <div class="login-bg" style="background-image: url('<?= htmlspecialchars($bg) ?>');"></div>
    <?php else: ?>
    <div class="login-bg" style="background: linear-gradient(135deg,#831843,#1e3a8a,#312e81);"></div>
    <?php endif; ?>

    <div class="login-wrap">
        <form class="glass-card" id="register-form" autocomplete="off">
            <a href="/h5/passport/login" class="glass-back">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                已有账号
            </a>

            <div class="glass-header">
                <div class="glass-logo">✨</div>
                <div class="glass-title">创建账号</div>
                <div class="glass-subtitle">加入 <?= htmlspecialchars($storeName) ?>，享受更多优惠</div>
            </div>

            <div class="form-group">
                <span class="form-input__prefix">+86</span>
                <input type="tel" class="form-input form-input--with-prefix" name="mobile" id="mobile"
                       placeholder="请输入手机号" maxlength="11" inputmode="numeric"
                       autocomplete="off"/>
            </div>

            <div class="form-group">
                <input type="text" class="form-input" name="nickName" id="nickName"
                       placeholder="昵称（选填）" maxlength="20" autocomplete="off"/>
            </div>

            <button type="submit" class="btn-register" id="btnRegister">注 册</button>

            <div class="form-agree">
                注册即代表你同意
                <a href="/h5/passport/agreement">《用户协议》</a>
                和
                <a href="/h5/passport/privacy">《隐私政策》</a>
            </div>

            <div class="form-footer" style="margin-top: 10px;">
                已有账号？<a href="/h5/passport/login">立即登录</a>
            </div>
        </form>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        function toast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            clearTimeout(toast._t);
            toast._t = setTimeout(function () { t.classList.remove('show'); }, 2200);
        }

        document.getElementById('register-form').addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btnRegister');
            var mobile = document.getElementById('mobile').value.trim();
            var nickName = document.getElementById('nickName').value.trim();

            if (!/^1\d{10}$/.test(mobile)) { toast('请输入正确的 11 位手机号'); return; }

            btn.disabled = true;
            btn.textContent = '注册中...';

            var fd = new FormData();
            fd.append('mobile', mobile);
            fd.append('nickName', nickName);

            fetch('/h5/passport/doRegister', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.textContent = '注 册';
                    if (data.code === 1) {
                        try { localStorage.setItem('h5_token', data.data.token); } catch (e) {}
                        toast('注册成功');
                        setTimeout(function () {
                            location.href = data.data.redirect || '/h5/user/index.html';
                        }, 600);
                    } else {
                        toast(data.msg || '注册失败');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.textContent = '注 册';
                    toast('网络异常，请稍后再试');
                });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') document.getElementById('register-form').requestSubmit();
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
