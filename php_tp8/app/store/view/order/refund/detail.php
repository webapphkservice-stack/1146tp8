<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget__order-detail widget-body mb-6">
                    <div class="widget-head">
                        <div class="widget-title">售后单信息</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                            <tbody>
                            <tr>
                                <th>订单号</th>
                                <th>买家</th>
                                <th>售后类型</th>
                                <th>处理状态</th>
                                <th>操作</th>
                            </tr>
                            <tr>
                                <td><?= $order['order_no'] ?></td>
                                <td>
                                    <p><?= $order['user']['nickName'] ?></p>
                                    <p class="text-gray-500">(用户id：<?= $order['user']['user_id'] ?>)</p>
                                </td>
                                <td class="">
                                    <span class="badge badge-secondary"> <?= $detail['type']['text'] ?> </span>
                                </td>
                                <td>
                                    <?php if ($detail['status']['value'] == 0): ?>
                                        <!-- 审核状态-->
                                        <p>
                                            <span>商家审核：</span>
                                            <?php if ($detail['is_agree']['value'] == 0): ?>
                                                <span class="badge"> <?= $detail['is_agree']['text'] ?> </span>
                                            <?php elseif ($detail['is_agree']['value'] == 10): ?>
                                                <span class="badge badge-success"> <?= $detail['is_agree']['text'] ?> </span>
                                            <?php elseif ($detail['is_agree']['value'] == 20): ?>
                                                <span class="badge badge-warning"> <?= $detail['is_agree']['text'] ?> </span>
                                            <?php endif; ?>
                                        </p>
                                        <!-- 发货状态-->
                                        <?php if ($detail['type']['value'] == 10 && $detail['is_agree']['value'] == 10): ?>
                                            <p>
                                                <span>用户发货：</span>
                                                <?php if ($detail['is_user_send'] == 0): ?>
                                                    <span class="badge"> 待发货 </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success"> 已发货 </span>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                        <!-- 商家收货状态-->
                                        <?php if (
                                            $detail['type']['value'] == 10
                                            && $detail['is_agree']['value'] == 10
                                            && $detail['is_user_send'] == 1
                                            && $detail['is_receipt'] == 0
                                        ): ?>
                                            <p><span>商家收货：</span> <span class="badge">待收货</span></p>
                                        <?php endif; ?>
                                    <?php elseif ($detail['status']['value'] == 20): ?>
                                        <span class="badge badge-success"> <?= $detail['status']['text'] ?> </span>
                                    <?php elseif ($detail['status']['value'] == 10 || $detail['status']['value'] == 30): ?>
                                        <span class="badge badge-warning"> <?= $detail['status']['text'] ?> </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (checkPrivilege('order/detail')): ?>
                                        <a class="x-f-13" target="_blank"
                                           href="<?= url('order/detail', ['order_id' => $detail['order_id']]) ?>">订单详情</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="widget-head">
                        <div class="widget-title">售后商品信息</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="regional-table table-base border text-center
                            whitespace-nowrap mb-1">
                            <tbody>
                            <tr>
                                <th width="25%">商品名称</th>
                                <th>商品编码</th>
                                <th>重量(Kg)</th>
                                <th>单价</th>
                                <th>购买数量</th>
                                <th>付款价</th>
                            </tr>
                            <tr>
                                <td class="goods-detail align-middle">
                                    <div class="goods-image">
                                        <img src="<?= $detail['order_goods']['image']['file_path'] ?>" alt="">
                                    </div>
                                    <div class="goods-info">
                                        <p class="goods-title"><?= $detail['order_goods']['goods_name'] ?></p>
                                        <p class="goods-spec text-gray-500">
                                            <?= $detail['order_goods']['goods_attr'] ?>
                                        </p>
                                    </div>
                                </td>
                                <td><?= $detail['order_goods']['goods_no'] ?: '--' ?></td>
                                <td><?= $detail['order_goods']['goods_weight'] ?: '--' ?></td>
                                <td>￥<?= $detail['order_goods']['goods_price'] ?></td>
                                <td>×<?= $detail['order_goods']['total_num'] ?></td>
                                <td><span class="x-color-red">￥<?= $detail['order_goods']['total_pay_price'] ?></span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="widget-head">
                        <div class="widget-title">用户申请原因</div>
                    </div>
                    <div class="apply_desc pl-4">
                        <div class="content x-f-13">
                            <span><?= $detail['apply_desc'] ?></span>
                        </div>
                        <div class="images">
                            <div class="uploader-list ">
                                <?php if (!empty($detail['image'])): foreach ($detail['image'] as $image): ?>
                                    <div class="file-item x-mt-10">
                                        <a href="<?= $image['file_path'] ?>"
                                           title="点击查看大图" target="_blank">
                                            <img src="<?= $image['file_path'] ?>">
                                        </a>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 商家审核 -->
                    <?php if (checkPrivilege('order.refund/audit')): ?>
                        <?php if ($detail['is_agree']['value'] == 0): ?>
                            <div class="widget-head">
                                <div class="widget-title">商家审核</div>
                            </div>
                            <!-- 去审核 -->
                            <form id="audit" class="my-form tpl-form-line-form" method="post"
                                  action="<?= url('order.refund/audit', ['order_refund_id' => $detail['order_refund_id']]) ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">售后类型 </label>
                                    <div class="sm:w-3/4  pt-1">
                                        <span class="badge badge-secondary"> <?= $detail['type']['text'] ?> </span>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">审核状态 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="refund[is_agree]"
                                                   value="10"
                                                   checked
                                                   required>
                                            同意
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="refund[is_agree]"
                                                   value="20">
                                            拒绝
                                        </label>
                                    </div>
                                </div>
                                <div class="item-agree-10 form-tab-group active">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">退货地址 </label>
                                    <div class="sm:w-3/4 ">
                                        <select name="refund[address_id]" class="form-select">
                                            <option value=""></option>
                                            <?php if (!empty($address)): foreach ($address as $item): ?>
                                                <option value="<?= $item['address_id'] ?>"><?= $item['name'] ?> <?= $item['phone'] ?> <?= $item['detail'] ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                        <small class="ml-1">
                                            <a href="<?= url('setting.address/index') ?>" target="_blank">去添加</a>
                                        </small>
                                    </div>
                                </div>
                                <div class="item-agree-20 form-tab-group ">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">拒绝原因 </label>
                                    <div class="sm:w-3/4 ">
                                <textarea class="border-green-500" rows="4" placeholder="请输入拒绝原因"
                                          name="refund[refuse_desc]"></textarea>
                                        <small>如审核状态为拒绝，则需要输入拒绝原因</small>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="sm:w-5/6 sm:ml-[16.667%] mt-6">
                                        <button type="submit" class="j-submit btn btn-sm btn-secondary">
                                            确认审核
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- 退货地址 -->
                    <?php if ($detail['is_agree']['value'] == 10): ?>
                        <div class="widget-head">
                            <div class="widget-title">退货地址</div>
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
                                    <td><?= $detail['address']['detail'] ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- 商家拒绝原因 -->
                    <?php if ($detail['is_agree']['value'] == 20): ?>
                        <div class="widget-head">
                            <div class="widget-title">商家拒绝原因</div>
                        </div>
                        <div class="apply_desc pl-4">
                            <div class="content x-f-13">
                                <span><?= $detail['refuse_desc'] ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 用户发货信息 -->
                    <?php if (
                        $detail['type']['value'] == 10
                        && $detail['is_agree']['value'] == 10
                        && $detail['is_user_send'] == 1
                    ): ?>
                        <div class="widget-head">
                            <div class="widget-title">用户发货信息</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table-base border text-center
                                whitespace-nowrap mb-1">
                                <tbody>
                                <tr>
                                    <th>物流公司</th>
                                    <th>物流单号</th>
                                    <th>用户发货状态</th>
                                    <th>发货时间</th>
                                    <th>商家收货状态</th>
                                </tr>
                                <tr>
                                    <td><?= $detail['express']['express_name'] ?></td>
                                    <td><?= $detail['express_no'] ?></td>
                                    <td>
                                        <span class="badge badge-success">已发货</span>
                                    </td>
                                    <td><?= date('Y-m-d H:i:s', $detail['send_time']) ?></td>
                                    <td>
                                        <?php if ($detail['is_receipt'] == 1): ?>
                                            <span class="badge badge-success">已收货</span>
                                        <?php else: ?>
                                            <span class="badge">待收货</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- 确认收货并退款 -->
                    <?php if (checkPrivilege('order.refund/receipt')): ?>
                        <?php if (
                            $detail['type']['value'] == 10
                            && $detail['is_agree']['value'] == 10
                            && $detail['is_user_send'] == 1
                            && $detail['is_receipt'] == 0
                        ): ?>
                            <div class="widget-head">
                                <div class="widget-title">确认收货并退款</div>
                            </div>
                            <div class="tips mb-3 sm:w-full">
                                <div class="pre">
                                    <p class="">注：该操作将执行订单原路退款 并关闭当前售后单，请确认并填写退款的金额（不能大于订单实付款）</p>
                                    <?php if ($order['update_price']['value'] != 0): ?>
                                        <p class="x-color-red">
                                            注：当前订单存在后台改价记录，退款金额请参考订单实付款金额</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form id="receipt" class="my-form tpl-form-line-form" method="post"
                                  action="<?= url('order.refund/receipt', ['order_refund_id' => $detail['order_refund_id']]) ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">售后类型 </label>
                                    <div class="sm:w-3/4  pt-1">
                                        <span class="badge badge-secondary"> <?= $detail['type']['text'] ?> </span>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">退款金额 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0.01" class="tpl-form-input"
                                               name="refund[refund_money]"
                                               value="<?= min($order['pay_price'], $detail['order_goods']['total_pay_price']) ?>"
                                               required>
                                        <small>
                                            请输入退款金额，最多<?= min($order['pay_price'], $detail['order_goods']['total_pay_price']) ?>
                                            元
                                        </small>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="sm:w-5/6 sm:ml-[16.667%] mt-6">
                                        <button type="submit" class="j-submit btn btn-sm btn-secondary">
                                            确认收货并退款
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {

        // 切换审核状态
        $("input:radio[name='refund[is_agree]']").change(function (e) {
            $('.form-tab-group')
                .removeClass('active')
                .filter('.item-agree-' + e.currentTarget.value)
                .addClass('active');
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('.my-form').superForm();

    });
</script>
