<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">出租车订单</div>
                    <div class="widget-function float-right">
                        <span class="text-gray-500" style="font-size:12px">价格以下单时后台配置为准</span>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <!-- 搜索 -->
                    <div class="overflow-x-auto sm:w-full">
                        <form class=" flex flex-wrap items-center gap-3" action="<?= url('taxi/order') ?>" method="get">
                            <div class="spec-form-inline inline-flex items-center gap-1">
                                <input type="text" class="form-input" name="order_no"
                                       placeholder="订单号" value="<?= request()->param('order_no') ?>">
                            </div>
                            <div class="spec-form-inline inline-flex items-center gap-1">
                                <input type="text" class="form-input" name="flight_no"
                                       placeholder="航班号" value="<?= request()->param('flight_no') ?>">
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <select name="status" style="width:140px" class="form-select">
                                    <option value="">全部状态</option>
                                    <option value="0" <?= request()->param('status') === '0' ? 'selected' : '' ?>>待接单</option>
                                    <option value="1" <?= request()->param('status') == 1 ? 'selected' : '' ?>>已接单</option>
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
                                <th>抵达机场</th>
                                <th>车型</th>
                                <th>价格</th>
                                <th>航班信息</th>
                                <th>酒店</th>
                                <th>联系人</th>
                                <th>订单状态</th>
                                <th>下单时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['order_no'] ?></td>
                                    <td class="align-middle"><?= $item['airport_name'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['car_type'] == 1): ?>
                                            <span class="badge badge-primary"><?= $item['car_type_text'] ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= $item['car_type_text'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['currency'] ?> <?= $item['price'] ?></td>
                                    <td class="align-middle">
                                        <?= $item['flight_no'] ?>
                                        <div class="text-gray-500" style="font-size:12px">
                                            出发 <?= $item['departure_time'] ?: '—' ?> /
                                            起飞 <?= $item['takeoff_time'] ?: '—' ?> /
                                            降落 <?= $item['landing_time'] ?: '—' ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?= $item['hotel_name'] ?: '<span class="text-gray-500">—</span>' ?>
                                        <div class="text-gray-500" style="font-size:12px;max-width:240px;white-space:normal">
                                            <?= $item['hotel_address'] ?>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <?= $item['contact_name'] ?: '<span class="text-gray-500">—</span>' ?>
                                        <div class="text-gray-500" style="font-size:12px"><?= $item['contact_phone'] ?></div>
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
                                    <td class="align-middle"><?= $item['create_time_text'] ?></td>
                                    <td class="align-middle">
                                        <form class="flex flex-wrap items-center gap-3" action="<?= url('taxi/orderState') ?>" method="post" style="margin:0">
                                            <input type="hidden" name="order_id" value="<?= $item['order_id'] ?>">
                                            <select name="status" style="width:108px" class="form-select">
                                                <option value="1" <?= $item['status'] == 1 ? 'selected' : '' ?>>已接单</option>
                                                <option value="2" <?= $item['status'] == 2 ? 'selected' : '' ?>>已完成</option>
                                                <option value="3" <?= $item['status'] == 3 ? 'selected' : '' ?>>已取消</option>
                                            </select>
                                            <select name="vehicle_index" style="width:160px;margin-top:6px" class="form-select">
                                                <option value="">派车（不变）</option>
                                                <?php foreach ($vehicle_options as $vo): ?>
                                                    <option value="<?= $vo['index'] ?>"><?= htmlspecialchars($vo['label']) ?></option>
                                                <?php endforeach; ?>
                                                <option value="clear">清除派车</option>
                                            </select>
                                            <button class="btn btn-xs btn-primary" type="submit" style="margin-top:6px">更新</button>
                                        </form>
                                        <?php if (!empty($item['dispatch_label'])): ?>
                                            <div class="text-gray-500" style="font-size:12px;margin-top:4px">已派：<?= htmlspecialchars($item['dispatch_label']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">暂无出租车订单</td>
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
