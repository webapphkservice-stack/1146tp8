<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">店铺管理</div>
                    <div class="widget function float-right">
                        <a href="<?= url('delivery/shopAdd') ?>" class="button">
                            <button type="button" class="btn btn-default btn-success rounded">
                                <span class="icon-plus"></span> 新增店铺
                            </button>
                        </a>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <!-- 搜索框 -->
                    <div class="overflow-x-auto sm:w-full">
                        <form class=" flex flex-wrap items-center gap-3" action="<?= url('delivery/shop') ?>" method="get">
                            <div class="spec-form-inline inline-flex items-center gap-1">
                                <input type="text" class="form-input" name="shop_name"
                                       placeholder="店名" value="<?= $request->get('shop_name') ?>">
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <button class="btn btn-default btn-radius" type="submit">搜索</button>
                            </div>
                        </form>
                    </div>
                    <div class="sm:w-full mt-4-xs">
                        <table width="100%" class="table-base   tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>店铺图片</th>
                                <th>店名</th>
                                <th>店铺地址</th>
                                <th>店铺WhatsApp</th>
                                <th>外送商品说明</th>
                                <th>排序</th>
                                <th>状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['image']) && !empty($item['image']['file_path'])): ?>
                                            <a href="<?= $item['image']['file_path'] ?>" target="_blank">
                                                <img src="<?= $item['image']['file_path'] ?>" width="50" height="50" style="border-radius:4px">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['shop_name'] ?></td>
                                    <td class="align-middle" style="max-width:240px"><?= $item['address'] ?: '—' ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['whatsapp']): ?>
                                            <a href="https://wa.me/<?= $item['whatsapp'] ?>" target="_blank"><?= $item['whatsapp'] ?></a>
                                        <?php else: ?>
                                            <span class="text-gray-500">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle" style="max-width:260px">
                                        <?= $item['description'] ? mb_substr($item['description'], 0, 40) : '—' ?>
                                    </td>
                                    <td class="align-middle"><?= $item['sort'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['status'] == 1): ?>
                                            <span class="badge badge-success">启用</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">禁用</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="<?= url('delivery/index', ['shop_id' => $item['shop_id']]) ?>">
                                                <i class="icon-list"></i> 外送商品
                                            </a>
                                            <a href="<?= url('delivery/shopEdit', ['shop_id' => $item['shop_id']]) ?>">
                                                <i class="icon-pencil"></i> 编辑
                                            </a>
                                            <a href="javascript:;" class="item-delete tpl-table-black-operation-del"
                                               data-id="<?= $item['shop_id'] ?>">
                                                <i class="icon-trash"></i> 删除
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">暂无店铺记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="lg:w-full ">
                            <div class="float-right"><?= $list->render() ?> </div>
                            <div class="float-right pagination-total mr-4">总记录：<?= $list->total() ?></div>
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
        $('.item-delete').delete('shop_id', "<?= url('delivery/shopDelete') ?>", '确定要删除该店铺吗？');
    });
</script>
