<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title a m-cf">店员列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="sm:w-full md:w-1/4">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <?php if (checkPrivilege('shop.clerk/add')): ?>
                                        <div class="btn-group btn-group-xs">
                                            <a class="btn btn-default btn-success"
                                               href="<?= url('shop.clerk/add') ?>">
                                                <span class="icon-plus"></span> 新增
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="sm:w-full md:w-3/4">
                                        <select name="shop_id" class="form-select">
                                            <option value=""></option>
                                            <?php if (isset($shopList)): foreach ($shopList as $shop): ?>
                                                <option value="<?= $shop['shop_id'] ?>"
                                                    <?= $request->get('shop_id') == $shop['shop_id'] ? 'selected' : '' ?>>
                                                    <?= $shop['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入店员姓名/手机号"
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
                                <th>店员ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>所属门店</th>
                                <th>店员姓名</th>
                                <th>店员手机号</th>
                                <th>状态</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['clerk_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle"><?= $item['user']['nickName'] ?></td>
                                    <td class="align-middle"><?= $item['shop']['shop_name'] ?></td>
                                    <td class="align-middle"><?= $item['real_name'] ?></td>
                                    <td class="align-middle"><?= $item['mobile'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                                           <?= $item['status'] ? '启用' : '禁用' ?>
                                       </span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('shop.clerk/edit')): ?>
                                                <a href="<?= url('shop.clerk/edit', ['clerk_id' => $item['clerk_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('shop.clerk/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['clerk_id'] ?>">
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
        var url = "<?= url('shop.clerk/delete') ?>";
        $('.item-delete').delete('clerk_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

