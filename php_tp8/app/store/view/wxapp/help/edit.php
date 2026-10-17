<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑帮助</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 标题 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="help[title]"
                                           value="<?= $model['title'] ?>" placeholder="请输入标题" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 内容 </label>
                                <div class="sm:w-3/4 ">
                                    <textarea class="" rows="6" placeholder="请输入帮助内容"
                                              name="help[content]"><?= $model['content'] ?></textarea>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="help[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                </div>
                            </div>

                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary"> 提交
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
