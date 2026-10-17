<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title "> 砍价记录</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入商品名称/用户昵称"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th width="10%">商品信息</th>
                                <th>用户信息</th>
                                <th>砍价底价</th>
                                <th>已砍金额</th>
                                <th>截止时间</th>
                                <th>是否购买</th>
                                <th>砍价状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['task_id'] ?></td>
                                    <td class="align-middle">
                                        <div class="goods-detail">
                                            <div class="goods-image">
                                                <img src="<?= $item['goods']['image'][0]['file_path'] ?>" alt="">
                                            </div>
                                            <div class="goods-info dis-flex flex-dir-column flex-x-center">
                                                <p class="goods-title"><?= $item['goods']['goods_name'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="user-detail ">
                                            <div class="user-avatar">
                                                <img src="<?= $item['user']['avatarUrl'] ?>" alt="">
                                            </div>
                                            <div class="detail-info dis-flex flex-dir-column flex-x-center">
                                                <p class="detail-info_item"><?= $item['user']['nickName'] ?></p>
                                                <p class="detail-info_item">(ID：<?= $item['user']['user_id'] ?>)</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle"><?= $item['floor_price'] ?></td>
                                    <td class="align-middle"><?= $item['cut_money'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['end_time'])  ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['is_buy'] ? 'success' : '' ?>">
                                               <?= $item['is_buy'] ? '已购买' : '未购买' ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['status'] ? 'secondary' : 'warning' ?>">
                                               <?= $item['status'] ? '砍价中' : '已结束' ?></span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.bargain.active/help')): ?>
                                                <a href="<?= url('apps.bargain.task/help', ['task_id' => $item['task_id']]) ?>"
                                                   class="tpl-table-black-operation-default">
                                                    查看砍价榜
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.bargain.active/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['task_id'] ?>">
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
        var url = "<?= url('apps.bargain.task/delete') ?>";
        $('.item-delete').delete('task_id', url);

    });
</script>

