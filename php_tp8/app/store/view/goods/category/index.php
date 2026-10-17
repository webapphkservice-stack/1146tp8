<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">商品分类</div>
                </div>
                <div class="widget-body float-right">
                    <div class="tips mb-3 w-full">
                        <div class="pre">
                            <p> 注：商品分类最多添加2级，分类图片可参考
                                <a href="<?= url('wxapp.page/category') ?>" target="_blank">分类页模板</a> 上传</p>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 lg:w-1/2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="btn-toolbar">
                                <?php if (checkPrivilege('goods.category/add')): ?>
                                    <div class="btn-group btn-group-xs">
                                        <a class="btn btn-default btn-success rounded"
                                           href="<?= url('goods.category/add') ?>">
                                            <span class="icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="w-full">
                        <table width="100%" class="table-base   tpl-table-black ">
                            <thead>
                            <tr>
                                <th>分类ID</th>
                                <th>分类名称</th>
                                <th>分类排序</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($list)): foreach ($list as $first): ?>
                                <tr>
                                    <td class="align-middle"><?= $first['category_id'] ?></td>
                                    <td class="align-middle"><?= $first['name'] ?></td>
                                    <td class="align-middle"><?= $first['sort'] ?></td>
                                    <td class="align-middle"><?=  time_text($first['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('goods.category/edit')): ?>
                                                <a href="<?= url('goods.category/edit',
                                                    ['category_id' => $first['category_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('goods.category/delete')): ?>
                                                <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $first['category_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                    <tr>
                                        <td class="align-middle"><?= $two['category_id'] ?></td>
                                        <td class="align-middle">　-- <?= $two['name'] ?></td>
                                        <td class="align-middle"><?= $two['sort'] ?></td>
                                        <td class="align-middle"><?=  time_text($two['create_time'])  ?></td>
                                        <td class="align-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('goods.category/edit')): ?>
                                                    <a href="<?= url('goods.category/edit',
                                                        ['category_id' => $two['category_id']]) ?>">
                                                        <i class="icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('goods.category/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $two['category_id'] ?>">
                                                        <i class="icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                        <tr>
                                            <td class="align-middle"><?= $three['category_id'] ?></td>
                                            <td class="align-middle">　　　-- <?= $three['name'] ?></td>
                                            <td class="align-middle"><?=  time_text($three['create_time'])  ?></td>
                                            <td class="align-middle">
                                                <div class="tpl-table-black-operation">
                                                    <?php if (checkPrivilege('goods.category/edit')): ?>
                                                        <a href="<?= url('goods.category/edit',
                                                            ['category_id' => $three['category_id']]) ?>">
                                                            <i class="icon-pencil"></i> 编辑
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('goods.category/delete')): ?>
                                                        <a href="javascript:;"
                                                           class="item-delete tpl-table-black-operation-del"
                                                           data-id="<?= $three['category_id'] ?>">
                                                            <i class="icon-trash"></i> 删除
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                <?php endforeach; endif; ?>
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
        var url = "<?= url('goods.category/delete') ?>";
        $('.item-delete').delete('category_id', url);

    });
</script>

