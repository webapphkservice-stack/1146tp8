<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">积分明细</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4-xl ">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                <div class="tpl-form-border-form ">
                                    <input type="text" name="start_time"
                                           class="form-input"
                                           value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期">
                                <div class="tpl-form-border-form ">
                                    <input type="text" name="end_time"
                                           class="form-input"
                                           value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期">
                                </div>
                                    <div class="input-group input-group-sm tpl-form-border-form">
                                        <input type="text" class="form-input" name="search" placeholder="请输入用户昵称"
                                               value="<?= $request->get('search') ?>">
                                        <div class="input-group-btn">
                                            <button class="btn btn-default icon-search" type="submit"></button>
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
                                <th>ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>变动数量</th>
                                <th>描述/说明</th>
                                <th>管理员备注</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['log_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <p class=""><?= $item['user']['nickName'] ?></p>
                                        <p class="text-gray-500">(用户ID：<?= $item['user']['user_id'] ?>)</p>
                                    </td>
                                    <td class="align-middle">
                                        <?= $item['value'] > 0 ? '+' : '' ?><?= $item['value'] ?>
                                    </td>
                                    <td class="align-middle"><?= $item['describe'] ?: '--' ?></td>
                                    <td class="align-middle"><?= $item['remark'] ?: '--' ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
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

    });
</script>
