<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title "> 秒杀商品列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="sm:w-full md:w-1/4">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="btn-toolbar">
                                        <?php if (checkPrivilege('apps.sharp.goods/add')): ?>
                                            <div class="btn-group btn-group-xs">
                                                <a class="btn btn-default btn-success rounded"
                                                   href="<?= url('apps.sharp.goods/add') ?>">
                                                    <span class="icon-plus"></span> 新增
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:w-full md:w-3/4">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入商品名称"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>秒杀商品ID</th>
                                <th width="20%">商品信息</th>
                                <th>限购数量</th>
                                <th>累积销量</th>
                                <th>库存总量</th>
                                <th>排序</th>
                                <th>状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['sharp_goods_id'] ?></td>
                                    <td class="goods-detail align-middle">
                                        <div class="goods-image">
                                            <img src="<?= $item['goods_image_id'] ? \app\common\model\BaseModel::getFileUrl($item['goods_image_id']) : '' ?>" alt="">
                                        </div>
                                        <div class="goods-info dis-flex flex-dir-column flex-x-center">
                                            <p class="goods-title"><?= $item['goods_name'] ?></p>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?= $item['limit_num'] == 0 ? '不限' : $item['limit_num'] ?></td>
                                    <td class="align-middle"><?= $item['total_sales'] ?></td>
                                    <td class="align-middle"><?= $item['seckill_stock'] ?></td>
                                    <td class="align-middle"><?= $item['sort'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                               <?= $item['status'] ? '上架' : '下架' ?></span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.sharp.goods/edit')): ?>
                                                <a href="<?= url('apps.sharp.goods/edit', ['sharp_goods_id' => $item['sharp_goods_id']]) ?>"
                                                   class="tpl-table-black-operation-default">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.sharp.goods/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['sharp_goods_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">暂无记录</td>
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
        var url = "<?= url('apps.sharp.goods/delete') ?>";
        $('.item-delete').delete('sharp_goods_id', url);

    });
</script>

