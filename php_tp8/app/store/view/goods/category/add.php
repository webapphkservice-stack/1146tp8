<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">添加商品分类</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">分类名称 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="text" class="tpl-form-input" name="category[name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">上级分类 </label>
                                <div class="sm:w-3/4 grow">
                                    <select name="category[parent_id]" class="form-select">
                                        <option value="0">顶级分类</option>
                                        <?php if (isset($list)): foreach ($list as $first): ?>
                                            <option value="<?= $first['category_id'] ?>">
                                                <?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">分类图片 </label>
                                <div class="sm:w-3/4 grow">
                                    <div class="block">
                                        <button type="button"
                                                class="upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">分类排序 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="number" class="tpl-form-input" name="category[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
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

<!-- 图片文件列表模板 -->
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'category[image_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
