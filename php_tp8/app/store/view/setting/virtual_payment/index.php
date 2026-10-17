<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">虚拟支付配置</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    虚拟支付 AppKey <span class="tpl-form-line-small-title">app_key</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="virtual_payment[app_key]"
                                           value="<?= isset($values['app_key']) ? $values['app_key'] : '' ?>" required>
                                    <small>微信 MP 后台「虚拟支付 → 基本配置」获取，区别于 AppSecret</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    报价 ID <span class="tpl-form-line-small-title">offer_id</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="virtual_payment[offer_id]"
                                           value="<?= isset($values['offer_id']) ? $values['offer_id'] : '' ?>" required>
                                    <small>调起 wx.requestVirtualPayment 必填</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                    回调校验 Token <span class="tpl-form-line-small-title">token</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="virtual_payment[token]"
                                           value="<?= isset($values['token']) ? $values['token'] : '' ?>">
                                    <small>配置虚拟支付回调 URL 时与 MP 填写一致（Notify 回校验使用）</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    运行环境
                                </label>
                                <div class="sm:w-3/4">
                                    <select name="virtual_payment[env]" class="tpl-form-input" required>
                                        <option value="0" <?= (isset($values['env']) && $values['env'] == 0) ? 'selected' : '' ?>>正式环境</option>
                                        <option value="1" <?= (isset($values['env']) && $values['env'] == 1) ? 'selected' : '' ?>>沙箱环境</option>
                                    </select>
                                    <small>AppKey 需与所选环境匹配</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary">提交</button>
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
        $('#my-form').superForm();
    });
</script>
