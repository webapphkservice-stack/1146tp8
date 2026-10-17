<?php

use app\common\enum\DeliveryType as DeliveryTypeEnum;

?>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">拼团订单列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="active_id" value="<?= $request->param('active_id') ?>">
                            <div class="sm:w-full md:w-1/4">
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="btn-toolbar">
                                        <div class="btn-group btn-group-xs">
                                            <?php if (checkPrivilege('apps.sharing.order.operate/export')): ?>
                                                <a class="j-export btn btn-success rounded"
                                                   href="javascript:void(0);">
                                                    <i class="iconfont icon-daochu mr-4-xs"></i>订单导出
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('apps.sharing.order.operate/batchdelivery')): ?>
                                                <a class="j-export btn btn-secondary rounded"
                                                   href="<?= url('apps.sharing.order.operate/batchdelivery') ?>">
                                                    <i class="iconfont icon-daoru mr-4-xs"></i>批量发货
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sm:w-full md:w-3/4">
                                    <div class="form-group ">
                                        <?php $dataType = $request->get('dataType'); ?>
                                        <select name="dataType" class="form-select">
                                            <option value=""></option>
                                            <option value="all"
                                                <?= $dataType === 'all' ? 'selected' : '' ?>>全部
                                            </option>
                                            <option value="pay"
                                                <?= $dataType === 'pay' ? 'selected' : '' ?>>待付款
                                            </option>
                                            <option value="sharing"
                                                <?= $dataType === 'sharing' ? 'selected' : '' ?>>拼团中
                                            </option>
                                            <option value="sharing_succeed"
                                                <?= $dataType === 'sharing_succeed' ? 'selected' : '' ?>>拼团成功
                                            </option>
                                            <option value="sharing_fail"
                                                <?= $dataType === 'sharing_fail' ? 'selected' : '' ?>>拼团失败
                                            </option>
                                            <option value="delivery"
                                                <?= $dataType === 'delivery' ? 'selected' : '' ?>>待发货
                                            </option>
                                            <option value="receipt"
                                                <?= $dataType === 'receipt' ? 'selected' : '' ?>>待收货
                                            </option>
                                            <option value="complete"
                                                <?= $dataType === 'complete' ? 'selected' : '' ?>>已完成
                                            </option>
                                            <option value="cancel"
                                                <?= $dataType === 'cancel' ? 'selected' : '' ?>>已取消
                                            </option>
                                        </select>
                                    <div class="form-group ">
                                        <?php $deliveryType = $request->get('delivery_type'); ?>
                                        <select name="delivery_type" class="form-select">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $deliveryType === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php foreach (DeliveryTypeEnum::data() as $item): ?>
                                                <option value="<?= $item['value'] ?>"
                                                    <?= $item['value'] == $deliveryType ? 'selected' : '' ?>><?= $item['name'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group ">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="extract_shop_id" class="form-select">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractShopId === '-1' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-group tpl-form-border-form ">
                                        <input type="text" name="start_time"
                                               class="form-input"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期">
                                    </div>
                                    <div class="form-group tpl-form-border-form ">
                                        <input type="text" name="end_time"
                                               class="form-input"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期">
                                    </div>
                                    <div class="form-group ">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入订单号/用户昵称" value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list overflow-x-auto sm:w-full mt-4-xs">
                        <table width="100%" class="table-base text-center
                        whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th width="20%" class="goods-detail">商品信息</th>
                                <th>订单类型</th>
                                <th width="10%">单价/数量</th>
                                <th width="10%">实付款</th>
                                <th>买家</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $colspan = 9; ?>
                            <?php if (!$list->isEmpty()): foreach ($list as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="<?= $colspan ?>"></td>
                                </tr>
                                <tr>
                                    <td class="align-middle text-left" colspan="<?= $colspan ?>">
                                        <span class="mr-6"> <?=  time_text($order['create_time'])  ?></span>
                                        <span class="mr-6">订单号：<?= $order['order_no'] ?></span>
                                    </td>
                                </tr>
                                <?php $i = 0;
                                foreach ($order['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail align-middle">
                                            <div class="goods-image">
                                                <img src="<?= $goods['image']['file_path'] ?>" alt="">
                                            </div>
                                            <div class="goods-info">
                                                <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                                <p class="goods-spec text-gray-500"><?= $goods['goods_attr'] ?></p>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <?php if ($order['order_type']['value'] == 10): ?>
                                                <span class="badge badge-secondary">
                                                <?= $order['order_type']['text'] ?>
                                            </span>
                                            <?php else: ?>
                                                <span class="badge badge-success">
                                                <?= $order['order_type']['text'] ?>
                                            </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['total_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['goods']); ?>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['pay_price'] ?></p>
                                                <p class="text-gray-500">(含运费：￥<?= $order['express_price'] ?>)</p>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['user']['nickName'] ?></p>
                                                <p class="text-gray-500">(用户id：<?= $order['user']['user_id'] ?>)</p>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="badge badge-secondary"><?= $order['pay_type']['text'] ?></span>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <span class="badge badge-secondary"><?= $order['delivery_type']['text'] ?></span>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p>付款状态：
                                                    <span class="badge
                                                <?= $order['pay_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['pay_status']['text'] ?></span>
                                                </p>
                                                <?php if (
                                                    $order['pay_status']['value'] == 20
                                                    && $order['order_type']['value'] == 20
                                                ): ?>
                                                    <p>拼单状态：
                                                        <?php if (
                                                            $order['active']['status']['value'] == 0
                                                            || $order['active']['status']['value'] == 10
                                                        ): ?>
                                                            <span class="badge"><?= $order['active']['status']['text'] ?></span>
                                                        <?php elseif ($order['active']['status']['value'] == 20): ?>
                                                            <span class="badge badge-success"><?= $order['active']['status']['text'] ?></span>
                                                        <?php elseif ($order['active']['status']['value'] == 30): ?>
                                                            <span class="badge badge-danger"><?= $order['active']['status']['text'] ?></span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <!--拼团失败：退款设置-->
                                                    <?php if ($order['active']['status']['value'] == 30): ?>
                                                        <p>退款状态：
                                                            <?php if (!$order['is_refund']): ?>
                                                                <span class="badge">待退款</span>
                                                            <?php else: ?>
                                                                <span class="badge badge-success">已退款</span>
                                                            <?php endif; ?>
                                                        </p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <!-- 拼单不成功不显示发货和收货状态 -->
                                                <?php if (
                                                    $order['order_type']['value'] == 10
                                                    || (
                                                        $order['order_type']['value'] == 20
                                                        && $order['active']['status']['value'] == 20
                                                    )
                                                ): ?>
                                                    <p>发货状态：
                                                        <span class="badge
                                                <?= $order['delivery_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['delivery_status']['text'] ?></span>
                                                    </p>
                                                    <p>收货状态：
                                                        <span class="badge
                                                <?= $order['receipt_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['receipt_status']['text'] ?></span>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($order['order_status']['value'] == 20 || $order['order_status']['value'] == 21): ?>
                                                    <p>订单状态：
                                                        <span class="badge badge-warning"><?= $order['order_status']['text'] ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <div class="tpl-table-black-operation">
                                                    <!-- 拼团信息-->
                                                    <?php if (
                                                        $order['order_type']['value'] == 20
                                                        && $order['pay_status']['value']
                                                        && $order['active_id'] > 0
                                                    ): ?>
                                                        <?php if (checkPrivilege('apps.sharing.active/index')): ?>
                                                            <a class="tpl-table-black-operation-default"
                                                               href="<?= url('apps.sharing.active/index', ['active_id' => $order['active_id']]) ?>">
                                                                拼团信息</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <!-- 订单详情-->
                                                    <?php if (checkPrivilege('apps.sharing.order/detail')): ?>
                                                        <a class="tpl-table-black-operation-default"
                                                           href="<?= url('apps.sharing.order/detail', ['order_id' => $order['order_id']]) ?>">
                                                            订单详情</a>
                                                    <?php endif; ?>
                                                    <!-- 去发货-->
                                                    <?php if (checkPrivilege(['apps.sharing.order/detail', 'apps.sharing.order/delivery'])): ?>
                                                        <?php if (
                                                            // 判断订单状态是否满足发货条件
                                                            $order['pay_status']['value'] == 20
                                                            && $order['order_status']['value'] != 20
                                                            && $order['order_status']['value'] != 21
                                                            && $order['delivery_status']['value'] == 10
                                                            // 拼团订单验证拼单状态
                                                            && ($order['order_type']['value'] == 20 ? $order['active']['status']['value'] == 20 : true)
                                                        ): ?>
                                                            <a class="tpl-table-black-operation"
                                                               href="<?= url('apps.sharing.order/detail#delivery',
                                                                   ['order_id' => $order['order_id']]) ?>">去发货</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <!-- 审核用户申请取消订单-->
                                                    <?php if (checkPrivilege(['apps.sharing.order/detail', 'apps.sharing.order.operate/confirmcancel'])): ?>
                                                        <?php if ($order['order_status']['value'] == 21): ?>
                                                            <a class="tpl-table-black-operation-del"
                                                               href="<?= url('apps.sharing.order/detail#cancel',
                                                                   ['order_id' => $order['order_id']]) ?>">去审核</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <!-- 拼团失败手动退款-->
                                                    <?php if (checkPrivilege('apps.sharing.order.operate/refund')): ?>
                                                        <?php if (
                                                            $order['order_type']['value'] == 20
                                                            && $order['pay_status']['value'] == 20
                                                            && $order['active']['status']['value'] == 30
                                                            && $order['is_refund'] == 0
                                                        ): ?>
                                                            <a class="tpl-table-black-operation-del"
                                                               href="<?= url('apps.sharing.order/detail#refund',
                                                                   ['order_id' => $order['order_id']]) ?>">去退款</a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="<?= $colspan ?>" class="text-center">暂无记录</td>
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
         * 订单导出
         */
        $('.j-export').click(function () {
            var data = {};
            var formData = $('#form-search').serializeArray();
            $.each(formData, function () {
                this.name !== 's' && (data[this.name] = this.value);
            });
            window.location = "<?= url('apps.sharing.order.operate/export') ?>" + '&' + $.urlEncode(data);
        });

    });

</script>

