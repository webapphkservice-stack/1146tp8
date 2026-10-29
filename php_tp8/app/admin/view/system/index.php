<?php
/** @var array $admin */
/** @var string $token */
/** @var array $stat */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - 运营管理系统</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            color: #fff; min-height: 100vh; overflow-x: hidden;
            background: linear-gradient(135deg, #285793 0%, #1b3a63 100%);
        }

        .content { padding: 22px 30px 40px; }
        .page-title { font-size: 24px; font-weight: 700; margin-bottom: 6px; }
        .page-sub { font-size: 13px; opacity: 0.72; margin-bottom: 22px; }
        .section-title { font-size: 16px; font-weight: 700; margin: 26px 0 12px; opacity: 0.92; }

        .panel {
            border-radius: 16px; padding: 20px 24px; margin-bottom: 18px;
            background: rgba(255,255,255,0.12); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.22); box-shadow: 0 10px 30px rgba(8,24,48,0.35);
        }
        .panel h3 { font-size: 15px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .panel h3 .sub { font-weight: 400; opacity: 0.55; font-size: 12px; margin-left: auto; }

        .stat-row { display: flex; gap: 16px; flex-wrap: wrap; }
        .stat-card {
            flex: 1; min-width: 180px; padding: 18px 20px; border-radius: 14px;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
        }
        .stat-card .num { font-size: 24px; font-weight: 700; }
        .stat-card .label { font-size: 12px; opacity: 0.7; margin-top: 6px; }

        /* 网站信息表单 */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 22px; }
        .form-grid .fld { display: flex; flex-direction: column; gap: 5px; }
        .form-grid label { font-size: 13px; opacity: 0.8; }
        .form-grid input, .form-grid textarea {
            padding: 9px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.12); color: #fff; font-size: 14px; outline: none;
            transition: border-color 0.15s;
        }
        .form-grid input:focus, .form-grid textarea:focus { border-color: #6db0ff; }
        .form-grid textarea { resize: vertical; min-height: 72px; grid-column: 1 / -1; }
        .panel-foot { margin-top: 16px; display: flex; justify-content: flex-end; gap: 10px; }

        .btn {
            border: none; border-radius: 9px; padding: 9px 18px; font-size: 13px; font-weight: 600;
            cursor: pointer; color: #fff; text-decoration: none; display: inline-block;
            transition: all 0.18s ease;
        }
        .btn-primary { background: rgba(40,87,147,0.9); }
        .btn-primary:hover { background: rgba(40,87,147,1); transform: translateY(-1px); }
        .btn-warn    { background: rgba(214,158,46,0.85); }
        .btn-warn:hover { filter: brightness(1.1); }
        .btn-danger  { background: rgba(196,72,72,0.85); }
        .btn-danger:hover { filter: brightness(1.1); }
        .btn-ghost   { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.25); }
        .btn-ghost:hover { background: rgba(255,255,255,0.20); }

        /* 环境检测表 */
        .env-section { margin-top: 12px; }
        .env-section h4 { font-size: 13px; font-weight: 600; opacity: 0.75; margin-bottom: 8px; }
        .env-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .env-table th, .env-table td { padding: 8px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .env-table th { opacity: 0.6; font-weight: 500; background: rgba(255,255,255,0.05); }
        .env-table .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600; }
        .badge-normal  { background: rgba(72,146,96,0.7); }
        .badge-warning { background: rgba(214,158,46,0.7); color: #2a1a00; }
        .badge-danger  { background: rgba(196,72,72,0.85); }

        .msg { padding: 9px 14px; border-radius: 8px; font-size: 13px; margin-top: 10px; display: none; }
        .msg-success { display: block; background: rgba(72,146,96,0.25); border: 1px solid rgba(72,146,96,0.5); }
        .msg-error   { display: block; background: rgba(196,72,72,0.25); border: 1px solid rgba(196,72,72,0.5); }
        .msg-info    { display: block; background: rgba(109,176,255,0.25); border: 1px solid rgba(109,176,255,0.5); }
    </style>
</head>
<body>
<div class="content">
    <div class="page-title">系统设置</div>
    <div class="page-sub">平台运行概览 · 网站信息 · 缓存与环境</div>

    <!-- 概览 -->
    <div class="panel">
        <h3>运行概览</h3>
        <div class="stat-row">
            <div class="stat-card"><div class="num"><?= $stat['store'] ?></div><div class="label">在售商城</div></div>
            <div class="stat-card"><div class="num"><?= $stat['store_user'] ?></div><div class="label">门店管理员</div></div>
            <div class="stat-card"><div class="num" style="font-size:16px;"><?= date('Y-m-d', (int)$stat['time_ts'] ?? time()) ?></div><div class="label">服务器时间</div></div>
            <div class="stat-card"><div class="num" style="font-size:16px;"><?= htmlspecialchars($stat['php'] ?? PHP_VERSION) ?></div><div class="label">PHP 版本</div></div>
            <div class="stat-card"><div class="num" style="font-size:16px;"><?= htmlspecialchars($stat['tp'] ?? \think\App::VERSION) ?></div><div class="label">ThinkPHP 版本</div></div>
        </div>
    </div>

    <!-- 网站信息 -->
    <div class="panel">
        <h3>📇 网站信息 <span class="sub">POST /admin/setting.web/index</span></h3>
        <form id="site-form">
            <div style="margin-bottom:14px; display:flex; align-items:flex-start; gap:16px;">
                <div style="min-width:96px;">
                    <div style="font-size:13px; opacity:0.8; margin-bottom:6px;">网站 logo</div>
                    <img id="s-logo-preview" alt="logo 预览" style="width:72px;height:72px;border-radius:10px;object-fit:contain;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);display:none;">
                    <div id="s-logo-empty" style="width:72px;height:72px;border-radius:10px;background:rgba(255,255,255,0.08);border:1px dashed rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;font-size:11px;opacity:0.6;">无 logo</div>
                </div>
                <div>
                    <input type="file" id="logo-file" accept="image/*" style="color:#fff;font-size:13px;">
                    <input type="hidden" name="site_info[logo]" id="s-logo">
                    <div style="display:flex; gap:8px; margin-top:8px;">
                        <button type="button" class="btn btn-ghost" id="logo-upload">上传 logo</button>
                        <button type="button" class="btn btn-ghost" id="logo-clear">清除</button>
                    </div>
                    <div style="font-size:11px; opacity:0.55; margin-top:6px;">上传后点击「保存网站信息」，显示在首页网站名称左侧</div>
                </div>
            </div>
            <div class="form-grid">
                <div class="fld">
                    <label>网站名称</label>
                    <input name="site_info[name]" id="s-name" placeholder="如：百信优选">
                </div>
                <div class="fld">
                    <label>网站备案号</label>
                    <input name="site_info[beian]" id="s-beian" placeholder="如：粤ICP备00000000号">
                </div>
                <div class="fld">
                    <label>联系方式</label>
                    <input name="site_info[contact]" id="s-contact" placeholder="如：admin@example.com / 400-000-0000">
                </div>
            </div>
            <div class="panel-foot">
                <button type="button" class="btn btn-primary" id="site-save">💾 保存网站信息</button>
            </div>
        </form>
        <div id="site-msg" class="msg"></div>
    </div>

    <!-- 清理缓存 -->
    <div class="panel">
        <h3>🧹 清理缓存 <span class="sub">POST /admin/setting.cache/clear</span></h3>
        <p style="font-size:12px; opacity:0.6; margin-bottom:12px;">
            安全项：模板编译 = runtime/temp + runtime/admin/temp（改 PHP 代码后清这个立刻生效）；日志 = runtime/log + runtime/admin/log。全部清理 = 模板编译 + 日志 + OPcache，<b>不掉线</b>。
        </p>
        <div class="stat-row" style="gap:12px;">
            <button class="btn btn-warn" id="clr-temp">📄  清模板编译</button>
            <button class="btn btn-danger" id="clr-all">♨  全部清理（推荐）</button>
            <button class="btn btn-ghost" id="clr-data" style="border-color:rgba(196,72,72,0.5);color:#ffb3b3;">⚠ 清数据缓存（会掉线）</button>
        </div>
        <div id="clr-msg" class="msg"></div>
    </div>

    <!-- 环境检测 -->
    <div class="panel">
        <h3>🩺 环境检测 <span class="sub">GET /admin/setting.science/index</span></h3>
        <div style="margin-bottom:12px;">
            <button class="btn btn-primary" id="env-check">🔍 立即检测</button>
        </div>
        <div id="env-result" style="display:none;">
            <div class="env-section">
                <h4>PHP 环境要求</h4>
                <table class="env-table" id="env-php"></table>
            </div>
            <div class="env-section">
                <h4>服务器信息</h4>
                <table class="env-table" id="env-server"></table>
            </div>
            <div class="env-section">
                <h4>目录可写性</h4>
                <table class="env-table" id="env-writeable"></table>
            </div>
        </div>
        <div id="env-empty" class="msg msg-info" style="display:block;">点击上方按钮开始检测</div>
    </div>
</div>

<script src="/assets/common/js/jquery.min.js"></script>
<script src="/assets/common/js/jquery4-polyfill.js"></script>
<script>
var TOKEN = '<?= htmlspecialchars($token) ?>';

// === 通用 AJAX 包装 ===
function api(url, data, done) {
    $.ajax({
        url: url + '?token=' + TOKEN,
        type: 'POST',
        dataType: 'json',
        data: data || {},
        success: function (res) { done && done(res); },
        error: function () { done && done({ code: 0, msg: '网络错误' }); }
    });
}
function get(url, done) {
    $.ajax({
        url: url + '?token=' + TOKEN,
        type: 'GET',
        dataType: 'json',
        success: function (res) { done && done(res); },
        error: function () { done && done({ code: 0, msg: '网络错误' }); }
    });
}
function showMsg(el, text, type) {
    $(el).removeClass('msg-success msg-error msg-info').addClass('msg-' + (type || 'info')).text(text).show();
}

// === 1. 网站信息 ===
(function () {
    get('/admin/setting.web/index', function (res) {
        if (res.code === 1 && res.data && res.data.values) {
            $('#s-name').val(res.data.values.name || '');
            $('#s-beian').val(res.data.values.beian || '');
            $('#s-contact').val(res.data.values.contact || '');
            var logo = res.data.values.logo || '';
            $('#s-logo').val(logo);
            if (logo) {
                $('#s-logo-preview').attr('src', logo).show();
                $('#s-logo-empty').hide();
            } else {
                $('#s-logo-preview').hide();
                $('#s-logo-empty').show();
            }
        }
    });

    // 网站 logo 上传
    $('#logo-upload').click(function () {
        var f = $('#logo-file')[0].files[0];
        if (!f) { showMsg('#site-msg', '❌ 请先选择图片', 'error'); return; }
        if (f.size > 5 * 1024 * 1024) { showMsg('#site-msg', '❌ 图片不能超过 5MB', 'error'); return; }
        var fd = new FormData();
        fd.append('iFile', f);
        $.ajax({
            url: '/admin/setting.upload/image?token=' + encodeURIComponent(TOKEN),
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.code === 1 && res.data && res.data.file_path) {
                    var url = res.data.file_path;
                    $('#s-logo').val(url);
                    $('#s-logo-preview').attr('src', url).show();
                    $('#s-logo-empty').hide();
                    $('#logo-file').val('');
                    showMsg('#site-msg', '✅ logo 上传成功，点击「保存网站信息」生效', 'success');
                } else {
                    showMsg('#site-msg', '❌ ' + (res.msg || '上传失败'), 'error');
                }
            },
            error: function () { showMsg('#site-msg', '❌ 网络错误', 'error'); }
        });
    });
    $('#logo-clear').click(function () {
        $('#s-logo').val('');
        $('#s-logo-preview').hide().attr('src', '');
        $('#s-logo-empty').show();
        $('#logo-file').val('');
    });

    $('#site-save').click(function () {
        var data = $('#site-form').serialize();
        api('/admin/setting.web/index', data, function (res) {
            if (res.code === 1) {
                showMsg('#site-msg', '✅ 保存成功', 'success');
            } else {
                showMsg('#site-msg', '❌ ' + (res.msg || '保存失败'), 'error');
            }
        });
    });
})();

