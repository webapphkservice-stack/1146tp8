<?php

use app\common\enum\live\LiveStatus as LiveStatusEnum;

?>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title "> 小程序直播间列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="tips mb-4 sm:w-full">
                        <div class="pre">
                            <p class="pb-4-xs"> 小程序直播操作说明：</p>
                            <p>1. 登录 <a href="https://mp.weixin.qq.com/" target="_blank">微信小程序运营平台</a>，点击左侧菜单栏 “直播”，点击
                                “创建直播间” 按钮。</p>
                            <p>2. 点击本页面中的 "同步直播间" 按钮，将直播间列表导入商城系统中。</p>
                        </div>
                    </div>
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="sm:w-full md:w-1/4">
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="btn-toolbar">
                                        <?php if (checkPrivilege('apps.live.room/refresh')): ?>
                                            <div class="btn-group btn-group-xs">
                                                <a class="j-refresh btn btn-default btn-success rounded"
                                                   href="javascript:void(0);">
                                                    <span class="icon-refresh"></span> 同步直播间
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:w-full md:w-3/4">
                                    <div class="form-group ">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入直播间名称/主播昵称"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                        </div>
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
                                <th>直播间ID</th>
                                <th>直播间名称</th>
                                <th>主播昵称</th>
                                <th>直播时间</th>
                                <th>直播状态</th>
                                <th>置顶</th>
                                <th>更新时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['room_id'] ?></td>
                                    <td class="align-middle"><?= $item['room_name'] ?></td>
                                    <td class="align-middle"><?= $item['anchor_name'] ?></td>
                                    <td class="align-middle">
                                        <p class="">
                                            <span class="title">开始：</span>
                                            <span class="value"><?=  time_text($item['start_time'])  ?></span>
                                        </p>
                                        <p class="">
                                            <span class="title">结束：</span>
                                            <span class="value"><?=  time_text($item['end_time'])  ?></span>
                                        </p>
                                    </td>
                                    <td class="align-middle">
                                        <?= LiveStatusEnum::data()[$item['live_status']]['name'] ?>
                                    </td>
                                    <td class="align-middle">
                                        <span class="j-setTop x-cur-p badge badge-<?= $item['is_top'] ? 'success' : 'warning' ?>"
                                              data-id="<?= $item['id'] ?>" data-istop="<?= $item['is_top'] ?>">
                                               <?= $item['is_top'] ? '是' : '否' ?></span>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['update_time'])  ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">暂无记录</td>
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

        /**
         * 同步直播间
         */
        $('.j-refresh').on('click', function () {
            var url = "<?= url('apps.live.room/refresh') ?>";
            var load = layer.load();
            $.post(url, {}, function (result) {
                result.code === 1 ? $.show_success(result.msg, result.url)
                    : $.show_error(result.msg);
                layer.close(load);
            });
        });

        // 商品状态
        $('.j-setTop').click(function () {
            var data = $(this).data();
            layer.confirm('确定要' + (parseInt(data['istop']) === 1 ? '取消' : '') + '置顶该房间吗？'
                , {title: '友情提示'}
                , function (index) {
                    var url = "<?= url('apps.live.room/settop') ?>";
                    $.post(url
                        , {id: data['id'], is_top: Number(!(parseInt(data['istop']) === 1))}
                        , function (result) {
                            result['code'] === 1 ? $.show_success(result['msg'], result['url'])
                                : $.show_error(result['msg']);
                        });
                    layer.close(index);
                });
        });

    });
</script>

