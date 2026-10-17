<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">小票打印设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 是否开启小票打印 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="printer[is_open]" value="1"
                                            <?= $values['is_open'] ? 'checked' : '' ?>> 开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="printer[is_open]" value="0"
                                            <?= $values['is_open'] ? '' : 'checked' ?>> 关闭
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 选择订单打印机 </label>
                                <div class="sm:w-3/4  p-1">
                                    <select name="printer[printer_id]" class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($printerList)): foreach ($printerList as $printer): ?>
                                            <option value="<?= $printer['printer_id'] ?>"
                                                <?= $values['printer_id'] == $printer['printer_id'] ? 'selected' : '' ?>>
                                                <?= $printer['printer_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 订单打印方式 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="checkbox" name="printer[order_status][]" value="20"
                                            <?= in_array('20', $values['order_status']) ? 'checked' : '' ?>>
                                        订单付款时
                                    </label>
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
