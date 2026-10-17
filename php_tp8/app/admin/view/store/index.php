<?php
/** @var array $admin */
/** @var string $token */
/** @var \think\Paginator $list */
/** @var array $stat */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>小程序商城管理 - 运营管理系统</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            color: #1f2d3d;
            background: linear-gradient(135deg, #285793 0%, #1b3a63 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== 布局 ===== */
        .content { padding: 22px 30px; color: #fff; }
        .page-title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .page-sub { font-size: 13px; opacity: 0.7; margin-bottom: 18px; }
        .tabs {
            display: inline-flex; gap: 4px; padding: 4px; border-radius: 12px;
            background: rgba(255,255,255,0.10); margin-bottom: 4px;
            border: 1px solid rgba(255,255,255,0.18);
        }
        .tabs a {
            padding: 7px 22px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.78);
            text-decoration: none; border-radius: 9px; transition: all 0.18s ease;
        }
        .tabs a.active { background: rgba(109,176,255,0.85); color: #fff; box-shadow: 0 3px 12px rgba(0,0,0,0.25); }

        /* ===== 统计卡片 ===== */
        .stat-row { display: flex; gap: 18px; margin-bottom: 24px; flex-wrap: wrap; }
        .stat-card {
            flex: 1; min-width: 200px; padding: 20px 22px; border-radius: 16px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.22);
            box-shadow: 0 10px 30px rgba(8,24,48,0.35);
        }
        .stat-card .num { font-size: 30px; font-weight: 700; color: #fff; }
        .stat-card .label { font-size: 13px; opacity: 0.75; margin-top: 6px; }
        .stat-card .bar { height: 4px; border-radius: 4px; margin-top: 14px; background: linear-gradient(90deg,#6db0ff,#285793); }

        /* ===== 内容面板 ===== */
        .panel {
            border-radius: 18px; background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.22);
            box-shadow: 0 10px 30px rgba(8,24,48,0.35); overflow: hidden;
        }
        .panel-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 22px; border-bottom: 1px solid rgba(255,255,255,0.14);
        }
        .panel-head h3 { font-size: 16px; font-weight: 600; }
        .btn {
            border: none; border-radius: 10px; padding: 9px 16px; font-size: 13px; font-weight: 600;
            cursor: pointer; color: #fff; transition: all 0.2s ease; text-decoration: none; display: inline-block;
        }
        .btn-primary { background: rgba(40,87,147,0.85); }
        .btn-primary:hover { background: rgba(40,87,147,1); transform: translateY(-1px); }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; font-size: 13px; font-weight: 600; opacity: 0.78;
            padding: 14px 22px; background: rgba(255,255,255,0.06);
        }
        tbody td { padding: 14px 22px; font-size: 14px; border-top: 1px solid rgba(255,255,255,0.10); }
        tbody tr:hover { background: rgba(255,255,255,0.06); }
        .empty { text-align: center; padding: 40px; opacity: 0.7; }

        .op { display: inline-flex; gap: 8px; }
        .op a {
            font-size: 12px; padding: 6px 12px; border-radius: 8px; cursor: pointer;
            text-decoration: none; color: #fff; border: 1px solid transparent;
        }
        .op .enter { background: rgba(40,87,147,0.85); }
        .op .recycle { background: rgba(214,158,46,0.85); }
        .op .recover { background: rgba(72,146,96,0.85); }
        .op .del { background: rgba(196,72,72,0.85); }
        .op a:hover { filter: brightness(1.1); }

        /* 密码列 */
        .pwd-mask { font-family: monospace; color: #999; letter-spacing: 2px; margin-right: 10px; }
        .pwd-reset {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 14px;
            background: rgba(100,116,139,0.85);
            color: #fff;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            transition: filter .15s;
        }
        .pwd-reset:hover { filter: brightness(1.15); }
        .pwd-reset.empty { background: transparent; color: #888; cursor: default; padding: 0; }

        .pager { padding: 14px 22px; font-size: 13px; opacity: 0.8; display: flex; justify-content: space-between; }

        /* 新增表单弹窗 */
        .form-layer { padding: 22px; }
        .form-layer .fld { margin-bottom: 14px; }
        .form-layer label { display: block; font-size: 13px; margin-bottom: 6px; opacity: 0.85; }
        .form-layer input {
            width: 100%; padding: 10px 12px; border-radius: 9px; border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.12); color: #fff; font-size: 14px; outline: none;
        }
        .form-layer input:focus { border-color: #6db0ff; }
        .form-layer .submit { margin-top: 8px; width: 100%; padding: 11px; border: none; border-radius: 10px;
            background: rgba(40,87,147,0.9); color: #fff; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
<div class="content">
    <div class="page-title">小程序商城管理</div>
    <div class="tabs">
        <a href="/admin/store/index?mode=active&token=<?= htmlspecialchars($token) ?>"
           class="<?= $mode === 'active' ? 'active' : '' ?>">在售商城（<?= $stat['active'] ?>）</a>
        <a href="/admin/store/index?mode=recycle&token=<?= htmlspecialchars($token) ?>"
           class="<?= $mode === 'recycle' ? 'active' : '' ?>">♻ 回收站（<?= $stat['recycle'] ?>）</a>
    </div>
    <div class="page-sub"><?= $mode === 'recycle' ? '已移入回收站的商城，可恢复或彻底删除。' : '管理入驻的小程序商城，可新增、进入后台、移入回收站或删除。' ?></div>

        <div class="stat-row">
            <div class="stat-card">
                <div class="num"><?= $stat['active'] ?></div>
                <div class="label">在售商城</div>
                <div class="bar"></div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $stat['recycle'] ?></div>
                <div class="label">回收站</div>
                <div class="bar"></div>
            </div>
            <div class="stat-card">
                <div class="num"><?= $stat['store_user'] ?></div>
                <div class="label">门店管理员</div>
                <div class="bar"></div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3><?= $mode === 'recycle' ? '回收站列表' : '商城列表' ?></h3>
                <?php if ($mode !== 'recycle'): ?>
                <button class="btn btn-primary" id="btn-add">+ 新增商城</button>
                <?php else: ?>
                <span></span>
                <?php endif; ?>
            </div>
            <table>
                <thead>
                <tr>
                    <th>商城ID</th>
                    <th>商城名称</th>
                    <th>管理员账号</th>
                    <th>密码</th>
                    <th>AppID</th>
                    <th>添加时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($list->isEmpty()): ?>
                    <tr><td colspan="7" class="empty">暂无商城记录</td></tr>
                <?php else: foreach ($list as $item): ?>
                    <tr>
                        <td><?= $item['wxapp_id'] ?></td>
                        <td><b><?= htmlspecialchars($item['app_name'] ?: '（未命名）') ?></b></td>
                        <td><?= htmlspecialchars($storeUserMap[$item['wxapp_id']] ?? '—') ?></td>
                        <td>
                            <span class="pwd-mask">••••••••</span>
                            <?php if (!empty($storeUserMap[$item['wxapp_id']])): ?>
                            <a class="pwd-reset" data-wxapp="<?= $item['wxapp_id'] ?>">重置</a>
                            <?php else: ?>
                            <span class="pwd-reset empty">无账号</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['app_id']) ?></td>
                        <td><?= date('Y-m-d H:i:s', (int)$item['create_time']) ?></td>
                        <td>
                            <div class="op">
                                <?php if ($mode === 'recycle'): ?>
                                <a class="recover" data-id="<?= $item['wxapp_id'] ?>">恢复</a>
                                <?php else: ?>
                                <a class="enter" data-id="<?= $item['wxapp_id'] ?>">进入商城</a>
                                <a class="recycle" data-id="<?= $item['wxapp_id'] ?>">回收站</a>
                                <?php endif; ?>
                                <a class="del" data-id="<?= $item['wxapp_id'] ?>">删除</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            <div class="pager">
                <span>共 <?= $list->total() ?> 条</span>
                <span>第 <?= $list->currentPage() ?> / <?= $list->lastPage() ?> 页</span>
            </div>
        </div>
    </div>

<script src="/assets/common/js/jquery.min.js"></script>
    <script src="/assets/common/js/jquery4-polyfill.js"></script>
<link rel="stylesheet" href="/assets/common/plugins/layer/theme/gpt/layer.css">
<script src="/assets/common/plugins/layer/layer.js"></script>
    <script>
    // layer 全局配置 —— 统一液态玻璃风格的弹窗
    layer.config({
        anim: -1,          // 不使用 layer 自带动画（CSS 皮肤已自带 gpt-pop）
        skin: 'gpt',       // 皮肤类名前缀
        shift: 2,          // 垂直居中
        maxmin: false,
        shadeClose: true
    });

    var TOKEN = '<?= htmlspecialchars($token) ?>';
    function api(url, data, done) {
        $.post(url + '?token=' + TOKEN, data, function (res) {
            if (res.code === 1) { done && done(res); }
            else { layer.msg(res.msg || '操作失败', {icon: 2}); }
        }, 'json');
    }

    // 进入商城：先写 store 登录态，再新窗口打开 store 后台
    $(document).on('click', '.op .enter', function () {
        var id = $(this).data('id');
        api('/admin/store/enter', {wxapp_id: id}, function (res) {
            layer.msg('已进入，正在打开商城后台…', {icon: 1});
            window.open('/store/index/index?wxapp_id=' + id, '_blank');
        });
    });

    // 移入回收站
    $(document).on('click', '.op .recycle', function () {
        var id = $(this).data('id');
        layer.confirm('确定移入回收站？', function (idx) {
            layer.close(idx);
            api('/admin/store/recycleWxapp', {wxapp_id: id}, function () { location.reload(); });
        });
    });

    // 恢复（从回收站移回）
    $(document).on('click', '.op .recover', function () {
        var id = $(this).data('id');
        api('/admin/store/recoveryWxapp', {wxapp_id: id}, function () {
            location.href = '/admin/store/index?mode=active&token=' + TOKEN;
        });
    });

    // 删除（软删除）
    $(document).on('click', '.op .del', function () {
        var id = $(this).data('id');
        layer.confirm('确定彻底删除？此操作不可恢复。', function (idx) {
            layer.close(idx);
            api('/admin/store/delete', {wxapp_id: id}, function () { location.reload(); });
        });
    });

    // 重置商城管理员密码
    $(document).on('click', '.pwd-reset:not(.empty)', function () {
        var wxappId = $(this).data('wxapp');
        var $btn = $(this);
        if (!wxappId) return;
        layer.confirm(
            '确定重置该商城管理员密码？重置后新密码将立即生效，旧密码作废。',
            function (idx) {
                layer.close(idx);
                $.post('/admin/store/resetPassword?token=' + TOKEN, {wxapp_id: wxappId}, function (res) {
                    if (res.code === 1 && res.data && res.data.password) {
                        // 把重置按钮临时替换成新密码展示，30 秒后还原
                        var $row = $btn.closest('tr');
                        var $mask = $row.find('.pwd-mask');
                        var $col = $btn.closest('td');
                        $mask.text(res.data.password);
                        $btn.remove();
                        layer.alert(
                            '商城 「' + ($row.find('td:nth-child(2) b').text() || '') + '」 ' +
                            '账号 <b>' + res.data.user_name + '</b> 的新密码是：' +
                            '<b style="color:#c62828;font-size:16px;letter-spacing:2px;">' + res.data.password + '</b><br>' +
                            '请立即告知使用者并妥善保存，此密码仅在此展示一次。',
                            {icon: 1, title: '密码已重置'}
                        );
                    } else {
                        layer.msg(res.msg || '重置失败', {icon: 2});
                    }
                }, 'json');
            }
        );
    });

    // 新增商城
    $('#btn-add').click(function () {
        var html = '<div class="form-layer">' +
            '<div class="fld"><label>商城名称</label><input id="f-store" placeholder="如：百信优选"></div>' +
            '<div class="fld"><label>管理员账号</label><input id="f-user" placeholder="如：admin"></div>' +
            '<div class="fld"><label>密码</label><input id="f-pass" type="password" placeholder="登录密码"></div>' +
            '<div class="fld"><label>确认密码</label><input id="f-conf" type="password" placeholder="再次输入密码"></div>' +
            '<button class="submit" id="f-submit">提交</button></div>';
        layer.open({ title: '新增小程序商城', area: ['420px', 'auto'], content: html });
        $('#f-submit').click(function () {
            var data = {
                'store[store_name]': $('#f-store').val(),
                'store[user_name]': $('#f-user').val(),
                'store[password]': $('#f-pass').val(),
                'store[password_confirm]': $('#f-conf').val()
            };
            api('/admin/store/add', data, function () { layer.closeAll(); location.reload(); });
        });
    });
</script>
</body>
</html>
