<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">下级用户列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="overflow-x-auto sm:w-full pt-4">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>性别</th>
                                <th>累积消费金额</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['user']['user_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>"
                                                 width="50" height="50" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <p><span><?= $item['user']['nickName'] ?></span></p>
                                    </td>
                                    <td class="align-middle"><?= $item['user']['gender'] ?></td>
                                    <td class="align-middle"><?= $item['user']['expend_money'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
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
</div>
<script>
    $(function () {

    });
</script>

