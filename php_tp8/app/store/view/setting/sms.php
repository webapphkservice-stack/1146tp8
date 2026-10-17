<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">短信通知（阿里云短信）</div>
                            </div>
                            <input type="hidden" name="sms[default]" value="aliyun">
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> AccessKeyId </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[engine][aliyun][AccessKeyId]"
                                           value="<?= $values['engine']['aliyun']['AccessKeyId'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> AccessKeySecret </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input"
                                           name="sms[engine][aliyun][AccessKeySecret]"
                                           value="<?= $values['engine']['aliyun']['AccessKeySecret'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 短信签名 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[engine][aliyun][sign]"
                                           value="<?= $values['engine']['aliyun']['sign'] ?>" required>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">注册/登录短信验证码</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    是否开启短信提醒
                                </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="sms[engine][aliyun][register][is_enable]" value="1"
                                            <?= $values['engine']['aliyun']['register']['is_enable'] == '1' ? 'checked' : '' ?>
                                               required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="sms[engine][aliyun][register][is_enable]" value="0"
                                            <?= $values['engine']['aliyun']['register']['is_enable'] == '0' ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    模板ID <span class="tpl-form-line-small-title">Template Code</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input"
                                           name="sms[engine][aliyun][register][template_code]"
                                           value="<?= $values['engine']['aliyun']['register']['template_code'] ?>" required>
                                    <small>必填。阿里云短信模板 CODE，例如：SMS_139800030</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                    测试接收手机号
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input"
                                           name="sms[engine][aliyun][register][accept_phone]"
                                           value="<?= $values['engine']['aliyun']['register']['accept_phone'] ?>">
                                    <small>仅用于点击「发送测试短信」时接收，真实用户验证码使用用户自行填写的手机号</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%]">
                                    <small>模板内容需包含变量 ${code}（验证码），例如：您的验证码为 ${code}，请勿泄露。</small>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">阿里云 ChatApp（WhatsApp 验证码）</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> AccessKeyId </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][accessKeyId]"
                                           value="<?= $values['chatapp']['accessKeyId'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> AccessKeySecret </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][accessKeySecret]"
                                           value="<?= $values['chatapp']['accessKeySecret'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 接入点 Endpoint </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][endpoint]"
                                           value="<?= $values['chatapp']['endpoint'] ?>">
                                    <small>默认 cams.ap-southeast-1.aliyuncs.com</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 区域 RegionId </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][regionId]"
                                           value="<?= $values['chatapp']['regionId'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 子空间ID CustSpaceId </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][custSpaceId]"
                                           value="<?= $values['chatapp']['custSpaceId'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 发送方号码 From </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][fromPhone]"
                                           value="<?= $values['chatapp']['fromPhone'] ?>">
                                    <div class="help-block">
                                        <small>WhatsApp 发送号码（含国家码，如 60123456789，不带 +）</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 验证码模板编码 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="sms[chatapp][verifyTemplateCode]"
                                           value="<?= $values['chatapp']['verifyTemplateCode'] ?>">
                                    <div class="help-block">
                                        <small>阿里云 ChatApp 中创建的验证码(authentication)模板编码</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 模板语言 </label>
                                <div class="sm:w-3/4">
                                    <select name="sms[chatapp][language]" class="form-input">
                                        <option value="zh_CN" <?= $values['chatapp']['language'] == 'zh_CN' ? 'selected' : '' ?>>中文 (zh_CN)</option>
                                        <option value="en_US" <?= $values['chatapp']['language'] == 'en_US' ? 'selected' : '' ?>>English (en_US)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 验证码有效期(秒) </label>
                                <div class="sm:w-3/4">
                                    <input type="number" class="tpl-form-input" name="sms[chatapp][verifyExpire]"
                                           value="<?= $values['chatapp']['verifyExpire'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 发送间隔(秒) </label>
                                <div class="sm:w-3/4">
                                    <input type="number" class="tpl-form-input" name="sms[chatapp][verifyInterval]"
                                           value="<?= $values['chatapp']['verifyInterval'] ?>">
                                </div>
                            </div>
                        </fieldset>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                <button type="submit" class="j-submit btn btn-secondary">提交
                                </button>
                            </div>
                        </div>
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

        /**
         * 发送测试短信
         */
        $('.j-sendTestMsg').click(function () {
            var msgType = $(this).data('msg-type')
                , formData = {
                AccessKeyId: $('input[name="sms[engine][aliyun][AccessKeyId]"]').val()
                , AccessKeySecret: $('input[name="sms[engine][aliyun][AccessKeySecret]"]').val()
                , sign: $('input[name="sms[engine][aliyun][sign]"]').val()
                , msg_type: msgType
                , template_code: $('input[name="sms[engine][aliyun][' + msgType + '][template_code]"]').val()
                , accept_phone: $('input[name="sms[engine][aliyun][' + msgType + '][accept_phone]"]').val()
            };
            if (!formData.AccessKeyId.length) {
                layer.msg('请填写 AccessKeyId');
                return false;
            }
            if (!formData.AccessKeySecret.length) {
                layer.msg('请填写 AccessKeySecret');
                return false;
            }
            if (!formData.sign.length) {
                layer.msg('请填写 短信签名');
                return false;
            }
            if (!formData.template_code.length) {
                layer.msg('请填写 模板ID');
                return false;
            }
            if (!formData.accept_phone.length) {
                layer.msg('请填写 接收手机号');
                return false;
            }
            layer.confirm('确定要发送测试短信吗', function (index) {
                var load = layer.load();
                var url = "<?= url('setting/smsTest') ?>";
                $.post(url, formData, function (result) {
                    layer.msg(result.msg);
                    layer.close(load);
                });
                layer.close(index);
            });
        });

    });
</script>
