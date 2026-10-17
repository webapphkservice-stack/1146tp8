<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">申请成为分销商</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入昵称/姓名/手机号"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto sm:w-full pb-6">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>
                                    <p>姓名</p>
                                    <p>手机号</p>
                                </th>
                                <th>推荐人</th>
                                <th>审核状态</th>
                                <th>审核方式</th>
                                <th>申请时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['user_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>"
                                                 width="50" height="50" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <p><span><?= $item['nickName'] ?></span></p>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($item['real_name']) || !empty($item['mobile'])): ?>
                                            <p><?= $item['real_name'] ?: '--' ?></p>
                                            <p><?= $item['mobile'] ?: '--' ?></p>
                                        <?php else: ?>
                                            <p>--</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['referee_id'] > 0): ?>
                                            <p><?= $item['referee']['nickName'] ?></p>
                                            <p class="text-gray-500 f-12">(ID：<?= $item['referee']['user_id'] ?>)</p>
                                        <?php else: ?>
                                            <p>平台</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['apply_status'] == 10) : ?>
                                            <span class="badge">待审核</span>
                                        <?php elseif ($item['apply_status'] == 20) : ?>
                                            <span class="badge badge-secondary">已通过</span>
                                        <?php elseif ($item['apply_status'] == 30) : ?>
                                            <span class="badge badge-warning">已驳回</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['apply_type'] == 10) : ?>
                                            <span>后台审核</span>
                                        <?php elseif ($item['apply_type'] == 20) : ?>
                                            <span>无需审核</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['apply_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if ($item['apply_status'] == 10) : ?>
                                                <?php if (checkPrivilege('apps.dealer.apply/submit')): ?>
                                                    <a class="j-audit" data-id="<?= $item['apply_id'] ?>"
                                                       href="javascript:void(0);">
                                                        <i class="icon-pencil"></i> 审核
                                                    </a>
                                                <?php endif; ?>
                                            <?php elseif ($item['apply_status'] == 30) : ?>
                                                <a class="j-show-reason tpl-table-black-operation-green"
                                                   href="javascript:void(0);"
                                                   data-reason="<?= $item['reject_reason'] ?>">
                                                    驳回原因</a>
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

<!-- 分销商审核 -->
<script id="tpl-dealer-apply" type="text/template">
    <div class="pt-3">
        <form class="form-dealer-apply tpl-form-line-form" method="post"
              action="<?= url('apps.dealer.apply/submit') ?>">
            <input type="hidden" name="apply_id" value="{{ id }}">
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 审核状态 </label>
                <div class="sm:w-3/4">
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply[apply_status]" value="20"
                               checked> 审核通过
                    </label>
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply[apply_status]" value="30"> 驳回
                    </label>
                </div>
            </div>
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 驳回原因 </label>
                <div class="sm:w-3/4">
                    <input type="text" class="tpl-form-input" name="apply[reject_reason]" placeholder="仅在驳回时填写"
                           value="">
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 审核操作
         */
        $('.j-audit').click(function () {
            var $this = $(this);
            layer.open({
                type: 1
                , title: '分销商审核'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-dealer-apply', $this.data())
                , success: function (layero) {
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-dealer-apply').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });

        /**
         * 显示驳回原因
         */
        $('.j-show-reason').click(function () {
            var $this = $(this);
            layer.alert($this.data('reason'), {title: '驳回原因'});
        });

    });
</script>

