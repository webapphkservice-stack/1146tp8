<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title "> 砍价助力榜</div>
                </div>
                <div class="widget-body float-right">
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户头像</th>
                                <th>用户昵称</th>
                                <th>砍掉的金额</th>
                                <th>操作时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['help_id'] ?></td>
                                    <td class="align-middle">
                                        <img src="<?= $item['user']['avatarUrl'] ?>" width="72" alt="">
                                    </td>
                                    <td class="align-middle">
                                        <span><?= $item['user']['nickName'] ?></span>
                                        <?php if ($item['is_creater']): ?>
                                            <span class="badge badge-success">发起人</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">-<?= $item['cut_money'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">暂无记录</td>
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

