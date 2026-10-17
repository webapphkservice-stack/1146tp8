<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">运费模板</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('setting.delivery/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('setting.delivery/add') ?>">
                                            <span class="icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="sm:w-full">
                        <table width="100%" class="table-base   tpl-table-black ">
                            <thead>
                            <tr>
                                <th>模板ID</th>
                                <th>模板名称</th>
                                <th>计费方式</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="align-middle"><?= $item['delivery_id'] ?></td>
                                        <td class="align-middle"><?= $item['name'] ?></td>
                                        <td class="align-middle"><?= $item['method']['text'] ?></td>
                                        <td class="align-middle"><?= $item['sort'] ?></td>
                                        <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                        <td class="align-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.delivery/edit')): ?>
                                                    <a href="<?= url('setting.delivery/edit',
                                                        ['delivery_id' => $item['delivery_id']]) ?>">
                                                        <i class="icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.delivery/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['delivery_id'] ?>">
                                                        <i class="icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">暂无记录</td>
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
        var url = "<?= url('setting.delivery/delete') ?>";
        $('.item-delete').delete('delivery_id', url);

    });
</script>

