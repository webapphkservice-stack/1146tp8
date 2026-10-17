<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">活跃用户列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="tips mb-4 sm:w-full">
                        <div class="pre">
                            <p> 注：仅展示7天内在小程序中活跃（浏览点击）的用户。</p>
                            <p> 注：formId有效期是7天，可用于向用户发送模板消息。</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>用户头像</th>
                                <th>用户昵称</th>
                                <th>formid数量</th>
                                <th>注册时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['user_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle"><?= $item['user']['nickName'] ?></td>
                                    <td class="align-middle"><?= $item['total_formid'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['user']['create_time'])  ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">暂无活跃用户</td>
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

