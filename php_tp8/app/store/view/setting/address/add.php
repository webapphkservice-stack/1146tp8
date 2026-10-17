<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">新增退货地址</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 收货人姓名 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="address[name]" value="" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 联系电话 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="address[phone]" value="" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 详细地址 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="address[detail]" value="" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="address[sort]" value="100"
                                           required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
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
