<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">优惠券领取记录</div>
                </div>
                <div class="widget-body float-right">
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th class="text-center">用户</th>
                                <th>优惠券ID</th>
                                <th>优惠券名称</th>
                                <th>优惠券类型</th>
                                <th>最低消费金额</th>
                                <th>优惠方式</th>
                                <th>有效期</th>
                                <th>领取时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="text-center">
                                        <p class=""><?= $item['user']['nickName'] ?></p>
                                        <p class="text-gray-500">(用户id：<?= $item['user']['user_id'] ?>)</p>
                                    </td>
                                    <td class="align-middle"><?= $item['coupon_id'] ?></td>
                                    <td class="align-middle"><?= $item['name'] ?></td>
                                    <td class="align-middle"><?= $item['coupon_type']['text'] ?></td>
                                    <td class="align-middle"><?= $item['min_price'] ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['coupon_type']['value'] == 10) : ?>
                                            <span>立减 <strong><?= $item['reduce_price'] ?></strong> 元</span>
                                        <?php elseif ($item['coupon_type']['value'] == 20) : ?>
                                            <span>打 <strong><?= $item['discount'] ?></strong> 折</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['expire_type'] == 10) : ?>
                                            <span>领取 <strong><?= $item['expire_day'] ?></strong> 天内有效</span>
                                        <?php elseif ($item['expire_type'] == 20) : ?>
                                            <span><?= $item['start_time']['text'] ?>
                                                ~ <?= $item['end_time']['text'] ?></span>
                                        <?php endif; ?>
                                    </td>
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

    });
</script>
