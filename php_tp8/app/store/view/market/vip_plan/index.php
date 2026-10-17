<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">VIP会员套餐列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('market.vip_plan/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('market.vip_plan/add') ?>">
                                            <span class="icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="sm:w-full overflow-x-auto">
                        <table width="100%"
                               class="table-base   tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>套餐ID</th>
                                <th>套餐名称</th>
                                <th>售价(元)</th>
                                <th>有效期(天)</th>
                                <th>是否启用</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['plan_id'] ?></td>
                                    <td class="align-middle"><?= $item['plan_name'] ?></td>
                                    <td class="align-middle"><?= $item['price'] ?></td>
                                    <td class="align-middle"><?= $item['days'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge <?= $item['is_enable'] ? 'badge-success' : 'badge-secondary' ?>">
                                            <?= $item['is_enable'] ? '启用' : '禁用' ?>
                                        </span>
                                    </td>
                                    <td class="align-middle"><?= $item['sort'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('market.vip_plan/edit')): ?>
                                                <a href="<?= url('market.vip_plan/edit', ['plan_id' => $item['plan_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('market.vip_plan/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['plan_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="lg:w-full ">
                        <div class="float-right"><?= $list->render() ?> </div>
                        <div class="float-right pagination-total mr-4">
                            <div class="align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        var url = "<?= url('market.vip_plan/delete') ?>";
        $('.item-delete').delete('plan_id', url);

    });
</script>
