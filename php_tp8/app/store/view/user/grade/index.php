<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">会员等级列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <div class="btn-group btn-group-xs">
                                    <a class="btn btn-default btn-success rounded"
                                       href="<?= url('user.grade/add') ?>">
                                        <span class="icon-plus"></span> 新增
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>等级ID</th>
                                <th>等级名称</th>
                                <th>等级权重</th>
                                <th>升级条件</th>
                                <th>等级权益</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['grade_id'] ?></td>
                                    <td class="align-middle"><?= $item['name'] ?></td>
                                    <td class="align-middle"><?= $item['weight'] ?></td>
                                    <td class="align-middle">
                                        <span>消费满<?= $item['upgrade']['expend_money'] ?>元</span>
                                    </td>
                                    <td class="align-middle">
                                        <span><?= $item['equity']['discount'] ?>折</span>
                                    </td>
                                    <td class="align-middle">
                                       <span class="badge badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                           <?= $item['status'] ? '启用' : '禁用' ?>
                                       </span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user.grade/edit')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('user.grade/edit', ['grade_id' => $item['grade_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user.grade/delete')): ?>
                                                <a class="j-delete tpl-table-black-operation-default"
                                                   href="javascript:void(0);" data-id="<?= $item['grade_id'] ?>">
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
        var url = "<?= url('user.grade/delete') ?>";
        $('.j-delete').delete('grade_id', url);

    });
</script>

