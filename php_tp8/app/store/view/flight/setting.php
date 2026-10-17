<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">航班接口配置（Bright Data）</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('flight/setting') ?>" method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">启用接口 </label>
                            <div class="sm:w-3/4">
                                <select name="brightdata[enable]" class="form-select">
                                    <?php $enable = isset($values['enable']) ? $values['enable'] : 1; ?>
                                    <option value="1" <?= $enable == 1 ? 'selected' : '' ?>>开启（无缓存时调用接口）</option>
                                    <option value="0" <?= $enable == 0 ? 'selected' : '' ?>>关闭（仅使用后台默认航班）</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">API Key </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="brightdata[api_key]"
                                       value="<?= isset($values['api_key']) ? $values['api_key'] : '' ?>" placeholder="Bright Data API Token">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">Zone 名称 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="brightdata[zone]"
                                       value="<?= isset($values['zone']) ? $values['zone'] : '' ?>" placeholder="Bright Data 后台创建的 Zone 名称">
                                <small>在 Bright Data 后台“代理区/Zones”中创建，类型为 SERP API</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">货币 </label>
                            <div class="sm:w-3/4">
                                <input type="text" name="brightdata[currency]"
                                       value="<?= isset($values['currency']) ? $values['currency'] : 'CNY' ?>" placeholder="CNY / USD">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">国家(gl) </label>
                            <div class="sm:w-3/4">
                                <input type="text" name="brightdata[country]"
                                       value="<?= isset($values['country']) ? $values['country'] : 'cn' ?>" placeholder="cn / us">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">语言(hl) </label>
                            <div class="sm:w-3/4">
                                <input type="text" name="brightdata[lang]"
                                       value="<?= isset($values['lang']) ? $values['lang'] : 'zh-CN' ?>" placeholder="zh-CN / en">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">缓存时长(小时) </label>
                            <div class="sm:w-3/4">
                                <input type="number" name="brightdata[expire_hours]"
                                       value="<?= isset($values['expire_hours']) ? $values['expire_hours'] : 168 ?>">
                                <small>同一航线在该时间内复用结果，减少接口额度消耗</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">请求超时(秒) </label>
                            <div class="sm:w-3/4">
                                <input type="number" name="brightdata[timeout]"
                                       value="<?= isset($values['timeout']) ? $values['timeout'] : 30 ?>">
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
