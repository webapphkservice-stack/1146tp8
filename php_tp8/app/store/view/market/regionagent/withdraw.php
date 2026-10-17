<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">代理商提现审核</div>
                </div>
                <div class="widget-body float-right">
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base   tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>用户</th>
                                <th>提现金额</th>
                                <th>税费</th>
                                <th>实际到账</th>
                                <th>提现方式</th>
                                <th>收款信息</th>
                                <th>申请时间</th>
                                <th>审核状态</th>
                                <th>打款状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <p><?= $item['nickName'] ?></p>
                                            <p>(用户ID：<?= $item['user_id'] ?>)</p>
                                        </td>
                                        <td><?= $item['money'] ?></td>
                                        <td><?= $item['tax_amount'] ?? '0.00' ?></td>
                                        <td><?= number_format($item['money'] - ($item['tax_amount'] ?? 0), 2) ?></td>
                                        <td>
                                            <?php if ($item['pay_type'] == 10): ?>
                                                <span>微信</span>
                                            <?php elseif ($item['pay_type'] == 30): ?>
                                                <span>银行卡</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['pay_type'] == 30): ?>
                                                <p>姓名：<?= $item['bank_name'] ?></p>
                                                <p>开户行：<?= $item['bank_account'] ?></p>
                                                <p>卡号：<?= $item['bank_card'] ?></p>
                                            <?php else: ?>
                                                <span>-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('Y-m-d H:i:s', $item['create_time']) ?></td>
                                        <td>
                                            <?php if ($item['apply_status'] == 10): ?>
                                                <span class="badge badge-warning">待审核</span>
                                            <?php elseif ($item['apply_status'] == 20): ?>
                                                <span class="badge badge-secondary">审核通过</span>
                                            <?php elseif ($item['apply_status'] == 40): ?>
                                                <span class="badge badge-danger">已驳回</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['pay_status'] == 10): ?>
                                                <span class="badge badge-warning">待打款</span>
                                            <?php elseif ($item['pay_status'] == 20): ?>
                                                <span class="badge badge-success">已打款</span>
                                            <?php else: ?>
                                                <span class="badge">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="tpl-table-black-operation">
                                                <?php if ($item['apply_status'] == 10): ?>
                                                    <a href="javascript:void(0);" class="j-audit" data-id="<?= $item['id'] ?>">
                                                        <i class="icon-pencil"></i> 审核
                                                    </a>
                                                <?php elseif ($item['apply_status'] == 20 && $item['pay_status'] == 10 && $item['pay_type'] == 10): ?>
                                                    <a href="javascript:void(0);" class="j-pay" data-id="<?= $item['id'] ?>" data-money="<?= $item['money'] ?>">
                                                        <i class="icon-money"></i> 微信打款
                                                    </a>
                                                <?php elseif ($item['apply_status'] == 20 && $item['pay_status'] == 10 && $item['pay_type'] == 30): ?>
                                                    <span class="text-blue-600">等待线下打款</span>
                                                <?php elseif ($item['pay_status'] == 20): ?>
                                                    <span class="text-green-600">已完成</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="lg:w-full ">
                        <div class="float-right"><?= $list->render() ?></div>
                        <div class="float-right pagination-total mr-4">
                            <div class="align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script id="tpl-withdraw-audit" type="text/template">
    <div class="pt-3">
        <form class="form-withdraw-audit tpl-form-line-form" method="post" action="<?= url('market.regionagent/withdraw_submit') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">审核状态</label>
                <div class="sm:w-3/4">
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply_status" value="20" checked> 审核通过
                    </label>
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply_status" value="40"> 驳回
                    </label>
                </div>
            </div>
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">驳回原因</label>
                <div class="sm:w-3/4">
                    <input type="text" class="tpl-form-input" name="reject_reason" placeholder="仅在驳回时填写">
                </div>
            </div>
        </form>
    </div>
</script>

<script id="tpl-withdraw-pay" type="text/template">
    <div class="pt-3">
        <div class="tpl-form-line-form">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">提现金额</label>
                <div class="sm:w-2/3 text-sm text-gray-700">
                    <span class="text-red-600 text-lg">￥{{ money }}</span>
                </div>
            </div>
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">打款说明</label>
                <div class="sm:w-2/3 text-sm text-gray-700 text-amber-600">
                    确定要通过微信企业付款到零钱吗？
                </div>
            </div>
        </div>
    </div>
</script>

<script>
    $(function () {
        // 审核按钮
        $('.j-audit').click(function () {
            var $this = $(this);
            layer.open({
                type: 1,
                title: '提现审核',
                area: '340px',
                offset: 'auto',
                anim: 1,
                closeBtn: 1,
                shade: 0.3,
                btn: ['确定', '取消'],
                content: template('tpl-withdraw-audit', $this.data()),
                success: function (layero) {
                },
                yes: function (index, layero) {
                    layero.find('.form-withdraw-audit').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            if (result.code === 1) {
                                $.show_success(result.msg, window.location.href);
                            } else {
                                $.show_error(result.msg);
                            }
                        }
                    });
                    layer.close(index);
                }
            });
        });

        // 微信打款按钮
        $('.j-pay').click(function () {
            var $this = $(this);
            layer.open({
                type: 1,
                title: '微信打款确认',
                area: '340px',
                offset: 'auto',
                anim: 1,
                closeBtn: 1,
                shade: 0.3,
                btn: ['确定打款', '取消'],
                content: template('tpl-withdraw-pay', $this.data()),
                yes: function (index, layero) {
                    $.ajax({
                        url: "<?= url('market.regionagent/withdraw_wechat_pay') ?>",
                        type: 'post',
                        dataType: 'json',
                        data: {id: $this.data('id')},
                        success: function (result) {
                            if (result.code === 1) {
                                $.show_success(result.msg, window.location.href);
                            } else {
                                $.show_error(result.msg);
                            }
                        }
                    });
                    layer.close(index);
                }
            });
        });
    });
</script>
