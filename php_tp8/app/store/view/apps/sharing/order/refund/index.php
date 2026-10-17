<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">售后列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="w-full">
                                    <div class="form-group tpl-form-border-form ">
                                        <input type="text" name="start_time"
                                               class="form-input"
                                               value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期">
                                    <div class="form-group tpl-form-border-form ">
                                        <input type="text" name="end_time"
                                               class="form-input"
                                               value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期">
                                    </div>
                                    <div class="form-group ">
                                        <select name="type" class="form-select">
                                            <option value=""></option>
                                            <option value="10" <?= $request->get('type') === '10' ? 'selected' : '' ?>>
                                                退货退款
                                            </option>
                                            <option value="20" <?= $request->get('type') === '20' ? 'selected' : '' ?>>
                                                换货
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group ">
                                        <select name="state" class="form-select">
                                            <option value=""></option>
                                            <option value="0" <?= $request->get('state') === '0' ? 'selected' : '' ?>>
                                                进行中
                                            </option>
                                            <option value="10" <?= $request->get('state') === '10' ? 'selected' : '' ?>>
                                                已拒绝
                                            </option>
                                            <option value="20" <?= $request->get('state') === '20' ? 'selected' : '' ?>>
                                                已完成
                                            </option>
                                        </select>
                                    </div>
                                    <div class="form-group ">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="order_no"
                                                   placeholder="请输入订单号"
                                                   value="<?= $request->get('order_no') ?>">
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
                                <th width="24%" class="goods-detail">商品信息</th>
                                <th width="10%">单价/数量</th>
                                <th width="15%">付款价</th>
                                <th>买家</th>
                                <th>售后类型</th>
                                <th>处理状态</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr class="order-empty">
                                    <td colspan="7"></td>
                                </tr>
                                <tr>
                                    <td class="align-middle text-left" colspan="7">
                                        <span class="mr-6">售后申请时间：<?=  time_text($item['create_time'])  ?></span>
                                        <span class="mr-6">订单号：<?= $item['order_no'] ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="goods-detail align-middle">
                                        <div class="goods-image">
                                            <img src="<?= $item['order_goods']['image']['file_path'] ?>"
                                                 alt="">
                                        </div>
                                        <div class="goods-info">
                                            <p class="goods-title"><?= $item['order_goods']['goods_name'] ?></p>
                                            <p class="goods-spec text-gray-500"><?= $item['order_goods']['goods_attr'] ?></p>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <p>￥<?= $item['order_goods']['goods_price'] ?></p>
                                        <p>×<?= $item['order_goods']['total_num'] ?></p>
                                    </td>
                                    <td class="align-middle">
                                        <p>￥<?= $item['order_goods']['total_pay_price'] ?></p>
                                    </td>
                                    <td class="align-middle">
                                        <p><?= $item['user']['nickName'] ?></p>
                                        <p class="text-gray-500">(用户id：<?= $item['user']['user_id'] ?>)</p>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-secondary"> <?= $item['type']['text'] ?> </span>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['status']['value'] == 0): ?>
                                            <!-- 审核状态-->
                                            <p>
                                                <span>商家审核：</span>
                                                <?php if ($item['is_agree']['value'] == 0): ?>
                                                    <span class="badge"> <?= $item['is_agree']['text'] ?> </span>
                                                <?php elseif ($item['is_agree']['value'] == 10): ?>
                                                    <span class="badge badge-success"> <?= $item['is_agree']['text'] ?> </span>
                                                <?php elseif ($item['is_agree']['value'] == 20): ?>
                                                    <span class="badge badge-warning"> <?= $item['is_agree']['text'] ?> </span>
                                                <?php endif; ?>
                                            </p>
                                            <!-- 发货状态-->
                                            <?php if ($item['type']['value'] == 10 && $item['is_agree']['value'] == 10): ?>
                                                <p>
                                                    <span>用户发货：</span>
                                                    <?php if ($item['is_user_send'] == 0): ?>
                                                        <span class="badge"> 待发货 </span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success"> 已发货 </span>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            <!-- 商家收货状态-->
                                            <?php if (
                                                $item['type']['value'] == 10
                                                && $item['is_agree']['value'] == 10
                                                && $item['is_user_send'] == 1
                                                && $item['is_receipt'] == 0
                                            ): ?>
                                                <p><span>商家收货：</span> <span class="badge">待收货</span></p>
                                            <?php endif; ?>
                                        <?php elseif ($item['status']['value'] == 20): ?>
                                            <span class="badge badge-success"> <?= $item['status']['text'] ?> </span>
                                        <?php elseif ($item['status']['value'] == 10 || $item['status']['value'] == 30): ?>
                                            <span class="badge badge-warning"> <?= $item['status']['text'] ?> </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php $url = url('apps.sharing.order.refund/detail', ['order_refund_id' => $item['order_refund_id']]); ?>
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.sharing.order.refund/detail')): ?>
                                                <a class="tpl-table-black-operation-green"
                                                   href="<?= $url ?>">售后详情</a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege(['apps.sharing.order.refund/detail', 'apps.sharing.order.refund/audit'])): ?>
                                                <?php if ($item['is_agree']['value'] == 0): ?>
                                                    <a class="tpl-table-black-operation-del"
                                                       href="<?= $url ?>#audit">去审核</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege(['apps.sharing.order.refund/detail', 'apps.sharing.order.refund/receipt'])): ?>
                                                <?php if (
                                                    $item['type']['value'] == 10
                                                    && $item['is_agree']['value'] == 10
                                                    && $item['is_user_send'] == 1
                                                    && $item['is_receipt'] == 0
                                                ): ?>
                                                    <a class="tpl-table-black-operation-del"
                                                       href="<?= $url ?>#receipt">确认收货</a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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


    });
</script>

