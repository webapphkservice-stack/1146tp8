<style>
    .img__category_style {
        width: 100%;
        box-shadow: 0 3px 10px #dcdcdc;
    }
</style>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">分类页模板</div>
                            </div>
                            <div class="wrapper mx-auto max-w-7xl px-4">
                                <div class="left-style w-full lg:w-1/3">
                                    <img class="img__category_style"
                                         src="assets/store/img/category_tpl/<?= $model['category_style'] ?>.png?v=<?= $version ?>">
                                </div>
                                <div class="right-form w-full lg:w-2/3">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 form-require"> 分类页样式 </label>
                                        <div class="sm:w-3/4">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="category[category_style]" value="10"
                                                    <?= $model['category_style'] == 10 ? 'checked' : '' ?>
                                                       required>
                                                一级分类 (大图)
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="category[category_style]" value="11"
                                                    <?= $model['category_style'] == 11 ? 'checked' : '' ?>>
                                                一级分类 (小图)
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" name="category[category_style]" value="20"
                                                    <?= $model['category_style'] == 20 ? 'checked' : '' ?>>
                                                二级分类
                                            </label>
                                            <div class="help__style help-block mt-4-xs">
                                                <small class="<?= $model['category_style'] == 10 ? '' : 'hide' ?>"
                                                       data-value="10">分类图尺寸：宽750像素 高度不限
                                                </small>
                                                <small class="<?= $model['category_style'] == 11 ? '' : 'hide' ?>"
                                                       data-value="11">分类图尺寸：宽188像素 高度不限
                                                </small>
                                                <small class="<?= $model['category_style'] == 20 ? '' : 'hide' ?>"
                                                       data-value="20">分类图尺寸：宽150像素 高150像素
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 form-require"> 分享标题 </label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" name="category[share_title]"
                                                   value="<?= $model['share_title'] ?>">
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                            <button type="submit" class="j-submit btn btn-secondary">提交
                                            </button>
                                        </div>
                                    </div>
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

        // 当前版本号
        var version = '<?= $version ?>';

        // 切换分类样式图
        var $imgCategorystyle = $('.img__category_style');
        var $helpStyleSmall = $('.help__style').find('small');
        $("input[name='category[category_style]']").change(function (e) {
            var styleValue = e.currentTarget.value;
            $helpStyleSmall.hide().filter('[data-value=' + styleValue + ']').show();
            $imgCategorystyle.attr('src', 'assets/store/img/category_tpl/' + styleValue + '.png?v=' + version);
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
