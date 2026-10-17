<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">订单流程设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">未支付订单 </label>
                                <div class="sm:w-3/4">
                                    <div class="sm:w-1/2">
                                        <input type="number" class="form-input" name="trade[order][close_days]"
                                               value="<?= $values['order']['close_days'] ?>" required>
                                    </div>
                                    <label class="sm:w-1/2 text-sm text-gray-600 shrink-0 text-left">天后自动关闭</label>
                                    <div class="help-block sm:w-full">
                                        <small>订单下单未付款，n天后自动关闭，设置0天不自动关闭</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">已发货订单 </label>
                                <div class="sm:w-3/4">
                                    <div class="sm:w-1/2">
                                        <input type="number" class="form-input" name="trade[order][receive_days]"
                                               value="<?= $values['order']['receive_days'] ?>" required>
                                    </div>
                                    <label class="sm:w-1/2 text-sm text-gray-600 shrink-0 text-left">天后自动确认收货</label>
                                    <div class="help-block sm:w-full">
                                        <small>如果在期间未确认收货，系统自动完成收货，设置0天不自动收货</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">已完成订单 </label>
                                <div class="sm:w-3/4">
                                    <div class="sm:w-1/2">
                                        <input type="number" class="form-input" name="trade[order][refund_days]"
                                               value="<?= $values['order']['refund_days'] ?>" required>
                                    </div>
                                    <label class="sm:w-1/2 text-sm text-gray-600 shrink-0 text-left">天内允许申请售后</label>
                                    <div class="help-block sm:w-full">
                                        <small>订单完成后 ，用户在n天内可以发起售后申请，设置0天不允许申请售后</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">运费设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">运费组合策略 </label>
                                <div class="sm:w-3/4">
                                    <div class="sm:w-full">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="trade[freight_rule]" value="10"
                                                <?= $values['freight_rule'] == '10' ? 'checked' : '' ?> required> 叠加
                                        </label>
                                        <div class="help-block">
                                            <small>订单中的商品有多个运费模板时，将每个商品的运费之和订为订单总运费</small>
                                        </div>
                                    </div>
                                    <div class="sm:w-full mt-3">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="trade[freight_rule]" value="20"
                                                <?= $values['freight_rule'] == '20' ? 'checked' : '' ?> required>
                                            以最低运费结算
                                        </label>
                                        <div class="help-block">
                                            <small>订单中的商品有多个运费模板时，取订单中运费最少的商品的运费计为订单总运费</small>
                                        </div>
                                    </div>
                                    <div class="sm:w-full mt-3">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="trade[freight_rule]" value="30"
                                                <?= $values['freight_rule'] == '30' ? 'checked' : '' ?> required>
                                            以最高运费结算
                                        </label>
                                        <div class="help-block">
                                            <small>订单中的商品有多个运费模板时，取订单中运费最多的商品的运费计为订单总运费</small>
                                        </div>
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
