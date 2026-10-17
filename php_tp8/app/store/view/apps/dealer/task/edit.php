<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">编辑每日任务</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" method="post">
                        <input type="hidden" name="task[task_id]" value="<?= $model['task_id'] ?>">

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">任务名称 </label>
                            <div class="sm:w-3/4 ">
                                <input type="text" class="tpl-form-input" name="task[task_name]"
                                       value="<?= $model['task_name'] ?>" placeholder="请输入任务名称" required>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">任务图标 </label>
                            <div class="sm:w-3/4 ">
                                <div class="block">
                                    <button type="button"
                                            class="upload-file btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="help-block">
                                        <small>建议尺寸：80×80像素</small>
                                    </div>
                                </div>
                                <div class="file-list">
                                    <?php if (!empty($model['task_icon'])): ?>
                                        <div class="file-item">
                                            <img src="<?= $model['task_icon'] ?>" alt="">
                                            <input type="hidden" name="task[task_icon]" value="<?= $model['task_icon'] ?>">
                                            <i class="iconfont icon-shanchu file-item-delete"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">任务类型 </label>
                            <div class="sm:w-3/4 ">
                                <select name="task[task_type]" required class="form-select">
                                    <option value="1" <?= $model['task_type'] == 1 ? 'selected' : '' ?>>每日签到</option>
                                    <option value="2" <?= $model['task_type'] == 2 ? 'selected' : '' ?>>分享商品</option>
                                    <option value="3" <?= $model['task_type'] == 3 ? 'selected' : '' ?>>浏览商品</option>
                                    <option value="4" <?= $model['task_type'] == 4 ? 'selected' : '' ?>>下单购买</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">奖励积分 </label>
                            <div class="sm:w-3/4 ">
                                <input type="number" class="tpl-form-input" name="task[points]"
                                       value="<?= $model['points'] ?>" placeholder="请输入奖励积分" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">排序 </label>
                            <div class="sm:w-3/4 ">
                                <input type="number" class="tpl-form-input" name="task[sort]"
                                       value="<?= $model['sort'] ?>" placeholder="数字越小越靠前" min="0">
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">状态 </label>
                            <div class="sm:w-3/4 ">
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="task[is_enable]" value="1"
                                        <?= $model['is_enable'] == 1 ? 'checked' : '' ?>>
                                    启用
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="task[is_enable]" value="0"
                                        <?= $model['is_enable'] == 0 ? 'checked' : '' ?>>
                                    禁用
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                <button type="submit" class="j-submit btn btn-secondary">提交保存</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/store/js/file.library.js"></script>
<script>
    $(function () {
        // 图片上传
        $('.upload-file').selectImages({
            name: 'task[task_icon]',
            multiple: false
        });

        // 删除图片
        $('.file-list').on('click', '.file-item-delete', function () {
            $(this).parent().remove();
        });

        // 表单提交
        $('#my-form').superForm();
    });
</script>
