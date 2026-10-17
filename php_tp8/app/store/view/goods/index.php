<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">出售中的商品</div>
                </div>
                <div class="widget-body float-right">
                    <div class="page_toolbar mb-1">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <?php if (checkPrivilege('goods/add')): ?>
                                <a class="btn btn-default btn-success"
                                   href="<?= url('goods/add') ?>">
                                    <span class="icon-plus"></span> 新增
                                </a>
                            <?php endif; ?>
                            <?php $category_id = $request->get('category_id') ?: null; ?>
                            <select name="category_id" class="form-select">
                                <option value="">全部分类</option>
                                <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                    <option value="<?= $first['category_id'] ?>"
                                        <?= $category_id == $first['category_id'] ? 'selected' : '' ?>>
                                        <?= $first['name'] ?></option>
                                    <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                        <option value="<?= $two['category_id'] ?>"
                                            <?= $category_id == $two['category_id'] ? 'selected' : '' ?>>
                                            　　<?= $two['name'] ?></option>
                                    <?php endforeach; endif; ?>
                                <?php endforeach; endif; ?>
                            </select>
                            <?php $status = $request->get('status') ?: null; ?>
                            <select name="status" class="form-select">
                                <option value="">全部状态</option>
                                <option value="10"
                                    <?= $status == 10 ? 'selected' : '' ?>>上架
                                </option>
                                <option value="20"
                                    <?= $status == 20 ? 'selected' : '' ?>>下架
                                </option>
                            </select>
                            <div class="input-group input-group-sm tpl-form-border-form">
                                <input type="text" class="form-input" name="search"
                                       placeholder="请输入商品名称"
                                       value="<?= $request->get('search') ?>">
                                <div class="input-group-btn">
                                    <button class="btn btn-default icon-search"
                                            type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table width="100%" class="table-base tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>商品ID</th>
                                <th>商品图片</th>
                                <th>商品名称</th>
                                <th>商品分类</th>
                                <th>实际销量</th>
                                <th>商品排序</th>
                                <th>商品状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['goods_id'] ?></td>
                                    <td class="align-middle">
                                        <?php if (!empty($item['image'][0])): ?>
                                            <a href="<?= $item['image'][0]['file_path'] ?>"
                                               title="点击查看大图" target="_blank">
                                                <img src="<?= $item['image'][0]['file_path'] ?>"
                                                     width="50" height="50" alt="商品图">
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= htmlspecialchars($item['goods_name']) ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($item['category']['name'] ?? '') ?></td>
                                    <td class="align-middle"><?= (int)$item['sales_actual'] ?></td>
                                    <td class="align-middle"><?= (int)$item['goods_sort'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['goods_status']['value'] == 10 ? 'success' : 'warning' ?>">
                                            <?= $item['goods_status']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="align-middle"><?= time_text($item['create_time']) ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('goods/edit')): ?>
                                                <a href="<?= url('goods/edit', ['goods_id' => $item['goods_id']]) ?>">
                                                    编辑
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= url('goods/copy', ['goods_id' => $item['goods_id']]) ?>">
                                                一键复制
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-8">暂无商品</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($list->lastPage() > 1): ?>
                        <div class="flex items-center justify-between mt-3 px-2">
                            <div class="pagination-total text-muted text-sm">
                                共 <?= $list->total() ?> 条
                            </div>
                            <?= $list->render() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
