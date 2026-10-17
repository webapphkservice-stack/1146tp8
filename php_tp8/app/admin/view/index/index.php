<?php
/** @var array $admin */
/** @var string $token */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>运营管理系统 - 桌面</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            color: #fff; overflow: hidden;
            background: linear-gradient(135deg, #285793 0%, #1b3a63 100%);
        }

        /* 背景轮播层（复用登录页 7 张图，双缓冲交叉淡入） */
        .bg-layer {
            position: fixed; inset: 0; z-index: -1;
            background-size: cover; background-position: center; background-repeat: no-repeat;
            opacity: 0; transition: opacity 1.4s ease;
        }

        /* 顶部菜单栏（仿 macOS 菜单条） */
        .menubar {
            position: fixed; top: 0; left: 0; right: 0; height: 30px; z-index: 20;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; font-size: 13px;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }
        .menubar .mb-left { display: flex; align-items: center; gap: 8px; font-weight: 600; }
        .menubar .logo { color: #6db0ff; }
        .menubar .mb-right { opacity: 0.9; }

        /* 中央欢迎语 */
        .welcome {
            position: fixed; top: 30%; left: 0; right: 0; text-align: center; z-index: 5;
            pointer-events: none; text-shadow: 0 2px 18px rgba(0, 0, 0, 0.55);
        }
        .welcome h1 { font-size: 34px; font-weight: 700; letter-spacing: 1px; }
        .welcome p { margin-top: 10px; font-size: 15px; opacity: 0.88; }

        /* 底部程序坞（macOS Dock） */
        .dock {
            position: fixed; bottom: 18px; left: 50%; transform: translateX(-50%); z-index: 20;
            display: flex; align-items: flex-end; gap: 26px; padding: 14px 28px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(26px); -webkit-backdrop-filter: blur(26px);
            border: 1px solid rgba(255, 255, 255, 0.30);
            border-radius: 28px; box-shadow: 0 18px 56px rgba(0, 0, 0, 0.45);
        }
        .dock-item {
            position: relative; width: 68px; height: 68px;
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.18s ease; transform-origin: bottom center; cursor: pointer;
            text-decoration: none;
        }
        .dock-item img {
            width: 60px; height: 60px; border-radius: 15px; object-fit: contain;
            background: rgba(255, 255, 255, 0.06);
            box-shadow: 0 7px 18px rgba(0, 0, 0, 0.40); transition: transform 0.18s ease;
        }
        .circle-icon {
            width: 60px; height: 60px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            box-shadow: 0 7px 18px rgba(0, 0, 0, 0.40);
        }
        .circle-icon svg { display: block; }
        .dock-item .label {
            position: absolute; top: -38px; left: 50%; transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.62); color: #fff; font-size: 13px;
            padding: 5px 10px; border-radius: 7px; white-space: nowrap; opacity: 0; transition: opacity 0.15s;
        }
        .dock-item:hover { transform: translateY(-28px) scale(1.6); }
        .dock-item:hover .label { opacity: 1; }
        /* 相邻图标随动放大，模拟 macOS Dock 放大效果 */
        .dock-item:hover + .dock-item { transform: translateY(-12px) scale(1.22); }
        .dock-item:has(+ .dock-item:hover) { transform: translateY(-12px) scale(1.22); }

        /* 应用窗口（macOS 风格） */
        .app-window {
            position: fixed; left: 50%; top: 50%;
            transform: translate(-50%, -50%);
            width: min(1200px, 92vw); height: min(740px, 82vh);
            z-index: 30;
            display: flex; flex-direction: column;
            border-radius: 14px; overflow: hidden;
            background: rgba(255, 255, 255, 0.10);
            backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.28);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
            transition: box-shadow 0.2s ease;
        }
        .app-window[hidden] { display: none; }
        .app-window.active { box-shadow: 0 36px 90px rgba(0, 0, 0, 0.6); }
        .win-titlebar {
            height: 36px; display: flex; align-items: center; justify-content: space-between;
            padding: 0 14px; background: rgba(255, 255, 255, 0.14);
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
            user-select: none; cursor: grab;
        }
        .win-buttons { display: flex; gap: 8px; }
        .win-btn { width: 13px; height: 13px; border-radius: 50%; display: inline-block; cursor: pointer; }
        .win-btn.close { background: #ff5f57; border: 1px solid #e0443e; }
        .win-btn.min   { background: #febc2e; border: 1px solid #dea123; }
        .win-btn.max   { background: #28c840; border: 1px solid #1aab29; }
        .win-title { font-size: 13px; font-weight: 600; opacity: 0.9; }
        .win-body { flex: 1; overflow: hidden; background: transparent; }
        .win-body iframe { width: 100%; height: 100%; border: 0; display: block; background: transparent; }
    </style>
</head>
<body>
<div id="bg1" class="bg-layer"></div>
<div id="bg2" class="bg-layer"></div>

<div class="menubar">
    <div class="mb-left"><span class="logo">❖</span> <?= htmlspecialchars($siteName) ?> · 运营管理</div>
    <div class="mb-right"><span id="clock"></span> · 欢迎 <?= htmlspecialchars($admin['user_name'] ?? '') ?></div>
</div>

<div class="welcome">
    <h1>欢迎回来，<?= htmlspecialchars($admin['user_name'] ?? '') ?></h1>
    <p>从下方程序坞打开「商城」或「系统」</p>
</div>

<div class="dock">
    <a class="dock-item" href="javascript:void(0)" data-app="dashboard">
        <img src="/assets/common/img/dock/dashboard.png" alt="看板">
        <span class="label">看板</span>
    </a>
    <a class="dock-item" href="javascript:void(0)" data-app="store">
        <img src="/assets/common/img/dock/store.png" alt="商城">
        <span class="label">商城</span>
    </a>
    <a class="dock-item" href="javascript:void(0)" data-app="system">
        <img src="/assets/common/img/dock/system.png" alt="系统">
        <span class="label">系统</span>
    </a>
    <div class="dock-item" id="dock-logout">
        <div class="circle-icon"><svg viewBox="0 0 24 24" width="34" height="34" fill="currentColor" aria-label="退出"><path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42A6.92 6.92 0 0 1 19 12c0 3.31-2.69 6-6 6s-6-2.69-6-6c0-2.17 1.16-4.06 2.88-5.12L8.46 5.17A8.94 8.94 0 0 0 5 12c0 4.42 3.58 8 8 8s8-3.58 8-8c0-2.83-1.31-5.31-3.17-6.83z"/></svg></div>
        <span class="label">退出</span>
    </div>
</div>

<!-- 商城窗口 -->
<div class="app-window" id="win-store" hidden>
    <div class="win-titlebar">
        <div class="win-buttons">
            <span class="win-btn close" data-target="win-store"></span>
            <span class="win-btn min"></span>
            <span class="win-btn max"></span>
        </div>
        <div class="win-title">商城管理</div>
        <div style="width:52px;"></div>
    </div>
    <div class="win-body">
        <iframe data-src="/admin/store/index?token=<?= htmlspecialchars($token) ?>"></iframe>
    </div>
</div>

<!-- 系统窗口 -->
<div class="app-window" id="win-system" hidden>
    <div class="win-titlebar">
        <div class="win-buttons">
            <span class="win-btn close" data-target="win-system"></span>
            <span class="win-btn min"></span>
            <span class="win-btn max"></span>
        </div>
        <div class="win-title">系统设置</div>
        <div style="width:52px;"></div>
    </div>
    <div class="win-body">
        <iframe data-src="/admin/index/system?token=<?= htmlspecialchars($token) ?>"></iframe>
    </div>
</div>

<!-- 看板窗口 -->
<div class="app-window" id="win-dashboard" hidden>
    <div class="win-titlebar">
        <div class="win-buttons">
            <span class="win-btn close" data-target="win-dashboard"></span>
            <span class="win-btn min"></span>
            <span class="win-btn max"></span>
        </div>
        <div class="win-title">数据看板</div>
        <div style="width:52px;"></div>
    </div>
    <div class="win-body">
        <iframe data-src="/admin/index/dashboard?token=<?= htmlspecialchars($token) ?>"></iframe>
    </div>
</div>

<script>
    // 顶部时钟
    (function () {
        function p(n) { return String(n).padStart(2, '0'); }
        function tick() {
            var d = new Date();
            document.getElementById('clock').textContent =
                p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds());
        }
        tick();
        setInterval(tick, 1000);
    })();

    // 桌面背景：每次登录随机选一张，不持续轮播
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
        var layer = document.getElementById('bg1');
        if (!layer) return;
        var curImg = Math.floor(Math.random() * imgs.length);
        layer.style.backgroundImage = "url('" + imgs[curImg] + "')";
        layer.style.opacity = '1';
        // 预加载其它图片
        imgs.forEach(function (s) { var i = new Image(); i.src = s; });
    })();

    // 退出登录
    document.getElementById('dock-logout').addEventListener('click', function () {
        try { localStorage.removeItem('admin_token'); } catch (e) {}
        window.location.href = '/admin/passport/login';
    });

    // 打开/聚焦应用窗口
    function openApp(appId) {
        var win = document.getElementById('win-' + appId);
        if (!win) return;
        document.querySelectorAll('.app-window').forEach(function (w) {
            w.classList.remove('active');
            w.style.zIndex = '30';
        });
        win.style.zIndex = '31';
        win.classList.add('active');
        var iframe = win.querySelector('iframe');
        if (iframe && !iframe.src && iframe.dataset.src) {
            iframe.src = iframe.dataset.src;
        }
        win.hidden = false;
    }
    // Dock 图标点击 → 打开对应应用窗口
    document.querySelectorAll('.dock-item[data-app]').forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            openApp(item.getAttribute('data-app'));
        });
    });
    // 窗口关闭按钮
    document.querySelectorAll('.win-btn.close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var w = document.getElementById(btn.getAttribute('data-target'));
            if (w) w.hidden = true;
        });
    });
</script>
</body>
</html>