// === 2. 清理缓存 ===
function clearCache(items) {
    api('/admin/setting.cache/clear', $.map(items, function (i) { return { name: 'item[]', value: i }; }), function (res) {
        if (res.code === 1) {
            showMsg('#clr-msg', '✅ ' + res.msg, 'success');
        } else {
            showMsg('#clr-msg', '❌ ' + (res.msg || '清理失败'), 'error');
        }
    });
}
$('#clr-temp').click(function () { clearCache(['temp']); });
$('#clr-all').click(function () { clearCache(['temp', 'opcache']); });
$('#clr-data').click(function () {
    if (!confirm('⚠ 清数据缓存会清除登录态，操作后需重新登录。确定继续？')) return;
    clearCache(['data']);
});

// === 3. 环境检测 ===
function statusBadge(s) {
    var map = { normal: ['✅ 通过', 'badge-normal'], warning: ['⚠ 警告', 'badge-warning'], danger: ['❌ 不通过', 'badge-danger'] };
    var v = map[s] || ['—', ''];
    return '<span class="badge ' + v[1] + '">' + v[0] + '</span>';
}
function renderEnvTable($table, rows) {
    var html = '<thead><tr><th>项目</th><th>值</th><th>状态</th><th>说明</th></tr></thead><tbody>';
    rows.forEach(function (r) {
        html += '<tr>' +
            '<td><b>' + r.name + '</b></td>' +
            '<td>' + (r.value || '—') + '</td>' +
            '<td>' + statusBadge(r.status) + '</td>' +
            '<td style="opacity:0.6;">' + (r.remark || '') + '</td>' +
            '</tr>';
    });
    html += '</tbody>';
    $table.html(html);
}
$('#env-check').click(function () {
    var $btn = $(this).prop('disabled', true).text('检测中…');
    get('/admin/setting.science/index', function (res) {
        $btn.prop('disabled', false).text('🔍 立即检测');
        if (res.code !== 1) { showMsg('#env-empty', '❌ ' + (res.msg || '检测失败'), 'error'); return; }
        $('#env-empty').hide();
        $('#env-result').show();
        renderEnvTable($('#env-php'), res.data.phpinfo || []);
        renderEnvTable($('#env-server'), res.data.server || []);
        renderEnvTable($('#env-writeable'), res.data.writeable || []);
        showMsg('#env-empty', '✅ 检测完成，共 ' + ((res.data.phpinfo||[]).length + (res.data.server||[]).length + (res.data.writeable||[]).length) + ' 项', 'success');
        $('#env-empty').show();
    });
});
</script>
</body>
</html>
