<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">物流公司列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('setting.express/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('setting.express/add') ?>">
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
                                <th>物流公司ID</th>
                                <th>物流公司名称</th>
                                <th>物流公司代码</th>
                                <th>微信物流编码</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="align-middle"><?= $item['express_id'] ?></td>
                                        <td class="align-middle"><?= $item['express_name'] ?></td>
                                        <td class="align-middle"><?= $item['express_code'] ?></td>
                                        <td class="align-middle"><?= $item['wechat_code'] ?: '--' ?></td>
                                        <td class="align-middle"><?= $item['sort'] ?></td>
                                        <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                        <td class="align-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.express/edit')): ?>
                                                    <a href="<?= url('setting.express/edit',
                                                        ['express_id' => $item['express_id']]) ?>">
                                                        <i class="icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.express/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['express_id'] ?>">
                                                        <i class="icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">暂无记录</td>
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
        var url = "<?= url('setting.express/delete') ?>";
        $('.item-delete').delete('express_id', url);

    });
</script>

