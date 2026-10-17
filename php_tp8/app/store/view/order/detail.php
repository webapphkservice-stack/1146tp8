<?php

// 订单详情
$detail = isset($detail) ? $detail : null;

// 本地 DB 降级时，给模板补一个安全的空结构，避免 Undefined array key
if (empty($detail)) {
    $detail = [
        'order_id'        => 0,
        'order_no'        => '',
        'create_time'     => 0,
        'total_price'     => '0.00',
        'order_price'     => '0.00',
        'pay_price'       => '0.00',
        'coupon_id'       => 0,
        'coupon_money'    => '0.00',
        'points_num'      => 0,
        'points_money'    => '0.00',
        'express_price'   => '0.00',
        'buyer_remark'    => '',
        'transaction_id'  => '',
        'pay_time'        => 0,
        'delivery_time'   => 0,
        'receipt_time'    => 0,
        'express_no'      => '',
        'pay_status'      => ['value' => 10, 'text' => '待付款'],
        'delivery_status' => ['value' => 10, 'text' => '待发货'],
        'receipt_status'  => ['value' => 10, 'text' => '待收货'],
        'order_status'    => ['value' => 10, 'text' => '进行中'],
        'pay_type'        => ['text' => '未支付'],
        'delivery_type'   => ['value' => 10, 'text' => '快递配送'],
        'update_price'    => ['value' => '0.00', 'symbol' => ''],
        'user'            => ['user_id' => 0, 'nickName' => '未知用户'],
        'address'         => [
            'name'    => '',
            'phone'   => '',
            'detail'  => '',
            'region'  => ['province' => '', 'city' => '', 'region' => ''],
        ],
        'express'         => ['express_name' => ''],
        'goods'           => [],
        'extract'         => ['linkman' => '', 'phone' => ''],
        'extract_shop'    => [
            'shop_id'    => 0,
            'shop_name'  => '',
            'address'    => '',
            'phone'      => '',
            'linkman'    => '',
            'logo'       => ['file_path' => ''],
            'region'     => ['province' => '', 'city' => '', 'region' => ''],
        ],
        'extract_clerk'   => ['clerk_id' => 0, 'real_name' => ''],
    ];
}

