<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">分销商提现申请</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-2">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="user_id" value="<?= $request->get('user_id') ?>">
                            <select name="apply_status" class="form-select">
                                <option value="">状态筛选</option>
                                <option value="-1" <?= $request->get('apply_status') === '-1' ? 'selected' : '' ?>>全部</option>
                                <option value="10" <?= $request->get('apply_status') == '10' ? 'selected' : '' ?>>待审核</option>
                                <option value="20" <?= $request->get('apply_status') == '20' ? 'selected' : '' ?>>审核通过</option>
                                <option value="40" <?= $request->get('apply_status') == '40' ? 'selected' : '' ?>>已打款</option>
                                <option value="30" <?= $request->get('apply_status') == '30' ? 'selected' : '' ?>>驳回</option>
                            </select>
                            <select name="pay_type" class="form-select">
                                <option value="">打款方式</option>
                                <option value="-1" <?= $request->get('pay_type') == '-1' ? 'selected' : '' ?>>全部</option>
                                <option value="20" <?= $request->get('pay_type') == '20' ? 'selected' : '' ?>>支付宝</option>
                                <option value="30" <?= $request->get('pay_type') == '30' ? 'selected' : '' ?>>银行卡</option>
                            </select>
                            <div class="input-group input-group-sm tpl-form-border-form">
                                <input type="text" class="form-input" name="search"
                                       placeholder="昵称/姓名/手机号"
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
                                <th>提现金额</th>
                                <th>打款方式</th>
                                <th>提现信息</th>
                                <th class="text-center">审核状态</th>
                                <th>申请时间</th>
                                <th>审核时间</th>
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
                                        <p><span><?= $item['money'] ?></span></p>
                                    </td>
                                    <td class="align-middle">
                                        <p><span><?= $item['pay_type']['text'] ?></span></p>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['pay_type']['value'] == 20) : ?>
                                            <p><span><?= $item['alipay_name'] ?></span></p>
                                            <p><span><?= $item['alipay_account'] ?></span></p>
                                        <?php elseif ($item['pay_type']['value'] == 30) : ?>
                                            <p><span><?= $item['bank_name'] ?></span></p>
                                            <p><span><?= $item['bank_account'] ?></span></p>
                                            <p><span><?= $item['bank_card'] ?></span></p>
                                        <?php else : ?>
                                            <p><span>--</span></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-center">
                                        <?php if ($item['apply_status'] == 10) : ?>
                                            <span class="badge">待审核</span>
                                        <?php elseif ($item['apply_status'] == 20) : ?>
                                            <span class="badge badge-secondary">审核通过</span>
                                        <?php elseif ($item['apply_status'] == 30) : ?>
                                            <p><span class="badge badge-warning">已驳回</span></p>
                                            <span class="f-12">
                                                <a class="j-show-reason" href="javascript:void(0);"
                                                   data-reason="<?= $item['reject_reason'] ?>">
                                                    查看原因</a>
                                            </span>
                                        <?php elseif ($item['apply_status'] == 40) : ?>
                                            <span class="badge badge-success">已打款</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle"><?= $item['audit_time'] ?: '--' ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (in_array($item['apply_status'], [10, 20])) : ?>
                                                <?php if (checkPrivilege('apps.dealer.withdraw/submit')): ?>
                                                    <a class="j-audit" data-id="<?= $item['id'] ?>"
                                                       href="javascript:void(0);">
                                                        <i class="icon-pencil"></i> 审核
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($item['apply_status'] == 20) : ?>
                                                    <?php if (checkPrivilege('apps.dealer.withdraw/money')): ?>
                                                        <a class="j-money tpl-table-black-operation-del"
                                                           data-id="<?= $item['id'] ?>" href="javascript:void(0);">确认打款
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if ($item['apply_status'] == 20 && $item['pay_type']['value'] == 10) : ?>
                                                    <?php if (checkPrivilege('apps.dealer.withdraw/wechat_pay_v3')): ?>
                                                        <a class="j-wechat-pay-v3 tpl-table-black-operation-green"
                                                           data-id="<?= $item['id'] ?>" href="javascript:void(0);">微信转账V3
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($item['wx_out_bill_no'])): ?>
                                                    <p class="f-12 mt-4-xs">
                                                        <span class="badge badge-primary">V3</span>
                                                        <span class="text-gray-500"><?= WxTransferV3::getStateText($item['wx_transfer_state']) ?></span>
                                                        <a class="j-query-transfer text-blue-600" data-id="<?= $item['id'] ?>" href="javascript:void(0);">查询</a>
                                                    </p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (in_array($item['apply_status'], [30, 40])) : ?>
                                                <span>---</span>
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

<!-- 提现审核 -->
<script id="tpl-dealer-withdraw" type="text/template">
    <div class="pt-3">
        <form class="form-dealer-withdraw tpl-form-line-form" method="post"
              action="<?= url('apps.dealer.withdraw/submit') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 审核状态 </label>
                <div class="sm:w-3/4">
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="withdraw[apply_status]" value="20"
                               checked> 审核通过
                    </label>
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="withdraw[apply_status]" value="30"> 驳回
                    </label>
                </div>
            </div>
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 驳回原因 </label>
                <div class="sm:w-3/4">
                    <input type="text" class="tpl-form-input" name="withdraw[reject_reason]" placeholder="仅在驳回时填写"
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
                , title: '提现审核'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-dealer-withdraw', $this.data())
                , success: function (layero) {
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-dealer-withdraw').ajaxSubmit({
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

        /**
         * 确认打款
         */
        $('.j-money').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('apps.dealer.withdraw/money') ?>";
            layer.confirm('确定已打款吗？', {title: '友情提示'}, function (index) {
                $.post(url, {id: id}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        /**
         * 微信付款（V2版本，已废弃）
         */
        $('.j-wechat-pay').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('apps.dealer.withdraw/wechat_pay') ?>";
            layer.confirm('该操作 将使用微信支付企业付款到零钱功能，确定打款吗？', {title: '友情提示'}, function (index) {
                $.post(url, {id: id}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        /**
         * 微信转账V3（商家转账到零钱）
         */
        $('.j-wechat-pay-v3').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('apps.dealer.withdraw/wechat_pay_v3') ?>";
            layer.confirm('该操作将使用微信支付V3商家转账到零钱功能，确定打款吗？', {
                title: '友情提示',
                btn: ['确定', '取消']
            }, function (index) {
                layer.load(2);
                $.post(url, {id: id}, function (result) {
                    layer.closeAll('loading');
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        /**
         * 查询微信转账状态
         */
        $('.j-query-transfer').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('apps.dealer.withdraw/query_transfer') ?>";
            layer.load(2);
            $.post(url, {id: id}, function (result) {
                layer.closeAll('loading');
                if (result.code === 1) {
                    layer.alert('转账状态：' + result.data.state_text + '<br>微信单号：' + result.data.data.bill_id, {
                        title: '查询结果',
                        btn: ['确定']
                    });
                } else {
                    $.show_error(result.msg);
                }
            });
        });

    });
</script>

