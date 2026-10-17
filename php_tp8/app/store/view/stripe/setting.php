<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">Stripe 支付配置</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('stripe/setting') ?>" method="post">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">启用 Stripe </label>
                            <div class="sm:w-3/4">
                                <select name="stripe[enable]" class="form-select">
                                    <?php $enable = isset($values['enable']) ? $values['enable'] : 0; ?>
                                    <option value="1" <?= $enable == 1 ? 'selected' : '' ?>>开启</option>
                                    <option value="0" <?= $enable == 0 ? 'selected' : '' ?>>关闭</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">Secret Key </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="stripe[secret_key]"
                                       value="<?= isset($values['secret_key']) ? $values['secret_key'] : '' ?>" placeholder="sk_test_xxx / sk_live_xxx">
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">Publishable Key </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="stripe[publishable_key]"
                                       value="<?= isset($values['publishable_key']) ? $values['publishable_key'] : '' ?>" placeholder="pk_test_xxx / pk_live_xxx">
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">货币 </label>
                            <div class="sm:w-3/4">
                                <input type="text" name="stripe[currency]"
                                       value="<?= isset($values['currency']) ? $values['currency'] : 'myr' ?>" placeholder="myr / usd / cny">
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">Webhook Secret </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="stripe[webhook_secret]"
                                       value="<?= isset($values['webhook_secret']) ? $values['webhook_secret'] : '' ?>" placeholder="whsec_xxx">
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%]">
                                <button type="submit" class="btn btn-primary btn-xs j-submit">保存</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('#my-form').superForm();
    });
</script>
