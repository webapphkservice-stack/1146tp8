<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">文章列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('content.article/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('content.article/add') ?>">
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
                                <th>文章ID</th>
                                <th>文章标题</th>
                                <th>封面图</th>
                                <th>文章分类</th>
                                <th>实际阅读量</th>
                                <th>文章排序</th>
                                <th>状态</th>
                                <th>添加时间</th>
                                <th>更新时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['article_id'] ?></td>
                                    <td class="align-middle">
                                        <p class="item-title"
                                           style="max-width: 400px;"><?= $item['article_title'] ?></p>
                                    </td>
                                    <td class="align-middle">
                                        <a href="<?= $item['image']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['image']['file_path'] ?>" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle"><?= $item['category']['name'] ?></td>
                                    <td class="align-middle"><?= $item['actual_views'] ?></td>
                                    <td class="align-middle"><?= $item['article_sort'] ?></td>
                                    <td class="align-middle">
                                           <span class="badge badge-<?= $item['article_status'] ? 'success' : 'warning' ?>">
                                               <?= $item['article_status'] ? '显示' : '隐藏' ?>
                                           </span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle"><?=  time_text($item['update_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.article/edit')): ?>
                                                <a href="<?= url('content.article/edit',
                                                    ['article_id' => $item['article_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('content.article/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['article_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">暂无记录</td>
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
        var url = "<?= url('content.article/delete') ?>";
        $('.item-delete').delete('article_id', url);

    });
</script>

