<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">用户水票（送水）</div>
                </div>
                <div class="widget-body float-right">

                    <!-- 发放 / 扣减 -->
                    <form id="grant-form" class=" flex flex-wrap items-center gap-3" onsubmit="return false;"
                          style="margin-bottom:12px;">
                        <div class="spec-form-inline inline-flex items-center gap-1">
                            <input type="number" class="form-input" name="user_id" placeholder="用户ID" min="1"
                                   required style="width:110px">
                        </div>
                        <div class="spec-form-inline inline-flex items-center gap-1">
                            <input type="number" class="form-input" name="num" placeholder="数量：正数发放 / 负数扣减"
                                   required style="width:200px">
                        </div>
                        <div class="spec-form-inline inline-flex items-center gap-1">
                            <input type="text" class="form-input" name="remark" placeholder="备注(可选)"
                                   style="width:180px">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">发放 / 扣减</button>
                    </form>
                    <div class="help-block" style="margin:-6px 0 12px;">
                        <small>1 张水票 = 1 桶桶装水，用户下单选择「门店配送」时可抵扣。发放后该用户会出现在下方列表。</small>
                    </div>

                    <!-- 搜索 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/store/water_ticket/index">
                                <div class="form-group ">
                                    <div class="input-group input-group-sm tpl-form-border-form">
                                        <input type="text" class="form-input" name="search"
                                               placeholder="用户ID / 昵称" value="<?= htmlspecialchars($search) ?>">
                                        <div class="input-group-btn">
                                            <button class="btn btn-default icon-search" type="submit"></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- 列表 -->
                    <div class="sm:w-full">
                        <table width="100%"
                               class="table-base text-center whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>昵称</th>
                                <th>可用水票(张)</th>
                                <th>更新时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($list->isEmpty()): ?>
                                <tr>
                                    <td colspan="4" class="text-center">暂无水票用户</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="align-middle"><?= $item['user_id'] ?></td>
                                        <td class="align-middle"><?= $item['nickName'] ?: '--' ?></td>
                                        <td class="align-middle">
                                            <span class="badge badge-success"><?= $item['water_ticket_num'] ?></span>
                                        </td>
                                        <td class="align-middle"><?= $item['update_time'] ? date('Y-m-d H:i', $item['update_time']) : '--' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="sm:w-full flex justify-center">
                            <?= $list->render() ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#grant-form').on('submit', function () {
            var form = $(this);
            var num = parseInt(form.find('input[name="num"]').val(), 10) || 0;
            var tips = num > 0
                ? ('确认给该用户发放 ' + num + ' 张水票？')
                : ('确认扣减该用户 ' + Math.abs(num) + ' 张水票？');
            layer.confirm(tips, function (index) {
                layer.close(index);
                $.post("<?= url('water_ticket/index') ?>", form.serialize(), function (res) {
                    if (res.code === 1) {
                        layer.msg(res.msg || '操作成功', {icon: 1});
                        setTimeout(function () {
                            location.reload();
                        }, 800);
                    } else {
                        layer.msg(res.msg || '操作失败', {icon: 2});
                    }
                }, 'json');
            });
            return false;
        });
    });
</script>
