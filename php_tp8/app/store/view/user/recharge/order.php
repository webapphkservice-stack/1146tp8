<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">余额充值记录</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-4-xl ">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                    <?php $rechargeType = $request->get('recharge_type'); ?>
                                    <select name="recharge_type" class="form-select">
                                        <option value=""></option>
                                        <option value="-1"
                                            <?= $rechargeType === '-1' ? 'selected' : '' ?>>全部
                                        </option>
                                        <?php foreach ($attributes['rechargeType'] as $attr): ?>
                                            <option value="<?= $attr['value'] ?>"
                                                <?= $rechargeType === (string)$attr['value'] ? 'selected' : '' ?>>
                                                <?= $attr['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php $payStatus = $request->get('pay_status'); ?>
                                    <select name="pay_status" class="form-select">
                                        <option value=""></option>
                                        <option value="-1"
                                            <?= $payStatus === '-1' ? 'selected' : '' ?>>全部
                                        </option>
                                        <?php foreach ($attributes['pay_status'] as $attr): ?>
                                            <option value="<?= $attr['value'] ?>"
                                                <?= $payStatus === (string)$attr['value'] ? 'selected' : '' ?>>
                                                <?= $attr['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <div class="tpl-form-border-form ">
                                    <input type="text" name="start_time"
                                           class="form-input"
                                           value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期">
                                <div class="tpl-form-border-form ">
                                    <input type="text" name="end_time"
                                           class="form-input"
                                           value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期">
                                </div>
                                    <div class="input-group input-group-sm tpl-form-border-form">
                                        <input type="text" class="form-input" name="search" placeholder="请输入用户昵称/订单号"
                                               value="<?= $request->get('search') ?>">
                                        <div class="input-group-btn">
                                            <button class="btn btn-default icon-search" type="submit"></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>订单ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>订单号</th>
                                <th>充值方式</th>
                                <th>套餐名称</th>
                                <th>支付金额</th>
                                <th>赠送金额</th>
                                <th>支付状态</th>
                                <th>付款时间</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['order_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <p class=""><?= $item['user']['nickName'] ?></p>
                                        <p class="text-gray-500">(用户ID：<?= $item['user']['user_id'] ?>)</p>
                                    </td>
                                    <td class="align-middle"><?= $item['order_no'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge
                                        <?= $item['recharge_type']['value'] == 10 ? 'badge-secondary' : 'badge-success' ?>">
                                                <?= $item['recharge_type']['text'] ?></span>
                                    </td>
                                    <td class="align-middle"><?= !empty($item['order_plan']) ? $item['order_plan']['plan_name'] : '--' ?></td>
                                    <td class="align-middle"><?= $item['pay_price'] ?></td>
                                    <td class="align-middle"><?= $item['gift_money'] ?></td>
                                    <td class="align-middle">
                                        <span class="badge
                                        <?= $item['pay_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                <?= $item['pay_status']['text'] ?></span>
                                    </td>
                                    <td class="align-middle"><?= $item['pay_time']['text'] ?: '--' ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">暂无记录</td>
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

        // 删除元素
        var url = "<?= url('market.coupon/delete') ?>";
        $('.item-delete').delete('coupon_id', url);

    });
</script>

