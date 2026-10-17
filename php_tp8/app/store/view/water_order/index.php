<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">送水订单（门店配送）</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 新订单提醒：门店端无站内消息，以列表顶部提示的方式提醒及时接单 -->
                    <?php if (!empty($waitAcceptCount)): ?>
                        <div class="bg-blue-50 text-blue-700 border border-blue-200 rounded px-4 py-3 mb-4 bg-red-50 text-red-700 border border-red-200 rounded px-4 py-3 mb-4" data-bg-blue-50 text-blue-700 border border-blue-200 rounded px-4 py-3 mb-4>
                            您有 <b><?= (int)$waitAcceptCount ?></b> 笔送水订单待接单，请尽快安排配送。
                        </div>
                    <?php endif; ?>
                    <!-- 筛选 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/store/water_order/index">
                                <div class="form-group ">
                                    <select name="delivery_shop_id" class="form-select">
                                        <option value="0">全部门店</option>
                                        <?php foreach ($shopList as $shop): ?>
                                            <option value="<?= $shop['shop_id'] ?>"
                                                <?= $request->get('delivery_shop_id') == $shop['shop_id'] ? 'selected' : '' ?>>
                                                <?= $shop['shop_name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <div class="form-group ">
                                    <?php $shopStatus = $request->get('shop_order_status'); ?>
                                    <select name="shop_order_status" class="form-select">
                                        <option value="0" <?= empty($shopStatus) ? 'selected' : '' ?>>全部状态</option>
                                        <option value="10" <?= $shopStatus == 10 ? 'selected' : '' ?>>待接单</option>
                                        <option value="20" <?= $shopStatus == 20 ? 'selected' : '' ?>>配送中</option>
                                        <option value="30" <?= $shopStatus == 30 ? 'selected' : '' ?>>已完成</option>
                                        <option value="40" <?= $shopStatus == 40 ? 'selected' : '' ?>>已取消</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <div class="input-group input-group-sm tpl-form-border-form">
                                        <input type="text" class="form-input" name="search"
                                               placeholder="订单号/用户昵称" value="<?= $request->get('search') ?>">
                                        <div class="input-group-btn">
                                            <button class="btn btn-default icon-search"
                                                    type="submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="sm:w-full">
                        <table width="100%"
                               class="table-base text-center whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>用户</th>
                                <th>派送门店</th>
                                <th>商品</th>
                                <th>商品金额</th>
                                <th>配送费</th>
                                <th>押桶押金</th>
                                <th>实付金额</th>
                                <th>配送状态</th>
                                <th>配送员</th>
                                <th>空桶归还</th>
                                <th>收货信息</th>
                                <th>下单时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $order): ?>
                                <?php
                                // 订单桶数（仅统计"按桶计押金"的商品件数，与收押金口径一致）
                                $bucketNum = 0;
                                foreach ($order['goods'] as $g) {
                                    if (isset($g['is_deposit_payable']) && (int)$g['is_deposit_payable'] != 1) {
                                        continue;
                                    }
                                    $bucketNum += (int)$g['total_num'];
                                }
                                $returnedNum = (int)$order['empty_bucket_num'];
                                // 是否处于"用户申请取消"中(预计算，模板内只用最简单的 if 判断)
                                $isApplyCancel = ((int)$order['order_status'] === 21);
                                // 是否显示商家主动取消按钮
                                $canShopCancel = (in_array((int)$order['shop_order_status'], [10, 20]) && !$isApplyCancel);
                                ?>
                                <tr>
                                    <td class="align-middle"><?= $order['order_no'] ?></td>
                                    <td class="align-middle"><?= $order['user']['nickName'] ?: '游客' ?></td>
                                    <td class="align-middle"><?= $order['delivery_shop_name'] ?: '-' ?></td>
                                    <td class="align-middle">
                                        <?php foreach ($order['goods'] as $goods): ?>
                                            <?= $goods['goods_name'] ?> × <?= $goods['total_num'] ?><br>
                                        <?php endforeach; ?>
                                    </td>
                                    <td class="align-middle">￥<?= $order['order_price'] ?></td>
                                    <td class="align-middle">￥<?= $order['express_price'] ?></td>
                                    <td class="align-middle">
                                        ￥<?php echo $order['deposit_amount']; ?>
                                        <?php if ($order['deposit_amount'] > 0): ?>
                                            <br>
                                            <?php if ($order['deposit_refund_status'] == 1): ?>
                                                <span class="badge badge-success">已退</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">未退</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">￥<?= $order['pay_price'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($order['shop_order_status'] == 10): ?>
                                            <span class="badge badge-warning">待接单</span>
                                        <?php elseif ($order['shop_order_status'] == 20): ?>
                                            <span class="badge badge-primary">配送中</span>
                                        <?php elseif ($order['shop_order_status'] == 30): ?>
                                            <span class="badge badge-success">已完成</span>
                                        <?php else: ?>
                                            <span class="badge">已取消</span>
                                        <?php endif; ?>
                                        <?php if ($isApplyCancel): ?>
                                            <br>
                                            <span class="badge badge-danger">用户申请取消</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($order['deliveryClerk'])): ?>
                                            <span class="badge badge-secondary">
                                                <?= $order['deliveryClerk']['real_name'] ?></span>
                                        <?php elseif (!empty($order['delivery_clerk_id'])): ?>
                                            <span class="badge badge-secondary">已指派
                                                #<?= $order['delivery_clerk_id'] ?></span>
                                        <?php else: ?>
                                            <span class="badge">未指派</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($order['deposit_amount'] > 0): ?>
                                            <?php if ($returnedNum >= $bucketNum && $bucketNum > 0): ?>
                                                <span class="badge badge-success">
                                                    <?= $returnedNum ?>/<?= $bucketNum ?> 已还完</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">
                                                    <?= $returnedNum ?>/<?= $bucketNum ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($order['address'])): ?>
                                            <?= $order['address']['name'] ?> <?= $order['address']['phone'] ?><br>
                                            <small><?= $order['address']['detail'] ?></small>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= date('Y-m-d H:i', $order['create_time']) ?></td>
                                    <td class="align-middle">
                                        <?php if ($isApplyCancel): ?>
                                            <button class="btn btn-xs btn-danger btn-audit-cancel"
                                                    data-id="<?= $order['order_id'] ?>">审核取消
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['shop_order_status'] == 10): ?>
                                            <button class="btn btn-xs btn-secondary btn-accept"
                                                    data-id="<?= $order['order_id'] ?>"
                                                    data-shop-id="<?= $order['delivery_shop_id'] ?>">接单
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['shop_order_status'] == 20): ?>
                                            <button class="btn btn-xs btn-success btn-finish"
                                                    data-id="<?= $order['order_id'] ?>">完成
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canShopCancel): ?>
                                            <button class="btn btn-xs btn-danger btn-cancel"
                                                    data-id="<?= $order['order_id'] ?>">取消
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['deposit_amount'] > 0): ?>
                                            <button class="btn btn-xs btn-default btn-recycle"
                                                    data-id="<?= $order['order_id'] ?>"
                                                    data-num="<?= $returnedNum ?>"
                                                    data-max="<?= $bucketNum ?>">登记空桶
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['deposit_amount'] > 0 && $order['deposit_refund_status'] != 1): ?>
                                            <button class="btn btn-xs btn-warning btn-refund-deposit"
                                                    data-id="<?= $order['order_id'] ?>">退押金
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="14" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="sm:w-full flex justify-center">
                            <?= $list->render() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        // 接单：先拉取该门店配送员，选择后提交（可不选直接接单）
        $('.btn-accept').on('click', function () {
            var id = $(this).data('id'), shopId = $(this).data('shop-id');
            $.post("<?= url('water_order/clerks') ?>", {shop_id: shopId}, function (res) {
                var clerks = (res.code === 1 && res.data.list) ? res.data.list : [];
                var html = '<div style="padding:15px;">';
                html += '<p style="margin-bottom:10px;">选择配送员（可不选）：</p>';
                html += '<select id="sel-clerk" class="form-input">';
                html += '<option value="0">暂不指派</option>';
                for (var i = 0; i < clerks.length; i++) {
                    html += '<option value="' + clerks[i].clerk_id + '">' + (clerks[i].real_name || ('#' + clerks[i].clerk_id)) + '</option>';
                }
                html += '</select></div>';
                layer.open({
                    type: 1,
                    title: '接单并指派配送员',
                    area: ['360px', '220px'],
                    content: html,
                    btn: ['确定', '取消'],
                    yes: function (index) {
                        var clerkId = $('#sel-clerk').val() || 0;
                        layer.close(index);
                        $.post("<?= url('water_order/accept') ?>", {
                            order_id: id,
                            clerk_id: clerkId
                        }, function (r) {
                            r.code === 1 ? (layer.msg(r.msg), setTimeout(function () {
                                location.reload();
                            }, 800)) : layer.msg(r.msg);
                        });
                    }
                });
            });
        });

        // 完成配送
        $('.btn-finish').on('click', function () {
            var id = $(this).data('id');
            $.post("<?= url('water_order/finish') ?>", {order_id: id}, function (res) {
                res.code === 1 ? (layer.msg(res.msg), setTimeout(function () {
                    location.reload();
                }, 800)) : layer.msg(res.msg);
            });
        });

        // 取消配送
        $('.btn-cancel').on('click', function () {
            var id = $(this).data('id');
            layer.confirm('确定取消该配送订单？', function () {
                $.post("<?= url('water_order/cancel') ?>", {order_id: id}, function (res) {
                    res.code === 1 ? (layer.msg(res.msg), setTimeout(function () {
                        location.reload();
                    }, 800)) : layer.msg(res.msg);
                });
            });
        });

        // 审核用户的取消申请：同意=退款并取消，拒绝=订单继续配送
        $('.btn-audit-cancel').on('click', function () {
            var id = $(this).data('id');
            layer.confirm('用户申请取消该订单，是否同意？<br>同意将原路退回已付款金额（含押桶押金）。', {
                btn: ['同意并退款', '拒绝申请']
            }, function (index) {
                layer.close(index);
                $.post("<?= url('water_order/auditCancel') ?>", {
                    order_id: id,
                    is_cancel: 1
                }, function (res) {
                    res.code === 1 ? (layer.msg(res.msg), setTimeout(function () {
                        location.reload();
                    }, 800)) : layer.msg(res.msg);
                });
            }, function (index) {
                layer.close(index);
                $.post("<?= url('water_order/auditCancel') ?>", {
                    order_id: id,
                    is_cancel: 0
                }, function (res) {
                    res.code === 1 ? (layer.msg(res.msg), setTimeout(function () {
                        location.reload();
                    }, 800)) : layer.msg(res.msg);
                });
            });
        });

        // 登记空桶归还数量
        $('.btn-recycle').on('click', function () {
            var $this = $(this), id = $this.data('id');
            var curNum = $this.data('num') || 0, maxNum = $this.data('max') || 0;
            var html = '<div style="padding:15px;">';
            html += '<p style="margin-bottom:10px;">已归还空桶数量（应还 ' + maxNum + ' 个）：</p>';
            html += '<input type="number" id="ipt-num" class="form-input" min="0" max="' + maxNum + '" value="' + curNum + '">';
            html += '</div>';
            layer.open({
                type: 1,
                title: '登记空桶归还',
                area: ['320px', '200px'],
                content: html,
                btn: ['确定', '取消'],
                yes: function (index) {
                    var num = parseInt($('#ipt-num').val(), 10) || 0;
                    layer.close(index);
                    $.post("<?= url('water_order/recycleBucket') ?>", {
                        order_id: id,
                        num: num
                    }, function (r) {
                        r.code === 1 ? (layer.msg(r.msg), setTimeout(function () {
                            location.reload();
                        }, 800)) : layer.msg(r.msg);
                    });
                }
            });
        });

        // 退还押桶押金（需空桶已全部归还）
        $('.btn-refund-deposit').on('click', function () {
            var id = $(this).data('id');
            layer.confirm('确认用户已归还全部空桶并退还押金？', function () {
                $.post("<?= url('water_order/refundDeposit') ?>", {order_id: id}, function (res) {
                    res.code === 1 ? (layer.msg(res.msg), setTimeout(function () {
                        location.reload();
                    }, 800)) : layer.msg(res.msg);
                });
            });
        });
    });
</script>
