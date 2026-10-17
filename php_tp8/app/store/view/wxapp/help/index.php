<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">帮助中心</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('wxapp.help/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('wxapp.help/add') ?>">
                                            <span class="icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>标题</th>
                                <th>内容</th>
                                <th>排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['title'] ?></td>
                                    <td class="align-middle">
                                        <p class="item-title" style="max-width: 400px;"><?= $item['content'] ?></p>
                                    </td>
                                    <td class="align-middle"><?= $item['sort'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('wxapp.help/edit')): ?>
                                                <a href="<?= url('wxapp.help/edit',
                                                    ['help_id' => $item['help_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('wxapp.help/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['help_id'] ?>">
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
        var url = "<?= url('wxapp.help/delete') ?>";
        $('.item-delete').delete('help_id', url);

    });
</script>

