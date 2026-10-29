<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>

                            <!-- ===== 微信小程序基础设置 ===== -->
                            <div class="widget-head">
                                <div class="widget-title">微信小程序设置</div>
                                <div class="text-xs text-gray-400 ml-2">用于获取 access_token / 用户 openid 等基础能力</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    AppID <span class="tpl-form-line-small-title">小程序ID</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="wxapp[app_id]"
                                           value="<?= $model['app_id'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">
                                    AppSecret <span class="tpl-form-line-small-title">小程序密钥</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="password" class="tpl-form-input" name="wxapp[app_secret]"
                                           value="<?= $model['app_secret'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                    微信小程序url地址 <span class="tpl-form-line-small-title">weixin://协议打开小程序</span>
                                </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="wxapp[weixin_url]"
                                           value="<?= $model['weixin_url'] ?>"
                                           placeholder="weixin://dl/business/?appid=xxx&path=pages/index/index&env_version=release">
                                    <small>用于 PC 顶部「微信」入口跳转微信小程序，格式示例：weixin://dl/business/?appid=xxx&path=pages/index/index&env_version=release</small>
                                </div>
                            </div>

                            <!-- ===== 支付配置 Tab（微信 / 支付宝）===== -->
                            <div class="widget-head mt-6">
                                <div class="widget-title">支付配置</div>
                                <div class="text-xs text-gray-400 ml-2">微信、支付宝两套独立参数，点击下方选项卡切换</div>
                            </div>

                            <div class="js-tabs">
                                <!-- Tab 导航 -->
                                <ul class="flex border-b border-gray-200 -mb-px">
                                    <li class="mr-2">
                                        <a href="javascript:;" data-target="#tab-wechat-pay"
                                           class="inline-block px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                                            微信支付
                                        </a>
                                    </li>
                                    <li class="mr-2">
                                        <a href="javascript:;" data-target="#tab-alipay-pay"
                                           class="inline-block px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                                            支付宝支付
                                        </a>
                                    </li>
                                </ul>

                                <!-- ===== Tab 1：微信支付配置 ===== -->
                                <div id="tab-wechat-pay" class="js-tab-panel pt-4">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 form-require">
                                            商户号 MCHID <span class="tpl-form-line-small-title">微信支付商户号</span>
                                        </label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" name="wxapp[mchid]"
                                                   value="<?= $model['mchid'] ?>" placeholder="例如：1234567890">
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 form-require">
                                            支付密钥 APIKEY <span class="tpl-form-line-small-title">V2 MD5 签名密钥</span>
                                        </label>
                                        <div class="sm:w-3/4">
                                            <input type="password" class="tpl-form-input" name="wxapp[apikey]"
                                                   value="<?= $model['apikey'] ?>" placeholder="32 位字母数字">
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            apiclient_cert.pem
                                        </label>
                                        <div class="sm:w-3/4">
                                             <textarea rows="6" name="wxapp[cert_pem]"
                                                       placeholder="使用文本编辑器打开 apiclient_cert.pem 文件，将全部内容复制进来"><?= $model['cert_pem'] ?></textarea>
                                            <small>V2 退款等敏感操作需要证书；V3 模式下可留空</small>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            apiclient_key.pem
                                        </label>
                                        <div class="sm:w-3/4">
                                             <textarea rows="6" name="wxapp[key_pem]"
                                                       placeholder="使用文本编辑器打开 apiclient_key.pem 文件，将全部内容复制进来"><?= $model['key_pem'] ?></textarea>
                                            <small>与 cert_pem 配对的私钥</small>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            异步通知地址
                                        </label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" readonly
                                                   value="<?= base_url() . 'index.php?s=/api/notify/order' ?>">
                                            <small>系统自动生成，微信支付成功后会回调此地址；请确认公网可访问</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- ===== Tab 2：支付宝支付配置 ===== -->
                                <div id="tab-alipay-pay" class="js-tab-panel hidden pt-4">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            支付宝 AppID <span class="tpl-form-line-small-title">小程序/应用ID</span>
                                        </label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" name="wxapp[alipay_app_id]"
                                                   value="<?= $model['alipay_app_id'] ?>" placeholder="例如：2024XXXXXXXXXXXX">
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            签名类型
                                        </label>
                                        <div class="sm:w-3/4">
                                            <select name="wxapp[alipay_sign_type]" class="tpl-form-input">
                                                <option value="RSA2" <?= $model['alipay_sign_type'] == 'RSA2' ? 'selected' : '' ?>>RSA2</option>
                                            </select>
                                            <small>目前仅支持 RSA2</small>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            应用私钥
                                        </label>
                                        <div class="sm:w-3/4">
                                             <textarea rows="6" name="wxapp[alipay_private_key]"
                                                       placeholder="使用文本编辑器打开应用私钥文件，或将支付宝开放平台生成的应用私钥复制进来"><?= $model['alipay_private_key'] ?></textarea>
                                            <small>用于发起支付请求时生成签名</small>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            支付宝公钥
                                        </label>
                                        <div class="sm:w-3/4">
                                             <textarea rows="6" name="wxapp[alipay_public_key]"
                                                       placeholder="使用文本编辑器打开支付宝公钥文件，或将支付宝开放平台生成的支付宝公钥复制进来"><?= $model['alipay_public_key'] ?></textarea>
                                            <small>用于验证支付宝异步通知及接口返回签名</small>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                            异步通知地址
                                        </label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" readonly
                                                   value="<?= base_url() . 'index.php?s=/api/NotifyAlipay/order' ?>">
                                            <small>系统自动生成并在下单时传给支付宝，无需在开放平台单独配置；请确认公网可访问</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary">提交保存</button>
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
