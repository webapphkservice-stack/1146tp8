<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑会员等级</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 等级名称 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="grade[name]"
                                           value="<?= $model['name'] ?>" placeholder="请输入等级名称" required>
                                    <small>例如：大众会员、黄金会员、铂金会员、钻石会员</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 等级权重 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="x-region-select">
                                        <select name="grade[weight]" style="width: 20rem;" required class="form-select">
                                            <option value="">请选择等级权重</option>
                                            <?php for ($i = 1; $i <= 50; $i++): ?>
                                                <option value="<?= $i ?>" <?= $model['weight'] == $i ? 'selected' : '' ?>>
                                                    <?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="help-block">
                                        <small>会员等级的权重，数字越大 等级越高</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 升级条件 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <span class="input-group-label input-group-label__left">实际消费金额满</span>
                                        <input type="number" name="grade[upgrade][expend_money]"
                                               value="<?= $model['upgrade']['expend_money'] ?>"
                                               class="form-input" min="0.01" required>
                                        <span class="widget-dealer__unit input-group-label input-group-label__right">元</span>
                                    </div>
                                    <div class="help-block">
                                        <small>用户的实际消费金额满n元后，自动升级</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 等级权益 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="input-group">
                                        <span class="input-group-label input-group-label__left">折扣率</span>
                                        <input type="number" class="form-input" name="grade[equity][discount]"
                                               value="<?= $model['equity']['discount'] ?>" max="10" min="0"
                                               required>
                                        <span class="widget-dealer__unit input-group-label input-group-label__right">折</span>
                                    </div>
                                    <div class="help-block">
                                        <small>折扣率范围0-10，9.5代表9.5折，0代表不折扣</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 等级状态 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="grade[status]" value="1"
                                            <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="grade[status]" value="0"
                                            <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
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
