<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">外送设置</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('delivery/setting') ?>" method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">启用外送 </label>
                            <div class="sm:w-3/4">
                                <select name="delivery[enable]" class="form-select">
                                    <?php $enable = isset($values['enable']) ? $values['enable'] : 1; ?>
                                    <option value="1" <?= $enable == 1 ? 'selected' : '' ?>>开启</option>
                                    <option value="0" <?= $enable == 0 ? 'selected' : '' ?>>关闭</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">默认国家 </label>
                            <div class="sm:w-3/4">
                                <select name="delivery[country]" class="form-select">
                                    <?php $country = isset($values['country']) ? $values['country'] : 'MY'; ?>
                                    <option value="MY" <?= $country == 'MY' ? 'selected' : '' ?>>马来西亚</option>
                                    <option value="CN" <?= $country == 'CN' ? 'selected' : '' ?>>中国大陆</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">提示文案 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="delivery[notice]"
                                       value="<?= isset($values['notice']) ? $values['notice'] : '' ?>" placeholder="如：仅马来西亚收货地址可使用本地外送服务">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商户回写密钥 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="delivery[webhook_secret]"
                                       value="<?= isset($values['webhook_secret']) ? $values['webhook_secret'] : '' ?>"
                                       placeholder="用于校验商户侧 webhook 回写签名 sign">
                                <small>回写商户系统时携带的 sign = md5(排序参数 + 该密钥)，商户侧据此校验来源合法性</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
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
