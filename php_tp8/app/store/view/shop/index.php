<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title a m-cf">门店列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="sm:w-full md:w-1/4">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <?php if (checkPrivilege('shop/add')): ?>
                                        <div class="btn-group btn-group-xs">
                                            <a class="btn btn-default btn-success"
                                               href="<?= url('shop/add') ?>">
                                                <span class="icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="sm:w-full md:w-3/4">
                                        <?php $isCheck = $request->get('is_check'); ?>
                                        <select name="is_check" class="form-select">
                                            <option value=""></option>
                                            <option value="-1" <?= $isCheck === '-1' ? 'selected' : '' ?>>全部</option>
                                            <option value="1" <?= $isCheck === '1' ? 'selected' : '' ?>>支持</option>
                                            <option value="0"<?= $isCheck === '0' ? 'selected' : '' ?>>不支持</option>
                                        </select>
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入门店名称/联系人/电话"
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
                                <th>门店ID</th>
                                <th>门店名称</th>
                                <th>门店logo</th>
                                <th>营业时间</th>
                                <th>联系人</th>
                                <th>联系电话</th>
                                <th>门店地址</th>
                                <th>自提核销</th>
                                <th>门店状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['shop_id'] ?></td>
                                    <td class="align-middle"><?= $item['shop_name'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['logo']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['logo']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle"><?= $item['shop_hours'] ?></td>
                                    <td class="align-middle"><?= $item['linkman'] ?></td>
                                    <td class="align-middle"><?= $item['phone'] ?></td>
                                    <td class="align-middle">
                                        <?= $item['region']['province'] ?>  <?= $item['region']['city'] ?>  <?= $item['region']['region'] ?>
                                        <?= $item['address'] ?>
                                    </td>
                                    <td class="align-middle">
                                            <span class="badge badge-<?= $item['is_check'] ? 'success' : 'warning' ?>">
                                               <?= $item['is_check'] ? '支持' : '不支持' ?>
                                           </span>
                                    </td>
                                    <td class="align-middle">
                                            <span class="badge badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                               <?= $item['status'] ? '启用' : '禁用' ?>
                                           </span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('shop/edit')): ?>
                                                <a href="<?= url('shop/edit', ['shop_id' => $item['shop_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= url('shop/delivery', ['shop_id' => $item['shop_id']]) ?>">
                                                <i class="icon-truck"></i> 配送
                                            </a>
                                            <?php if (checkPrivilege('shop/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['shop_id'] ?>">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">暂无记录</td>
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
        var url = "<?= url('shop/delete') ?>";
        $('.item-delete').delete('shop_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

