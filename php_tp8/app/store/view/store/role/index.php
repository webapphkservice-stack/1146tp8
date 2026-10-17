<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title a m-cf">角色列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <?php if (checkPrivilege('store.role/add')): ?>
                                <div class="btn-group btn-group-xs">
                                    <a class="btn btn-default btn-success"
                                       href="<?= url('store.role/add') ?>">
                                        <span class="icon-plus"></span> 新增
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>角色ID</th>
                                <th>角色名称</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['role_id'] ?></td>
                                    <td class="align-middle"><?= $item['role_name_h1'] ?></td>
                                    <td class="align-middle"><?= $item['sort'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('store.role/edit')): ?>
                                                <a href="<?= url('store.role/edit', ['role_id' => $item['role_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('store.role/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['role_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">暂无记录</td>
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
        var url = "<?= url('store.role/delete') ?>";
        $('.item-delete').delete('role_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

