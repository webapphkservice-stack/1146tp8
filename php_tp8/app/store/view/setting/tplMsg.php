<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="tips mt-3 mb-3">
                                <div class="pre">
                                    <p>
                                        模板消息仅用于微信小程序向用户发送服务通知，因微信限制，每笔支付订单可允许向用户在7天内推送最多3条模板消息。
                                        <a href="<?= url('store/setting.help/tplmsg') ?>" target="_blank">如何获取模板消息ID？</a>
                                    </p>
                                </div>
                            </div>
                            <div class="widget-head">
                                <div class="widget-title">支付成功通知</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    是否启用
                                </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[payment][is_enable]" value="1"
                                            <?= $values['payment']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[payment][is_enable]" value="0"
                                            <?= $values['payment']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="tplMsg[payment][template_id]"
                                           value="<?= $values['payment']['template_id'] ?>">
                                    <div class="help-block mt-4-xs">
                                        <small>模板编号AT0009，关键词 (订单编号、支付时间、订单金额、商品名称)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">订单发货通知</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    是否启用
                                </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[delivery][is_enable]" value="1"
                                            <?= $values['delivery']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[delivery][is_enable]" value="0"
                                            <?= $values['delivery']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="tplMsg[delivery][template_id]"
                                           value="<?= $values['delivery']['template_id'] ?>">
                                    <small>模板编号AT0007，关键词 (订单编号、商品信息、收货人、收货地址、物流公司、物流单号)</small>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">售后状态通知</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    是否启用
                                </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[refund][is_enable]" value="1"
                                            <?= $values['refund']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tplMsg[refund][is_enable]" value="0"
                                            <?= $values['refund']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    模板消息ID
                                    <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="tplMsg[refund][template_id]"
                                           value="<?= $values['refund']['template_id'] ?>">
                                    <small>模板编号AT0553，关键词 (售后类型、状态、订单号、商品名称、申请时间、申请原因)</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
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
