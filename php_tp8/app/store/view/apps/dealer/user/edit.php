<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 编辑分销商</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> 分销商ID </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12  text-sm text-gray-700">
                                    <span><?= $model['user_id'] ?></span>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> 微信头像 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12  text-sm text-gray-700">
                                    <img width="60" height="60" src="<?= $model['user']['avatarUrl'] ?>" alt="">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 姓名 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="model[real_name]"
                                           value="<?= $model['real_name'] ?>" placeholder="请输入分销商姓名" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 手机号 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="model[mobile]"
                                           value="<?= $model['mobile'] ?>" placeholder="请输入分销商手机号" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 成为时间 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12  text-sm text-gray-700">
                                    <span><?=  time_text($model['create_time'])  ?></span>
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
