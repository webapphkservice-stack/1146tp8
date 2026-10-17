<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">文件分组</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('content.files.group/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('content.files.group/add') ?>">
                                            <span class="icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="sm:w-full">
                        <table width="100%" class="table-base   tpl-table-black">
                            <thead>
                            <tr>
                                <th>分组ID</th>
                                <th>分组名称</th>
                                <th>文件类型</th>
                                <th>分组排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $first): ?>
                                <tr>
                                    <td class="align-middle"><?= $first['group_id'] ?></td>
                                    <td class="align-middle"><?= $first['group_name'] ?></td>
                                    <td class="align-middle"><?= $first['group_type'] ?></td>
                                    <td class="align-middle"><?= $first['sort'] ?></td>
                                    <td class="align-middle"><?=  time_text($first['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.files.group/edit')): ?>
                                                <a href="<?= url('content.files.group/edit',
                                                    ['group_id' => $first['group_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('content.files.group/delete')): ?>
                                                <a href="javascript:void(0);" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['group_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">暂无记录</td>
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
        // 删除元素
        var url = "<?= url('content.files.group/delete') ?>";
        $('.item-delete').delete('group_id', url);

    });
</script>

