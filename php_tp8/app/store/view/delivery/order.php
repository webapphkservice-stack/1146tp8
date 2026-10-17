<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">外送订单</div>
                    <div class="widget-function float-right">
                        <span class="text-gray-500" style="font-size:12px">店铺接单/配送状态取自对应店铺当前设置</span>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <!-- 搜索 -->
                    <div class="overflow-x-auto sm:w-full">
                        <form class=" flex flex-wrap items-center gap-3" action="<?= url('delivery/order') ?>" method="get">
                            <div class="spec-form-inline inline-flex items-center gap-1">
                                <input type="text" class="form-input" name="order_no"
                                       placeholder="订单号" value="<?= request()->param('order_no') ?>">
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <select name="status" style="width:140px" class="form-select">
                                    <option value="">全部状态</option>
                                    <option value="0" <?= request()->param('status') === '0' ? 'selected' : '' ?>>待接单</option>
                                    <option value="1" <?= request()->param('status') == 1 ? 'selected' : '' ?>>配送中</option>
                                    <option value="2" <?= request()->param('status') == 2 ? 'selected' : '' ?>>已完成</option>
                                    <option value="3" <?= request()->param('status') == 3 ? 'selected' : '' ?>>已取消</option>
                                </select>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <button class="btn btn-default btn-radius" type="submit">搜索</button>
                            </div>
                        </form>
                    </div>
                    <div class="sm:w-full mt-4-xs">
                        <table width="100%" class="table-base   tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>所属店铺</th>
                                <th>外送服务</th>
                                <th>费用</th>
                                <th>收货人</th>
                                <th>订单状态</th>
                                <th>店铺接单状态</th>
                                <th>店铺配送状态</th>
                                <th>下单时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['order_no'] ?></td>
                                    <td class="align-middle"><?= $item['shop_name'] ?: '<span class="text-gray-500">—</span>' ?></td>
                                    <td class="align-middle"><?= $item['delivery_title'] ?></td>
                                    <td class="align-middle">
                                        RM <?= $item['total_price'] ?>
                                        <div class="text-gray-500" style="font-size:12px">
                                            商品 <?= $item['goods_price'] ?> + 配送 <?= $item['delivery_fee'] ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?= $item['address']['name'] ?> <?= $item['address']['phone'] ?>
                                        <div class="text-gray-500" style="font-size:12px;max-width:240px">
                                            <?= $item['address']['region'] ?> <?= $item['address']['detail'] ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['status'] == 0): ?>
                                            <span class="badge badge-warning"><?= $item['status_text'] ?></span>
                                        <?php elseif ($item['status'] == 1): ?>
                                            <span class="badge badge-primary"><?= $item['status_text'] ?></span>
                                        <?php elseif ($item['status'] == 2): ?>
                                            <span class="badge badge-success"><?= $item['status_text'] ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= $item['status_text'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['accept_order'] == 1): ?>
                                            <span class="badge badge-success">接单中</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">暂不接单</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['delivery_enabled'] == 1): ?>
                                            <span class="badge badge-success">可外送</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">仅自提</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['create_time_text'] ?></td>
                                    <td class="align-middle">
                                        <form class="flex flex-wrap items-center gap-3" action="<?= url('delivery.order/orderState') ?>" method="post" style="margin:0">
                                            <input type="hidden" name="order_id" value="<?= $item['order_id'] ?>">
                                            <select name="status" style="width:108px" class="form-select">
                                                <option value="1" <?= $item['status'] == 1 ? 'selected' : '' ?>>已接单</option>
                                                <option value="3" <?= $item['status'] == 3 ? 'selected' : '' ?>>已取消</option>
                                            </select>
                                            <button class="btn btn-xs btn-primary" type="submit">更新</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">暂无外送订单</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="lg:w-full ">
                            <div class="float-right"><?= $list->render() ?> </div>
                            <div class="float-right pagination-total mr-4">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