?>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget__order-detail widget-body mb-6">

                    <!-- 订单进度步骤条 -->
                    <div class="sm:w-full">
                        <?php
                        // 计算当前步骤位置
                        $progress = 2;
                        $detail['pay_status']['value'] == 20 && $progress += 1;
                        $detail['delivery_status']['value'] == 20 && $progress += 1;
                        $detail['receipt_status']['value'] == 20 && $progress += 1;
                        // $detail['order_status']['value'] == 30 && $progress += 1;
                        ?>
                        <ul class="order-detail-progress progress-<?= $progress ?>">
                            <li>
                                <span>下单时间</span>
                                <div class="tip"><?=  time_text($detail['create_time'])  ?></div>
                            </li>
                            <li>
                                <span>付款</span>
                                <?php if ($detail['pay_status']['value'] == 20): ?>
                                    <div class="tip">
                                        付款于 <?= date('Y-m-d H:i:s', $detail['pay_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>发货</span>
                                <?php if ($detail['delivery_status']['value'] == 20): ?>
                                    <div class="tip">
                                        发货于 <?= date('Y-m-d H:i:s', $detail['delivery_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>收货</span>
                                <?php if ($detail['receipt_status']['value'] == 20): ?>
                                    <div class="tip">
                                        收货于 <?= date('Y-m-d H:i:s', $detail['receipt_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <li>
                                <span>完成</span>
                                <?php if ($detail['order_status']['value'] == 30): ?>
                                    <div class="tip">
                                        完成于 <?= date('Y-m-d H:i:s', $detail['receipt_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>

                    <!-- 基本信息 -->
                    <div class="widget-head">
                        <div class="widget-title">基本信息</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>订单金额</th>
                                <th>支付方式</th>
                                <th>配送方式</th>
                                <th>交易状态</th>
                                <?php if ($detail['pay_status']['value'] == 10 && $detail['order_status']['value'] == 10) : ?>
                                    <th>操作</th>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td><?= $detail['order_no'] ?></td>
                                <td>
                                    <p><?= $detail['user']['nickName'] ?></p>
                                    <p class="text-gray-500">(用户id：<?= $detail['user']['user_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <div class="td__order-price text-left">
                                        <ul class="grid grid-cols-2 gap-4">
                                            <li class="text-right">订单总额：</li>
                                            <li class="text-right">￥<?= $detail['total_price'] ?> </li>
                                        </ul>
                                        <?php if ($detail['coupon_id'] > 0) : ?>
                                            <ul class="grid grid-cols-2 gap-4">
                                                <li class="text-right">优惠券抵扣：</li>
                                                <li class="text-right">- ￥<?= $detail['coupon_money'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                        <?php if ($detail['points_num'] > 0) : ?>
                                            <ul class="grid grid-cols-2 gap-4">
                                                <li class="text-right">积分抵扣：</li>
                                                <li class="text-right">- ￥<?= $detail['points_money'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="grid grid-cols-2 gap-4">
                                            <li class="text-right">运费金额：</li>
                                            <li class="text-right">+￥<?= $detail['express_price'] ?></li>
                                        </ul>
                                        <?php if ($detail['update_price']['value'] != '0.00') : ?>
                                            <ul class="grid grid-cols-2 gap-4">
                                                <li class="text-right">后台改价：</li>
                                                <li class="text-right"><?= $detail['update_price']['symbol'] ?>
                                                    ￥<?= $detail['update_price']['value'] ?></li>
                                            </ul>
                                        <?php endif; ?>
                                        <ul class="grid grid-cols-2 gap-4">
                                            <li class="text-right">实付款金额：</li>
                                            <li class="x-color-red text-right">
                                                ￥<?= $detail['pay_price'] ?></li>
                                        </ul>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= $detail['pay_type']['text'] ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary"><?= $detail['delivery_type']['text'] ?></span>
                                </td>
                                <td>
                                    <p>付款状态：
                                        <span class="badge
                                        <?= $detail['pay_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?></span>
                                    </p>
                                    <p>发货状态：
                                        <span class="badge
                                        <?= $detail['delivery_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                <?= $detail['delivery_status']['text'] ?></span>
                                    </p>
                                    <p>收货状态：
                                        <span class="badge
                                        <?= $detail['receipt_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                <?= $detail['receipt_status']['text'] ?></span>
                                    </p>
                                    <?php if ($detail['order_status']['value'] == 20 || $detail['order_status']['value'] == 21): ?>
                                        <p>订单状态：
                                            <span class="badge badge-warning"><?= $detail['order_status']['text'] ?></span>
                                        </p>
                                    <?php endif; ?>
                                </td>
                                <?php if ($detail['pay_status']['value'] == 10 && $detail['order_status']['value'] == 10) : ?>
                                    <td>
                                        <?php if (checkPrivilege('order/updateprice')): ?>
                                            <p class="text-center">
                                                <a class="j-update-price" href="javascript:void(0);"
                                                   data-order_id="<?= $detail['order_id'] ?>"
                                                   data-order_price="<?= $detail['order_price'] ?>"
                                                   data-express_price="<?= $detail['express_price'] ?>">修改价格</a>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 商品信息 -->
                    <div class="widget-head">
                        <div class="widget-title">商品信息</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                            <tbody>
                            <tr>
                                <th>商品名称</th>
                                <th>商品编码</th>
                                <th>重量(Kg)</th>
                                <th>单价</th>
                                <th>购买数量</th>
                                <th>商品总价</th>
                            </tr>
                            <?php foreach ($detail['goods'] as $goods): ?>
                                <tr>
                                    <td class="goods-detail align-middle" width="30%">
                                        <div class="goods-image">
                                            <img src="<?= $goods['image']['file_path'] ?>" alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            <p class="goods-spec text-gray-500">
                                                <?= $goods['goods_attr'] ?>
                                            </p>
                                        </div>
                                    </td>
                                    <td><?= $goods['goods_no'] ?: '--' ?></td>
                                    <td><?= $goods['goods_weight'] ?: '--' ?></td>
                                    <td>
                                        <p class="<?= $goods['is_user_grade'] ? 'x-text-delete' : '' ?>">
                                            <span>￥<?= $goods['goods_price'] ?: '--' ?></span>
                                        </p>
                                        <?php if ($goods['is_user_grade']): ?>
                                            <p class="x-color-red">
                                                会员折扣价：<span>￥<?= $goods['grade_goods_price'] ?: '--' ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td>×<?= $goods['total_num'] ?></td>
                                    <td>￥<?= $goods['total_price'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="6" class="text-right ">
                                    <span class="">买家留言：<?= $detail['buyer_remark'] ?: '无' ?></span>
                                    <span class="float-right">总计金额：￥<?= $detail['total_price'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 收货信息 -->
                    <?php if ($detail['delivery_type']['value'] == app\common\enum\DeliveryType::EXPRESS): ?>
                        <div class="widget-head">
                            <div class="widget-title">收货信息</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                                <tbody>
                                <tr>
                                    <th>收货人</th>
                                    <th>收货电话</th>
                                    <th>收货地址</th>
                                </tr>
                                <tr>
                                    <td><?= $detail['address']['name'] ?></td>
                                    <td><?= $detail['address']['phone'] ?></td>
                                    <td>
                                        <?= $detail['address']['region']['province'] ?>
                                        <?= $detail['address']['region']['city'] ?>
                                        <?= $detail['address']['region']['region'] ?>
                                        <?= $detail['address']['detail'] ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- 自提门店信息 -->
                    <?php if ($detail['delivery_type']['value'] == app\common\enum\DeliveryType::EXTRACT): ?>
                        <?php if (!empty($detail['extract'])): ?>
                            <div class="widget-head">
                                <div class="widget-title">自提信息</div>
                            </div>
                            <div class="help-block x-f-14 pl-4">
                                <p class="mb-1">联系人：<?= $detail['extract']['linkman'] ?></p>
                                <p>联系电话：<?= $detail['extract']['phone'] ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="widget-head">
                            <div class="widget-title">自提门店信息</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                                <tbody>
                                <tr>
                                    <th>门店ID</th>
                                    <th>门店logo</th>
                                    <th>门店名称</th>
                                    <th>联系人</th>
                                    <th>联系电话</th>
                                    <th>门店地址</th>
                                </tr>
                                <tr>
                                    <td><?= $detail['extract_shop']['shop_id'] ?></td>
                                    <td>
                                        <a href="<?= $detail['extract_shop']['logo']['file_path'] ?>" title="点击查看大图"
                                           target="_blank">
                                            <img src="<?= $detail['extract_shop']['logo']['file_path'] ?>" height="72"
                                                 alt="">
                                        </a>
                                    </td>
                                    <td><?= $detail['extract_shop']['shop_name'] ?></td>
                                    <td><?= $detail['extract_shop']['linkman'] ?></td>
                                    <td><?= $detail['extract_shop']['phone'] ?></td>
                                    <td>
                                        <?= $detail['extract_shop']['region']['province'] ?>
                                        <?= $detail['extract_shop']['region']['city'] ?>
                                        <?= $detail['extract_shop']['region']['region'] ?>
                                        <?= $detail['extract_shop']['address'] ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- 付款信息 -->
                    <?php if ($detail['pay_status']['value'] == 20): ?>
                        <div class="widget-head">
                            <div class="widget-title">付款信息</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="regional-table table-base border text-center
                                whitespace-nowrap mb-1">
                                <tbody>
                                <tr>
                                    <th>应付款金额</th>
                                    <th>支付方式</th>
                                    <th>支付流水号</th>
                                    <th>付款状态</th>
                                    <th>付款时间</th>
                                </tr>
                                <tr>
                                    <td>￥<?= $detail['pay_price'] ?></td>
                                    <td><?= $detail['pay_type']['text'] ?></td>
                                    <td><?= $detail['transaction_id'] ?: '--' ?></td>
                                    <td>
                                        <span class="badge
                                        <?= $detail['pay_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                <?= $detail['pay_status']['text'] ?></span>
                                    </td>
                                    <td>
                                        <?= $detail['pay_time'] ? date('Y-m-d H:i:s', $detail['pay_time']) : '--' ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!--  用户取消订单 -->
                    <?php if ($detail['pay_status']['value'] == 20 && $detail['order_status']['value'] == 21): ?>
                        <?php if (checkPrivilege('order.operate/confirmcancel')): ?>
                            <div class="widget-head">
                                <div class="widget-title"><strong>用户取消订单</strong></div>
                            </div>
                            <div class="tips mb-3 sm:w-full">
                                <div class="pre">
                                    <p>当前买家已付款并申请取消订单，请审核是否同意，如同意则自动退回付款金额（微信支付原路退款）并关闭订单。</p>
                                </div>
                            </div>
                            <!-- 去审核 -->
                            <form id="cancel" class="my-form tpl-form-line-form" method="post"
                                  action="<?= url('order.operate/confirmcancel', ['order_id' => $detail['order_id']]) ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">审核状态 </label>
                                    <div class="sm:w-3/4 ">
                                        <div class="sm:w-3/4">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="order[is_cancel]"
                                                       value="1"
                                                       required>
                                                同意
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="order[is_cancel]"
                                                       value="0"
                                                       checked>
                                                拒绝
                                            </label>
                                        </div>

                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                        <button type="submit" class="j-submit btn btn-sm btn-secondary">
                                            确认审核
                                        </button>

                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 发货信息 -->
                    <?php if (
                        $detail['pay_status']['value'] == 20    // 支付状态：已支付
                        && $detail['delivery_type']['value'] == app\common\enum\DeliveryType::EXPRESS
                        && !in_array($detail['order_status']['value'], [20, 21])   // 订单状态：未取消
                    ): ?>
                        <div class="widget-head">
                            <div class="widget-title">发货信息</div>
                        </div>
                        <?php if ($detail['delivery_status']['value'] == 10): ?>
                            <?php if (checkPrivilege('order/delivery')): ?>
                                <!-- 去发货 -->
                                <form id="delivery" class="my-form tpl-form-line-form" method="post"
                                      action="<?= url('order/delivery', ['order_id' => $detail['order_id']]) ?>">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require">物流公司 </label>
                                        <div class="sm:w-3/4  pt-1">
                                            <select name="order[express_id]" required class="form-select">
                                                <option value=""></option>
                                                <?php if (isset($expressList)): foreach ($expressList as $expres): ?>
                                                    <option value="<?= $expres['express_id'] ?>">
                                                        <?= $expres['express_name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                            <div class="help-block mt-4-xs">
                                                <small>可在 <a href="<?= url('setting.express/index') ?>" target="_blank">物流公司列表</a>
                                                    中设置
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require">物流单号 </label>
                                        <div class="sm:w-3/4 ">
                                            <input type="text" class="tpl-form-input" name="order[express_no]" required>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                            <button type="submit" class="j-submit btn btn-sm btn-secondary">
                                                确认发货
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="regional-table table-base border text-center
                                    whitespace-nowrap mb-1">
                                    <tbody>
                                    <tr>
                                        <th>物流公司</th>
                                        <th>物流单号</th>
                                        <th>发货状态</th>
                                        <th>发货时间</th>
                                    </tr>
                                    <tr>
                                        <td><?= $detail['express']['express_name'] ?></td>
                                        <td><?= $detail['express_no'] ?></td>
                                        <td>
                                             <span class="badge
                                            <?= $detail['delivery_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                    <?= $detail['delivery_status']['text'] ?></span>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d H:i:s', $detail['delivery_time']) ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 门店自提核销 -->
                    <?php if (
                        $detail['pay_status']['value'] == 20    // 支付状态：已支付
                        && $detail['delivery_type']['value'] == app\common\enum\DeliveryType::EXTRACT
                        && !in_array($detail['order_status']['value'], [20, 21])   // 订单状态：未取消
                    ): ?>
                        <div class="widget-head">
                            <div class="widget-title">门店自提核销</div>
                        </div>
                        <?php if ($detail['delivery_status']['value'] == 10): ?>
                            <?php if (checkPrivilege('order.operate/extract')): ?>
                                <form id="delivery" class="my-form tpl-form-line-form" method="post"
                                      action="<?= url('order.operate/extract', ['order_id' => $detail['order_id']]) ?>">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require">门店核销员 </label>
                                        <div class="sm:w-3/4  pt-1">
                                            <select name="order[extract_clerk_id]"
                                                    required class="form-select">
                                                <option value=""></option>
                                                <?php if (isset($shopClerkList)): foreach ($shopClerkList as $clerk): ?>
                                                    <option value="<?= $clerk['clerk_id'] ?>">
                                                        <?= $clerk['real_name'] ?> (<?= $clerk['shop']['shop_name'] ?>)
                                                    </option>
                                                <?php endforeach; endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require">买家取货状态 </label>
                                        <div class="sm:w-3/4 ">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="order[extract_status]" value="1"
                                                       checked required>
                                                已取货
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                            <button type="submit" class="j-submit btn btn-sm btn-secondary">
                                                确认核销
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="regional-table table-base border text-center
                                    whitespace-nowrap mb-1">
                                    <tbody>
                                    <tr>
                                        <th>自提门店名称</th>
                                        <th>核销员</th>
                                        <th>核销状态</th>
                                        <th>核销时间</th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p><?= $detail['extract_shop']['shop_name'] ?></p>
                                            <p class="text-gray-500">
                                                (ID: <?= $detail['extract_shop']['shop_id'] ?>)
                                            </p>
                                        </td>
                                        <td>
                                            <p><?= $detail['extract_clerk']['real_name'] ?></p>
                                            <p class="text-gray-500">
                                                (ID: <?= $detail['extract_clerk']['clerk_id'] ?>)
                                            </p>
                                        </td>
                                        <td>
                                             <span class="badge
                                            <?= $detail['delivery_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                    已核销</span>
                                        </td>
                                        <td>
                                            <?= date('Y-m-d H:i:s', $detail['delivery_time']) ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- 后台改价模板 -->
<script id="tpl-update-price" type="text/template">
    <div class="pt-3">
        <form class="form-update-price tpl-form-line-form" method="post"
              action="<?= url('order/updatePrice', ['order_id' => $detail['order_id']]) ?>">
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 订单金额 </label>
                <div class="sm:w-3/4">
                    <input type="number" min="0.00" class="tpl-form-input" name="order[update_price]"
                           value="{{ order_price }}">
                    <small>最终付款价 = 订单金额 + 运费金额</small>
                </div>
            </div>
            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 运费金额 </label>
                <div class="sm:w-3/4">
                    <input type="number" min="0.00" class="tpl-form-input" name="order[update_express_price]"
                           value="{{ express_price }}">
                </div>
            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 修改价格
         */
        $('.j-update-price').click(function () {
            var data = $(this).data();
            $.showModal({
                title: '订单价格修改'
                , content: template('tpl-update-price', data)
                , yes: function () {
                    // 表单提交
                    $('.form-update-price').ajaxSubmit({
                        type: "post",
                        dataType: "json",
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    return true;
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('.my-form').superForm();

    });
</script>
