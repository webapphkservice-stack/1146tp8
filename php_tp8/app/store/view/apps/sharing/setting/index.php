<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">拼团设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 拼团失败自动退款 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[auto_refund]" value="1"
                                            <?= ($values['auto_refund'] ?? '') == 1 ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[auto_refund]" value="0"
                                            <?= ($values['auto_refund'] ?? '') == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block mt-3">
                                        <small>注：如果不开启自动退款，则需要在 [订单管理] 处手动退款</small>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4-lg">
                                <label class="sm:w-1/4 form-require"> 是否允许使用优惠券 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_coupon]" value="1"
                                            <?= ($values['is_coupon'] ?? '') == 1 ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_coupon]" value="0"
                                            <?= ($values['is_coupon'] ?? '') == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="pt-4-lg">
                                <label class="sm:w-1/4 form-require"> 是否支持分销 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="1"
                                            <?= ($values['is_dealer'] ?? '') == 1 ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="0"
                                            <?= ($values['is_dealer'] ?? '') == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block pt-1">
                                        <small>注：拼团订单是否参与分销</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">规则描述</div>
                            </div>
                            <div class="pt-4-lg">
                                <label class="sm:w-1/4 form-require"> 规则简述 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="basic[rule_brief]"
                                           value="<?= $values['rule_brief'] ?? '' ?>">
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 form-require"> 规则详述 </label>
                                <div class="sm:w-3/4">
                                    <textarea class="border-green-500" rows="7" placeholder="请输入分销商申请协议"
                                              name="basic[rule_detail]"><?= $values['rule_detail'] ?? '' ?></textarea>
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
