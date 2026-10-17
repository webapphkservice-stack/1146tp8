<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">积分设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 积分名称 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="points[points_name]"
                                           value="<?= $values['points_name'] ?>" required>
                                    <div class="help-block">
                                        <small>注：修改积分名称后，在买家端的所有页面里，看到的都是自定义的名称</small>
                                    </div>
                                    <div class="help-block">
                                        <small>注：商家使用自定义的积分名称来做品牌运营。如京东把积分称为“京豆”，淘宝把积分称为“淘金币”</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 积分说明 </label>
                                <div class="sm:w-3/4 ">
                                    <textarea rows="5" name="points[describe]"
                                              placeholder="请输入积分说明/规则"><?= $values['describe'] ?></textarea>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">积分赠送</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否开启购物送积分 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_shopping_gift]" value="1"
                                            <?= $values['is_shopping_gift'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_shopping_gift]" value="0"
                                            <?= $values['is_shopping_gift'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：如开启则订单完成后赠送用户积分</small>
                                    </div>
                                    <div class="help-block pt-1">
                                        <small>积分赠送规则：1.订单确认收货已完成；2.已完成订单超出后台设置的申请售后期限</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 积分赠送比例 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <input type="number" name="points[gift_ratio]"
                                               class="form-input" min="0"
                                               value="<?= $values['gift_ratio'] ?>" required>
                                        <span class="input-group-label input-group-label__right">%</span>
                                    </div>
                                    <div class="help-block">
                                        <small>注：赠送比例请填写数字0~100；订单的运费不参与积分赠送</small>
                                    </div>
                                    <div class="help-block">
                                        <small>例：订单付款金额(100.00元) * 积分赠送比例(100%) = 实际赠送的积分(100积分)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">每日签到</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否开启每日签到 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_signin_open]" value="1"
                                            <?= isset($values['is_signin_open']) && $values['is_signin_open'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_signin_open]" value="0"
                                            <?= isset($values['is_signin_open']) && $values['is_signin_open'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                    <div class="help-block">
                                        <small>注：开启后用户可在个人中心进行每日签到获得积分</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 每日签到赠送积分 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <input type="number" name="points[signin_points]"
                                               class="form-input" min="0"
                                               value="<?= isset($values['signin_points']) ? $values['signin_points'] : '10' ?>" required>
                                        <span class="input-group-label input-group-label__right">积分</span>
                                    </div>
                                    <div class="help-block">
                                        <small>注：用户每日签到可获得的积分数量</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">积分抵扣</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否允许下单使用积分抵扣 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_shopping_discount]" value="1"
                                            <?= $values['is_shopping_discount'] ? 'checked' : '' ?>> 允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="points[is_shopping_discount]" value="0"
                                            <?= $values['is_shopping_discount'] ? '' : 'checked' ?>> 不允许
                                    </label>
                                    <div class="help-block">
                                        <small>注：如开启则用户下单时可选择使用积分抵扣订单金额</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 积分抵扣比例 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <span class="input-group-label input-group-label__left">1个积分可抵扣</span>
                                        <input type="number" class="form-input" min="0.01"
                                               name="points[discount][discount_ratio]"
                                               value="<?= $values['discount']['discount_ratio'] ?>" required>
                                        <span class="input-group-label input-group-label__right">元</span>
                                    </div>
                                    <div class="help-block">
                                        <small>例如：1积分可抵扣0.01元，100积分则可抵扣1元，1000积分则可抵扣10元</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 抵扣条件 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group ">
                                        <span class="input-group-label input-group-label__left">订单满</span>
                                        <input type="number" class="form-input" min="0"
                                               name="points[discount][full_order_price]"
                                               value="<?= $values['discount']['full_order_price'] ?>" required>
                                        <span class="input-group-label input-group-label__right">元</span>
                                    </div>
                                    <div class="input-group mt-3">
                                        <span class="input-group-label input-group-label__left">
                                            最高可抵扣金额
                                        </span>
                                        <input type="number" class="form-input" min="0" max="90"
                                               name="points[discount][max_money_ratio]"
                                               value="<?= $values['discount']['max_money_ratio'] ?>" required>
                                        <span class="input-group-label input-group-label__right">%</span>
                                    </div>
                                    <div class="help-block">
                                        <small>温馨提示：例如订单金额100元，最高可抵扣10%，此时用户可抵扣10元</small>
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
