<?php
// 配送配置：用 toArray() + ?? 兜底，避免数据库字段尚未迁移时报错
$cfg = $model->toArray();
$deliveryEnabled = $cfg['delivery_enabled'] ?? 0;
$deliveryFee = $cfg['delivery_fee'] ?? '0.00';
$minOrderAmount = $cfg['min_order_amount'] ?? '0.00';
$freeDeliveryAmount = $cfg['free_delivery_amount'] ?? '0.00';
$depositEnabled = $cfg['deposit_enabled'] ?? 0;
$depositAmount = $cfg['deposit_amount'] ?? '0.00';
$waterTicketEnabled = $cfg['water_ticket_enabled'] ?? 0;
$emptyBucketRecycle = $cfg['empty_bucket_recycle'] ?? 0;
$deliveryTimeRange = $cfg['delivery_time_range'] ?? '';
$timeRangeArr = explode('-', $deliveryTimeRange);
$deliveryTimeStart = isset($timeRangeArr[0]) ? trim($timeRangeArr[0]) : '';
$deliveryTimeEnd = isset($timeRangeArr[1]) ? trim($timeRangeArr[1]) : '';
?>

<!-- 配送设置表单 -->
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">门店配送（送水）设置 - <?= $model['shop_name'] ?></div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 开启门店配送 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" name="shop[delivery_enabled]" value="1"
                                            <?= $deliveryEnabled == 1 ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="shop[delivery_enabled]" value="0"
                                            <?= $deliveryEnabled != 1 ? 'checked' : '' ?>> 关闭
                                    </label>
                                    <div class="text-xs text-gray-500 mt-1">关闭后该门店不会出现在用户可选配送门店中</div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 配送费 </label>
                                <div class="sm:w-3/4">
                                    <input type="number" step="0.01" min="0" class="tpl-form-input"
                                           name="shop[delivery_fee]" value="<?= $deliveryFee ?>">
                                    <div class="text-xs text-gray-500 mt-1">用户选择该门店配送时收取的配送费，0 表示免配送费</div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 起送金额 </label>
                                <div class="sm:w-3/4">
                                    <input type="number" step="0.01" min="0" class="tpl-form-input"
                                           name="shop[min_order_amount]" value="<?= $minOrderAmount ?>">
                                    <div class="text-xs text-gray-500 mt-1">订单商品金额达到该值才允许配送，0 表示不限制</div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 满额免配送费 </label>
                                <div class="sm:w-3/4">
                                    <input type="number" step="0.01" min="0" class="tpl-form-input"
                                           name="shop[free_delivery_amount]" value="<?= $freeDeliveryAmount ?>">
                                    <div class="text-xs text-gray-500 mt-1">订单商品金额达到该值免除配送费，0 表示不减免</div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 押桶押金 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" name="shop[deposit_enabled]" value="1"
                                            <?= $depositEnabled == 1 ? 'checked' : '' ?>> 收取
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="shop[deposit_enabled]" value="0"
                                            <?= $depositEnabled != 1 ? 'checked' : '' ?>> 不收
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 单桶押金金额 </label>
                                <div class="sm:w-3/4">
                                    <input type="number" step="0.01" min="0" class="tpl-form-input"
                                           name="shop[deposit_amount]" value="<?= $depositAmount ?>">
                                    <div class="text-xs text-gray-500 mt-1">按购买件数累计：押金 = 单桶押金 × 商品件数</div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 支持水票 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" name="shop[water_ticket_enabled]" value="1"
                                            <?= $waterTicketEnabled == 1 ? 'checked' : '' ?>> 支持
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="shop[water_ticket_enabled]" value="0"
                                            <?= $waterTicketEnabled != 1 ? 'checked' : '' ?>> 不支持
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 空桶回收 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center mr-4">
                                        <input type="radio" name="shop[empty_bucket_recycle]" value="1"
                                            <?= $emptyBucketRecycle == 1 ? 'checked' : '' ?>> 支持
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="shop[empty_bucket_recycle]" value="0"
                                            <?= $emptyBucketRecycle != 1 ? 'checked' : '' ?>> 不支持
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6"> 配送时段 </label>
                                <div class="sm:w-3/4 flex items-center gap-x-2">
                                    <input type="time" class="tpl-form-input" name="shop[delivery_time_start]"
                                           value="<?= $deliveryTimeStart ?>" style="width:140px">
                                    <span>至</span>
                                    <input type="time" class="tpl-form-input" name="shop[delivery_time_end]"
                                           value="<?= $deliveryTimeEnd ?>" style="width:140px">
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-4 flex gap-x-2">
                                    <button type="submit" class="j-submit btn btn-secondary rounded">保存设置</button>
                                    <a class="btn btn-default rounded" href="<?= url('shop/index') ?>">返回门店列表</a>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 可配送区域 -->
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">可配送区域</div>
                </div>
                <div class="widget-body float-right">
                    <form id="area-form" method="post"
                          action="<?= url('shop/addArea', ['shop_id' => $model['shop_id']]) ?>">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="x-region-select" data-region-selected class="flex gap-x-2">
                                <select name="area[province_id]" data-province class="tpl-form-input" style="width:140px">
                                    <option value="">请选择省份</option>
                                </select>
                                <select name="area[city_id]" data-city class="tpl-form-input" style="width:140px">
                                    <option value="">请选择城市</option>
                                </select>
                                <select name="area[region_id]" data-region class="tpl-form-input" style="width:140px">
                                    <option value="">请选择地区（不选=全市）</option>
                                </select>
                            </div>
                            <button type="button" id="btn-add-area" class="btn btn-secondary rounded">添加区域</button>
                            <div class="text-xs text-gray-500 mt-2">不选地区表示该城市全辖区均可配送</div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table width="100%" class="table-base tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>区域</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($areaList) && !$areaList->isEmpty()): foreach ($areaList as $area): ?>
                                <tr>
                                    <td class="align-middle"><?= $area['area_name'] ?></td>
                                    <td class="align-middle">
                                        <button class="btn btn-danger btn-xs btn-del-area rounded"
                                                data-id="<?= $area['id'] ?>"
                                                data-shop-id="<?= $model['shop_id'] ?>">删除</button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="2" class="text-center">暂未配置配送区域</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 配送员设置 -->
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">配送员设置（由店员兼任）</div>
                </div>
                <div class="widget-body float-right">
                    <div class="overflow-x-auto">
                        <table width="100%" class="table-base tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>店员ID</th>
                                <th>昵称</th>
                                <th>设为配送员</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($clerkList) && !$clerkList->isEmpty()): foreach ($clerkList as $clerk): ?>
                                <tr>
                                    <td class="align-middle"><?= $clerk['clerk_id'] ?></td>
                                    <td class="align-middle"><?= $clerk['real_name'] ?: '-' ?></td>
                                    <td class="align-middle">
                                        <input type="checkbox" class="chk-deliveryer"
                                               data-id="<?= $clerk['clerk_id'] ?>"
                                            <?= $clerk['is_deliveryer'] == 1 ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">该门店暂无店员</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">开启后，该店员可在送水订单接单时被指派为配送员（配送员由店员兼任）</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        // 校验：开押桶押金时必须同时开启空桶回收
        $('#my-form').on('submit', function () {
            var deposit = $('input[name="shop[deposit_enabled]"]:checked').val();
            var recycle = $('input[name="shop[empty_bucket_recycle]"]:checked').val();
            if (deposit === '1' && recycle !== '1') {
                layer.msg('开启押桶押金时，必须同时开启空桶回收');
                return false;
            }
        });
        $('#my-form').superForm();

        // 添加配送区域
        $('#btn-add-area').on('click', function () {
            if (!$('[data-province]').val() || !$('[data-city]').val()) {
                layer.msg('请选择省份与城市');
                return;
            }
            $.post($('#area-form').attr('action'), $('#area-form').serialize(), function (res) {
                if (res.code === 1) {
                    layer.msg(res.msg);
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    layer.msg(res.msg);
                }
            });
        });

        // 设置/取消配送员
        $('.chk-deliveryer').on('change', function () {
            var $this = $(this);
            $.post("<?= url('shop/setDeliveryer') ?>", {
                clerk_id: $this.data('id'),
                is_deliveryer: $this.prop('checked') ? 1 : 0
            }, function (res) {
                layer.msg(res.msg);
            });
        });

        // 删除配送区域
        $('.btn-del-area').on('click', function () {
            var $this = $(this);
            layer.confirm('确定删除该配送区域？', function () {
                $.post("<?= url('shop/delArea') ?>", {
                    id: $this.data('id'),
                    shop_id: $this.data('shop-id')
                }, function (res) {
                    if (res.code === 1) {
                        layer.msg(res.msg);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        layer.msg(res.msg);
                    }
                });
            });
        });
    });
</script>
