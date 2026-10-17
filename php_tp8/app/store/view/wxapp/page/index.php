<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">页面设计</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('wxapp.page/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('wxapp.page/add') ?>">
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
                                <th>页面ID</th>
                                <th width="40%">页面名称</th>
                                <th>页面类型</th>
                                <th>添加时间</th>
                                <th>更新时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list

                                                                   as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['page_id'] ?></td>
                                    <td class="align-middle">
                                        <span><?= $item['page_name'] ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['page_type'] == 10) : ?>
                                            <span class="badge badge-warning">商城首页</span>
                                        <?php elseif ($item['page_type'] == 20) : ?>
                                            <span class="badge badge-secondary">自定义页</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle"><?=  time_text($item['update_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('wxapp.page/edit')): ?>
                                                <a href="<?= url('wxapp.page/edit', ['page_id' => $item['page_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($item['page_type'] == 20) : ?>
                                                <?php if (checkPrivilege('wxapp.page/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['page_id'] ?>">
                                                        <i class="icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('wxapp.page/sethome')): ?>
                                                    <a href="javascript:;"
                                                       class="j-setHome tpl-table-black-operation-green"
                                                       data-id="<?= $item['page_id'] ?>">
                                                        <i class="iconfont icon-home"></i> 设为首页
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
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        // 删除元素
        $('.item-delete').delete('page_id', "<?= url('wxapp.page/delete') ?>");

        // 设为首页
        $('.j-setHome').click(function () {
            var pageId = $(this).attr('data-id');
            layer.confirm('确定要将此页面设置为默认首页吗？', function (index) {
                $.post("<?= url('wxapp.page/sethome') ?>", {page_id: pageId}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

    });
</script>

