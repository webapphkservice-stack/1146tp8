<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">每日任务设置</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <?php if (checkPrivilege('apps.dealer.task/add')): ?>
                                <a class="btn btn-primary rounded"
                                   href="<?= url('apps.dealer.task/add') ?>">
                                    <i class="icon-plus"></i> 新增任务
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full pb-6">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>任务图标</th>
                                <th>任务名称</th>
                                <th>任务类型</th>
                                <th>奖励积分</th>
                                <th>排序</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['task_id'] ?></td>
                                    <td class="align-middle">
                                        <?php if (!empty($item['task_icon'])): ?>
                                            <img src="<?= $item['task_icon'] ?>" width="40" height="40" alt="">
                                        <?php else: ?>
                                            <span>--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['task_name'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['task_type'] == 1): ?>
                                            <span class="badge">每日签到</span>
                                        <?php elseif ($item['task_type'] == 2): ?>
                                            <span class="badge badge-secondary">分享商品</span>
                                        <?php elseif ($item['task_type'] == 3): ?>
                                            <span class="badge badge-success">浏览商品</span>
                                        <?php elseif ($item['task_type'] == 4): ?>
                                            <span class="badge badge-warning">下单购买</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= intval($item['points']) ?> 积分</td>
                                    <td class="align-middle"><?= intval($item['sort']) ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['is_enable'] == 1) : ?>
                                            <span class="badge badge-success">启用</span>
                                        <?php else : ?>
                                            <span class="badge">禁用</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['create_time'] ? date('Y-m-d H:i:s', intval($item['create_time'])) : '--' ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.dealer.task/edit')): ?>
                                                <a href="<?= url('apps.dealer.task/edit', ['task_id' => $item['task_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.dealer.task/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['task_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        // 删除任务
        $('.item-delete').click(function () {
            var id = $(this).data('id');
            layer.confirm('确定要删除该任务吗？', function (index) {
                $.post('<?= url("apps.dealer.task/delete") ?>', {task_id: id}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });
    });
</script>
