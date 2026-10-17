<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title a m-cf">管理员列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <?php if (checkPrivilege('store.user/add')): ?>
                                <div class="btn-group btn-group-xs">
                                    <a class="btn btn-default btn-success"
                                       href="<?= url('store.user/add') ?>">
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
                                <th>管理员ID</th>
                                <th>用户名</th>
                                <th>姓名</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['store_user_id'] ?></td>
                                    <td class="align-middle"><?= $item['user_name'] ?></td>
                                    <td class="align-middle"><?= $item['real_name'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if ($store['user']['store_user_id'] != $item['store_user_id']): ?>
                                                <?php if (checkPrivilege('store.user/edit')): ?>
                                                    <a href="<?= url('store.user/edit', ['user_id' => $item['store_user_id']]) ?>">
                                                        <i class="icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('store.user/delete')): ?>
                                                    <a href="javascript:void(0);"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['store_user_id'] ?>">
                                                        <i class="icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
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
        var url = "<?= url('store.user/delete') ?>";
        $('.item-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

