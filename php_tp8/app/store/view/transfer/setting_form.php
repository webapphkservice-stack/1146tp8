<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">转账接口配置（微信支付商家转账 V3）</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('transfer/setting') ?>" method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商户号 (mchid) </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="mchid"
                                       value="<?= isset($values['mchid']) ? $values['mchid'] : '' ?>" placeholder="微信支付商户号">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">API 证书序列号 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="serial_no"
                                       value="<?= isset($values['serial_no']) ? $values['serial_no'] : '' ?>" placeholder="商户 API 证书序列号">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">API v3 密钥 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="api_v3_key"
                                       value="<?= isset($values['api_v3_key']) ? $values['api_v3_key'] : '' ?>" placeholder="API v3 密钥（32位）">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商户私钥路径 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="private_key_path"
                                       value="<?= isset($values['private_key_path']) ? $values['private_key_path'] : '' ?>" placeholder="apiclient_key.pem 文件路径">
                                <small>如已配置在 cert 目录下，则填写相对于 source 目录的路径</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">平台证书路径 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="cert_path"
                                       value="<?= isset($values['cert_path']) ? $values['cert_path'] : '' ?>" placeholder="微信支付平台证书路径">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">默认转账场景 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="default_scene_id"
                                       value="<?= isset($values['default_scene_id']) ? $values['default_scene_id'] : '1000' ?>" placeholder="默认场景 ID">
                                <small>默认使用的转账场景 ID，1000 = 现金营销</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">单笔限额(元) </label>
                            <div class="sm:w-3/4">
                                <input type="number" class="tpl-form-input" name="max_amount"
                                       value="<?= isset($values['max_amount']) ? $values['max_amount'] : '' ?>" placeholder="留空则不限制">
                                <small>单笔转账最大金额，留空则不限制</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%]">
                                <button type="submit" class="btn btn-primary btn-xs j-submit">保存</button>
                                <a href="<?= url('transfer/scenes') ?>" class="btn btn-default btn-xs">返回场景管理</a>
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
