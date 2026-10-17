<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">顶部导航设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 小程序标题 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="tabbar[wxapp_title]"
                                           value="<?= $model['wxapp_title'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">顶部导航文字颜色 </label>
                                <div class="sm:w-3/4">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tabbar[top_text_color]" value="10"
                                            <?= $model['top_text_color']['value'] == 10 ? 'checked' : '' ?>> 黑色
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="tabbar[top_text_color]" value="20"
                                            <?= $model['top_text_color']['value'] == 20 ? 'checked' : '' ?>> 白色
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">顶部导航背景色 </label>
                                <div class="sm:w-3/4 pt-1">
                                    <input type="color" class="tpl-form-input" name="tabbar[top_background_color]"
                                           value="<?= $model['top_background_color'] ?>" required>
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

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
