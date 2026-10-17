<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">充值设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否允许用户充值 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_entrance]" value="1"
                                            <?= $values['is_entrance'] ? 'checked' : '' ?>> 允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_entrance]" value="0"
                                            <?= $values['is_entrance'] ? '' : 'checked' ?>> 不允许
                                    </label>
                                    <div class="help-block">
                                        <small>如设置不允许则用户端不显示充值按钮</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否允许自定义金额 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_custom]" value="1"
                                            <?= $values['is_custom'] ? 'checked' : '' ?>> 允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_custom]" value="0"
                                            <?= $values['is_custom'] ? '' : 'checked' ?>> 不允许
                                    </label>
                                    <div class="help-block">
                                        <small>是否允许用户填写自定义的充值金额</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 是否自动匹配套餐 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_match_plan]" value="1"
                                            <?= $values['is_match_plan'] ? 'checked' : '' ?>> 是
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[is_match_plan]" value="0"
                                            <?= $values['is_match_plan'] ? '' : 'checked' ?>> 否
                                    </label>
                                    <div class="help-block">
                                        <small>用户充值自定义金额是否自动匹配套餐，如不开启则不参与套餐金额赠送</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 充值说明 </label>
                                <div class="sm:w-3/4 ">
                                    <textarea rows="5" name="recharge[describe]"
                                              placeholder="请输入充值说明"><?= $values['describe'] ?></textarea>
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
