<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">代币充值订单</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 筛选 -->
                    <div class="page_toolbar mb-1 ">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <div class="sm:w-full md:w-3/4">
                                    <div class="form-group ">
                                        <?php $payStatus = $request->get('pay_status'); ?>
                                        <select name="pay_status" class="form-select">
                                            <option value="0" <?= empty($payStatus) ? 'selected' : '' ?>>全部</option>
                                            <option value="10" <?= $payStatus == 10 ? 'selected' : '' ?>>未支付</option>
                                            <option value="20" <?= $payStatus == 20 ? 'selected' : '' ?>>已支付</option>
                                        </select>
                                    <div class="form-group ">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="keyword"
                                                   placeholder="订单号/用户昵称" value="<?= $request->get('keyword') ?>">
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
                    <div class="sm:w-full">
                        <table width="100%" class="table-base text-center whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>用户</th>
                                <th>充值代币</th>
                                <th>支付金额</th>
                                <th>支付状态</th>
                                <th>微信流水号</th>
                                <th>下单时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['order_no'] ?></td>
                                    <td class="align-middle"><?= $item['nickName'] ?: '游客' ?></td>
                                    <td class="align-middle"><?= $item['coin_num'] ?></td>
                                    <td class="align-middle">￥<?= $item['money'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['pay_status'] == 20): ?>
                                            <span class="badge badge-success">已支付</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">未支付</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?= $item['transaction_id'] ?: '-' ?></td>
                                    <td class="align-middle"><?= $item['create_time'] ?: '-' ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">暂无记录</td>
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
