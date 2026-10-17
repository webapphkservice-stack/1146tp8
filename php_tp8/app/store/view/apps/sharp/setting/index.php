<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 整点秒杀设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">未支付订单 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="sm:w-1/2 x-pl-0">
                                        <input type="number" class="tpl-form-input" name="basic[order][order_close]"
                                               value="<?= $values['order']['order_close'] ?? '' ?>" required>
                                    </div>
                                    <label class="sm:w-1/2 text-sm text-gray-600 shrink-0 text-left">分钟后自动关闭</label>
                                    <div class="help-block sm:w-full">
                                        <small>秒杀订单下单未付款，n分钟后自动关闭，设置0则不自动关闭</small>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 是否支持分销 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="1"
                                            <?= ($values['is_dealer'] ?? '') == 1 ? 'checked' : '' ?> required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="0" <?= ($values['is_dealer'] ?? '') == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block pt-1">
                                        <small>注：整点秒杀订单是否参与分销</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-sm btn-secondary">提交
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
