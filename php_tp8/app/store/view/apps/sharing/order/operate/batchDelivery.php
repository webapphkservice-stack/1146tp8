<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post" enctype="multipart/form-data">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">批量发货</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require"> 导入发货模板 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="block">
                                        <button type="button" class="upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择模板
                                        </button>
                                        <input id="doc-form-file" name="iFile" type="file" multiple="multiple" required>
                                    </div>
                                    <div class="file-list mt-4-xs"></div>
                                    <div class="help-block mt-4-xs">
                                        <small><a href="<?= url('order.operate/deliveryTpl') ?>">默认模板格式下载</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 form-require">物流公司 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="order[express_id]" required class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($express_list)): foreach ($express_list as $expres): ?>
                                            <option value="<?= $expres['express_id'] ?>">
                                                <?= $expres['express_name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block mt-4-xs">
                                        <small>可在 <a href="<?= url('setting.express/index') ?>"
                                                     target="_blank">物流公司列表</a>
                                            中设置
                                        </small>
                                    </div>
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
         * 文件列表
         */
        $('#doc-form-file').on('change', function () {
            var fileNames = '';
            $.each(this.files, function () {
                fileNames += '<span class="badge badge-success">' + this.name + '</span> ';
            });
            $('.file-list').html(fileNames);
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
