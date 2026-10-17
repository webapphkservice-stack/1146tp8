<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 砍价设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 是否支持分销 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="1"
                                            <?= ($values['is_dealer'] ?? '') == 1 ? 'checked' : '' ?> required>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="basic[is_dealer]" value="0" <?= ($values['is_dealer'] ?? '') == 0 ? 'checked' : '' ?>>
                                        关闭
                                    </label>
                                    <div class="help-block pt-1">
                                        <small>注：砍价订单是否参与分销</small>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 砍价规则 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <textarea class="border-green-500" rows="8" placeholder="请输入砍价规则"
                                              name="basic[bargain_rules]"><?= $values['bargain_rules'] ?? '' ?></textarea>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-sm btn-secondary">提交
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
