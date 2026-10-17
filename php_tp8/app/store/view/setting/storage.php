<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">文件上传设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                    默认上传方式
                                </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="storage[default]" value="local"
                                            <?= $values['default'] === 'local' ? 'checked' : '' ?>> 本地 (不推荐)
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="storage[default]" value="qiniu"
                                            <?= $values['default'] === 'qiniu' ? 'checked' : '' ?>> 七牛云存储
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="storage[default]" value="aliyun"
                                            <?= $values['default'] === 'aliyun' ? 'checked' : '' ?>> 阿里云OSS
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="storage[default]" value="qcloud"
                                            <?= $values['default'] === 'qcloud' ? 'checked' : '' ?>> 腾讯云COS
                                    </label>
                                </div>
                            </div>
                            <div id="qiniu"
                                 class="form-tab-group <?= $values['default'] === 'qiniu' ? 'active' : '' ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qiniu][bucket]"
                                               value="<?= $values['engine']['qiniu']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        ACCESS_KEY <span class="tpl-form-line-small-title">AK</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qiniu][access_key]"
                                               value="<?= $values['engine']['qiniu']['access_key'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        SECRET_KEY <span class="tpl-form-line-small-title">SK</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qiniu][secret_key]"
                                               value="<?= $values['engine']['qiniu']['secret_key'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qiniu][domain]"
                                               value="<?= $values['engine']['qiniu']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
                                    </div>
                                </div>
                            </div>
                            <div id="aliyun"
                                 class="form-tab-group <?= $values['default'] === 'aliyun' ? 'active' : '' ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input" name="storage[engine][aliyun][bucket]"
                                               value="<?= $values['engine']['aliyun']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> AccessKeyId </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][access_key_id]"
                                               value="<?= $values['engine']['aliyun']['access_key_id'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> AccessKeySecret </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][access_key_secret]"
                                               value="<?= $values['engine']['aliyun']['access_key_secret'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][aliyun][domain]"
                                               value="<?= $values['engine']['aliyun']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
                                    </div>
                                </div>
                            </div>
                            <div id="qcloud"
                                 class="form-tab-group <?= $values['default'] === 'qcloud' ? 'active' : '' ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        存储空间名称 <span class="tpl-form-line-small-title">Bucket</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qcloud][bucket]"
                                               value="<?= $values['engine']['qcloud']['bucket'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        所属地域 <span class="tpl-form-line-small-title">Region</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][region]"
                                               value="<?= $values['engine']['qcloud']['region'] ?>">
                                        <small>请填写地域简称，例如：ap-beijing、ap-hongkong、eu-frankfurt</small>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        SecretId
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][secret_id]"
                                               value="<?= $values['engine']['qcloud']['secret_id'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        SecretKey
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input"
                                               name="storage[engine][qcloud][secret_key]"
                                               value="<?= $values['engine']['qcloud']['secret_key'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                        空间域名 <span class="tpl-form-line-small-title">Domain</span>
                                    </label>
                                    <div class="sm:w-3/4">
                                        <input type="text" class="tpl-form-input" name="storage[engine][qcloud][domain]"
                                               value="<?= $values['engine']['qcloud']['domain'] ?>">
                                        <small>请补全http:// 或 https://，例如：http://static.cloud.com</small>
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

        // 切换默认上传方式
        $("input:radio[name='storage[default]']").change(function (e) {
            $('.form-tab-group').removeClass('active');
            $('#' + e.currentTarget.value).addClass('active');
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
