<?php



?>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">商城设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 商城名称 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="store[name]"
                                           value="<?= $values['name'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 配送方式 </label>
                                <div class="sm:w-3/4">
                                    <?php foreach (app\common\enum\DeliveryType::data() as $item): ?>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" name="store[delivery_type][]"
                                                   value="<?= $item['value'] ?>"
                                                <?= in_array($item['value'], $values['delivery_type']) ? 'checked' : '' ?>>
                                            <?= $item['name'] ?>
                                        </label>
                                    <?php endforeach; ?>
                                    <div class="help-block">
                                        <small>注：配送方式至少选择一个</small>
                                    </div>
                                </div>
                            </div>
                            <div class="widget-head">
                                <div class="widget-title"> 物流查询API</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 快递100 Customer </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][customer]"
                                           value="<?= isset($values['kuaidi100']['customer']) ? $values['kuaidi100']['customer'] : '' ?>">
                                    <small>用于查询物流信息，<a href="https://www.kuaidi100.com/openapi/"
                                                       target="_blank">快递100申请</a></small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 快递100 Key </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="store[kuaidi100][key]"
                                           value="<?= isset($values['kuaidi100']['key']) ? $values['kuaidi100']['key'] : '' ?>">
                                </div>
                            </div>
                            <div class="widget-head">
                                <div class="widget-title"> 备案信息</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 备案文本 </label>
                                <div class="sm:w-3/4">
                                    <input type="text" class="tpl-form-input" name="store[icp_text]"
                                           value="<?= isset($values['icp_text']) ? $values['icp_text'] : '' ?>"
                                           placeholder="请输入备案信息，如：沪ICP备12345678号">
                                    <small>将在个人中心底部显示备案信息</small>
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
