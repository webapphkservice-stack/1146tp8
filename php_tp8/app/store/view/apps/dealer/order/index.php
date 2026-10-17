<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">分销订单</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <input type="hidden" name="user_id" value="<?= $request->get('user_id') ?>">
                                        <select name="is_settled" class="form-select">
                                            <option value=""></option>
                                            <option value="-1" <?= $request->get('is_settled') == '-1' ? 'selected' : '' ?>>
                                                全部
                                            </option>
                                            <option value="0" <?= $request->get('is_settled') === '0' ? 'selected' : '' ?>>
                                                未结算
                                            </option>
                                            <option value="1" <?= $request->get('is_settled') == '1' ? 'selected' : '' ?>>
                                                已结算
                                            </option>
                                        </select>
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="order-list overflow-x-auto sm:w-full mt-4-xs">
                        <table width="100%" class="table-base text-center
                        whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th width="30%" class="goods-detail">商品信息</th>
                                <th width="10%">单价/数量</th>
                                <th width="15%">实付款</th>
                                <th>买家</th>
                                <th>交易状态</th>
                                <th>佣金结算</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list->toArray()['data'] as $order): ?>
                                <tr class="order-empty">
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td class="align-middle text-left" colspan="6">
                                        <span class="mr-6"> <?=  time_text($order['order_master']['create_time'])  ?></span>
                                        <span class="mr-6">订单号：<?= $order['order_master']['order_no'] ?></span>
                                    </td>
                                </tr>
                                <?php $i = 0;
                                foreach ($order['order_master']['goods'] as $goods): $i++; ?>
                                    <tr>
                                        <td class="goods-detail align-middle">
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
                                        <td class="align-middle">
                                            <p>￥<?= $goods['goods_price'] ?></p>
                                            <p>×<?= $goods['total_num'] ?></p>
                                        </td>
                                        <?php if ($i === 1) : $goodsCount = count($order['order_master']['goods']); ?>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p>￥<?= $order['order_master']['pay_price'] ?></p>
                                                <p class="text-gray-500">
                                                    (含运费：￥<?= $order['order_master']['express_price'] ?>)</p>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p><?= $order['order_master']['user']['nickName'] ?></p>
                                                <p class="text-gray-500">
                                                    (用户id：<?= $order['order_master']['user']['user_id'] ?>)</p>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <p>付款状态：
                                                    <span class="badge
                                                <?= $order['order_master']['pay_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['order_master']['pay_status']['text'] ?></span>
                                                </p>
                                                <p>发货状态：
                                                    <span class="badge
                                                <?= $order['order_master']['delivery_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['order_master']['delivery_status']['text'] ?></span>
                                                </p>
                                                <p>收货状态：
                                                    <span class="badge
                                                <?= $order['order_master']['receipt_status']['value'] == 20 ? 'badge-success' : '' ?>">
                                                        <?= $order['order_master']['receipt_status']['text'] ?></span>
                                                </p>
                                            </td>
                                            <td class="align-middle" rowspan="<?= $goodsCount ?>">
                                                <?php if (!!$order['is_settled']) : ?>
                                                    <span class="badge badge-success">已结算</span>
                                                <?php else : ?>
                                                    <span class="badge">未结算</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td class="align-middle text-left" colspan="6">
                                        <div class="dealer ">
                                            <?php if ($order['first_user_id'] > 0): ?>
                                                <div class="dealer-item  mr-4-xl">
                                                    <p>
                                                        <span class="text-right">一级分销商：</span>
                                                        <span><?= $order['dealer_first']['user']['nickName'] ?>
                                                            (ID: <?= $order['dealer_first']['user_id'] ?>)</span>
                                                    </p>
                                                    <p>
                                                        <span class="text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['first_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['second_user_id'] > 0): ?>
                                                <div class="dealer-item  mr-4-xl">
                                                    <p>
                                                        <span class="text-right">二级分销商：</span>
                                                        <span><?= $order['dealer_second']['user']['nickName'] ?>
                                                            (ID: <?= $order['dealer_second']['user_id'] ?>)</span>
                                                    </p>
                                                    <p>
                                                        <span class="text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['second_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['third_user_id'] > 0): ?>
                                                <div class="dealer-item  mr-4-xl">
                                                    <p>
                                                        <span class="text-right">三级分销商：</span>
                                                        <span><?= $order['dealer_third']['user']['nickName'] ?>
                                                            (ID: <?= $order['dealer_third']['user_id'] ?>)</span>
                                                    </p>
                                                    <p>
                                                        <span class="text-right">分销佣金：</span>
                                                        <span class="x-color-red">￥<?= $order['third_money'] ?></span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">暂无记录</td>
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

