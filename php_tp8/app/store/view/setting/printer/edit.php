<?php

use app\common\enum\PrinterType as PrinterTypeEnum;

?>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑小票打印机</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印机名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="printer[printer_name]"
                                           value="<?= $model['printer_name'] ?>" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印机类型 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="printer[printer_type]" class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($printerType)): foreach ($printerType as $key => $name): ?>
                                            <option value="<?= $key ?>"
                                                <?= $model['printer_type']['value'] == $key ? 'selected' : '' ?>>
                                                <?= $name ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>目前支持 飞鹅打印机、365云打印</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 打印机配置：飞鹅打印机 -->
                            <div id="<?= PrinterTypeEnum::FEI_E_YUN ?>"
                                 class="form-tab-group <?= PrinterTypeEnum::FEI_E_YUN == $model['printer_type']['value'] ? 'active' : '' ?>">
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> USER </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input"
                                               name="printer[<?= PrinterTypeEnum::FEI_E_YUN ?>][USER]"
                                               value="<?= isset($model['printer_config']['USER']) ? $model['printer_config']['USER'] : '' ?>">
                                        <small>飞鹅云后台注册用户名</small>
                                    </div>
                                </div>
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> UKEY </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input"
                                               name="printer[<?= PrinterTypeEnum::FEI_E_YUN ?>][UKEY]"
                                               value="<?= isset($model['printer_config']['UKEY']) ? $model['printer_config']['UKEY'] : '' ?>">
                                        <small>飞鹅云后台登录生成的UKEY</small>
                                    </div>
                                </div>
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印机编号 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input"
                                               name="printer[<?= PrinterTypeEnum::FEI_E_YUN ?>][SN]"
                                               value="<?= isset($model['printer_config']['SN']) ? $model['printer_config']['SN'] : '' ?>">
                                        <small>打印机编号为9位数字，查看飞鹅打印机底部贴纸上面的编号</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 打印机配置：365云打印 -->
                            <div id="<?= PrinterTypeEnum::PRINT_CENTER ?>"
                                 class="form-tab-group <?= PrinterTypeEnum::PRINT_CENTER == $model['printer_type']['value'] ? 'active' : '' ?>">
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印机编号 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input"
                                               name="printer[<?= PrinterTypeEnum::PRINT_CENTER ?>][deviceNo]"
                                               value="<?= isset($model['printer_config']['deviceNo']) ? $model['printer_config']['deviceNo'] : '' ?>">
                                    </div>
                                </div>
                                <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印机秘钥 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input"
                                               name="printer[<?= PrinterTypeEnum::PRINT_CENTER ?>][key]"
                                               value="<?= isset($model['printer_config']['key']) ? $model['printer_config']['key'] : '' ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 打印联数 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="printer[print_times]"
                                           value="<?= $model['print_times'] ?>"
                                           required>
                                    <small>同一订单，打印的次数</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="printer[sort]"
                                           value="<?= $model['sort'] ?>"
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

        // 切换打印机类型
        $("select[name='printer[printer_type]']").on('change', function (e) {
            $('.form-tab-group').removeClass('active');
            $('#' + e.currentTarget.value).addClass('active');
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
