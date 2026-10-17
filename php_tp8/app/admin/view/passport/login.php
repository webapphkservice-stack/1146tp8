<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <title><?= htmlspecialchars($siteName) ?>-运营管理系统</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link rel="icon" type="image/png" href="/assets/common/i/favicon.ico"/>
    <style>
        :root {
            --primary: rgb(40, 87, 147);
            --primary-light: rgb(58, 116, 185);
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
            overflow: hidden;
            position: relative;
            color: #fff;
            /* 背景兜底（实际背景由 JS 切换的 .bg-layer 呈现） */
            background: linear-gradient(135deg, #285793 0%, #1b3a63 100%);
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
            background: rgba(130, 180, 240, 0.6);
            top: -140px;
            left: -100px;
        }

        body::after {
            width: 460px;
            height: 460px;
            background: rgba(30, 70, 120, 0.7);
            bottom: -140px;
            right: -80px;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 380px;
            max-width: 90vw;
            padding: 48px 40px 40px;
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

        .brand {
            text-align: center;
            margin-bottom: 34px;
        }

        .brand-text {
            color: #fff;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
        }

        .form-group {
            margin-bottom: 20px;
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
            box-shadow: 0 0 0 3px rgba(40, 87, 147, 0.4);
        }

        #btn-submit {
            width: 100%;
            height: 46px;
            margin-top: 4px;
            border: 1px solid rgba(255, 255, 255, 0.45);
            border-radius: 12px;
            /* 磨砂玻璃按钮：主题色半透明 + 背景模糊 */
            background: rgba(40, 87, 147, 0.38);
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

        #btn-submit:hover {
            background: rgba(40, 87, 147, 0.55);
            transform: translateY(-2px);
        }

        #btn-submit:active {
            transform: translateY(0);
        }

        #btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
<div id="bg1" class="bg-layer"></div>
<div id="bg2" class="bg-layer"></div>
<div class="login-card">
    <div class="brand">
        <h2 class="brand-text"><?= htmlspecialchars($siteName) ?>-运营管理系统</h2>
    </div>
    <form id="login-form" class="login-form">
        <div class="form-group">
            <input class="" name="User[user_name]" placeholder="请输入用户名" type="text" required>
        </div>
        <div class="form-group">
            <input class="" name="User[password]" placeholder="请输入密码" type="password" required>
        </div>
        <div class="form-group">
            <button id="btn-submit" type="submit">
                登录
            </button>
        </div>
    </form>
</div>
</body>
<script src="/assets/common/js/jquery.min.js"></script>
    <script src="/assets/common/js/jquery4-polyfill.js"></script>
    <link rel="stylesheet" href="/assets/common/plugins/layer/theme/gpt/layer.css">
    <script src="/assets/common/plugins/layer/layer.js"></script>
    <script src="/assets/common/js/jquery.form.min.js"></script>
<script>
    $(function () {
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

        var $form = $('#login-form');
        $form.submit(function () {
            var $btn = $('#btn-submit');
            $btn.attr("disabled", true);
            $form.ajaxSubmit({
                type: "post",
                dataType: "json",
                success: function (result) {
                    $btn.attr('disabled', false);
                    if (result.code === 1) {
                        // 总后台为 JSON API：登录成功返回 token，前端自行持有
                        if (result.data && result.data.token) {
                            try {
                                localStorage.setItem('admin_token', result.data.token);
                            } catch (e) {
                            }
                            // 登录成功：携带 token 跳转至后台首页
                            window.location.href = '/admin/index/index?token=' + encodeURIComponent(result.data.token);
                            return;
                        }
                        layer.msg(result.msg || '登录成功', {time: 1500, anim: 1});
                    } else {
                        layer.msg(result.msg || '登录失败', {time: 1500, anim: 6});
                    }
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
                                layer.msg((r && r.msg) || '登录失败', {time: 1500, anim: 6});
                            }
                        },
                        error: function () {
                            layer.msg('网络错误，请重试', {time: 1500, anim: 6});
                        }
                    });
                }
            } catch (e) {}
        })();
    });
</script>
</html>
