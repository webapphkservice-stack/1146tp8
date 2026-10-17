<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">VIP会员设置</div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">会员日设置</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 每月会员日 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <input type="number" name="vip[member_day]" class="form-input" min="1" max="31"
                                               value="<?= $values['member_day'] ?>" required>
                                        <span class="input-group-label input-group-label__right">号</span>
                                    </div>
                                    <div class="help-block">
                                        <small>每月该日期为会员日，当天VIP会员可获得双倍积分；设为0表示不启用会员日</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 会员日双倍积分 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="vip[is_member_day_double]" value="1"
                                            <?= $values['is_member_day_double'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="vip[is_member_day_double]" value="0"
                                            <?= $values['is_member_day_double'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>开启后，会员日当天VIP会员的积分（订单/签到）将按倍率翻倍</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 积分倍数 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <input type="number" name="vip[double_points_ratio]" class="form-input" min="1"
                                               value="<?= $values['double_points_ratio'] ?>" required>
                                        <span class="input-group-label input-group-label__right">倍</span>
                                    </div>
                                    <div class="help-block">
                                        <small>会员日当天VIP积分赠送倍数，例如2表示翻倍</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">开通权益</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> 赠送运费券 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <select name="vip[freight_coupon_id]" class="form-select">
                                        <option value="0">不赠送</option>
                                        <?php foreach ($couponList as $coupon): ?>
                                            <option value="<?= $coupon['coupon_id'] ?>"
                                                <?= $values['freight_coupon_id'] == $coupon['coupon_id'] ? 'selected' : '' ?>>
                                                <?= $coupon['name'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>开通VIP会员时自动赠送一张该运费券给用户（需先在优惠券中创建"运费券"类型）</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 退货包运费 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="vip[return_freight]" value="1"
                                            <?= $values['return_freight'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="vip[return_freight]" value="0"
                                            <?= $values['return_freight'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>开启后，VIP会员的订单标记为"退货包运费"，可在售后时享受运费减免</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
