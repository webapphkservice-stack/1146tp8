<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title"><?= $title ?></div>
                </div>
                <div class="widget-body float-right">
                    <div class="page_toolbar mb-1">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="dataType" value="<?= $dataType ?>">
                            <div class="btn-toolbar">
                                <div class="btn-group btn-group-xs">
                                    <?php if (checkPrivilege('order.operate/export')): ?>
                                        <a class="j-export btn btn-success rounded"
                                           href="javascript:void(0);">
                                            <i class="iconfont icon-daochu mr-4-xs"></i>订单导出
                                        </a>
                                    <?php endif; ?>
                                    <?php if (checkPrivilege('order.operate/batchdelivery')): ?>
                                        <?php if (in_array($dataType, ['all', 'delivery'])): ?>
                                            <a class="btn btn-secondary rounded"
                                               href="<?= url('order.operate/batchdelivery') ?>">
                                                <i class="iconfont icon-daoru mr-4-xs"></i>批量发货
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php $deliveryType = $request->get('delivery_type'); ?>
                            <select name="delivery_type" class="form-select">
                                <option value="">全部配送方式</option>
                                <?php foreach (app\common\enum\DeliveryType::data() as $item): ?>
                                    <option value="<?= $item['value'] ?>"
                                        <?= $item['value'] == $deliveryType ? 'selected' : '' ?>><?= $item['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php $extractShopId = $request->get('extract_shop_id'); ?>
                            <select name="extract_shop_id" class="form-select">
                                <option value="">全部门店</option>
                                <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                    <option value="<?= $item['shop_id'] ?>"
                                        <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <div class="tpl-form-border-form">
                                <input type="text" name="start_time" class="form-input"
                                       value="<?= $request->get('start_time') ?>" placeholder="起始日期">
                            </div>
                            <div class="tpl-form-border-form">
                                <input type="text" name="end_time" class="form-input"
                                       value="<?= $request->get('end_time') ?>" placeholder="截止日期">
                            </div>
                            <div class="input-group input-group-sm tpl-form-border-form">
                                <input type="text" class="form-input" name="search"
                                       placeholder="请输入订单号/用户昵称"
                                       value="<?= $request->get('search') ?>">
                                <div class="input-group-btn">
                                    <button class="btn btn-default icon-search"
                                            type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table width="100%" class="table-base tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>用户</th>
                                <th>商品</th>
                                <th>订单金额</th>
                                <th>实付</th>
                                <th>配送方式</th>
                                <th>支付状态</th>
                                <th>发货状态</th>
                                <th>下单时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= htmlspecialchars($item['order_no']) ?></td>
                                    <td class="align-middle"><?= htmlspecialchars($item['user']['nickName'] ?? '') ?></td>
                                    <td class="align-middle">
                                        <?php if (!empty($item['goods'])): ?>
                                            <?php foreach ($item['goods'] as $idx => $g): ?>
                                                <?php if ($idx < 2): ?>
                                                    <?= htmlspecialchars($g['goods_name']) ?><?= $idx < count($item['goods'])-1 ? '，' : '' ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            <?php if (count($item['goods']) > 2): ?>...<?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">¥<?= $item['order_price'] ?></td>
                                    <td class="align-middle">¥<?= $item['pay_price'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge badge-info"><?= $item['delivery_type']['text'] ?></span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['pay_status']['value'] == 10 ? 'warning' : 'success' ?>">
                                            <?= $item['pay_status']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-<?= $item['delivery_status']['value'] == 10 ? 'warning' : 'success' ?>">
                                            <?= $item['delivery_status']['text'] ?>
                                        </span>
                                    </td>
                                    <td class="align-middle"><?= time_text($item['create_time']) ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <a href="<?= url('order/detail', ['order_id' => $item['order_id']]) ?>">
                                                详情
                                            </a>
                                            <?php if ($item['delivery_status']['value'] == 10 && $item['pay_status']['value'] == 20): ?>
                                                <a href="<?= url('order/delivery', ['order_id' => $item['order_id']]) ?>">
                                                    发货
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-8">暂无订单</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($list->lastPage() > 1): ?>
                        <div class="flex items-center justify-between mt-3 px-2">
                            <div class="pagination-total text-muted text-sm">
                                共 <?= $list->total() ?> 条
                            </div>
                            <?= $list->render() ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
