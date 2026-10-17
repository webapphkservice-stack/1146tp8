<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">新增每日任务</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" method="post">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">任务名称 </label>
                            <div class="sm:w-3/4 ">
                                <input type="text" class="tpl-form-input" name="task[task_name]"
                                       placeholder="请输入任务名称" required>
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
                                <div class="file-list"></div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">任务类型 </label>
                            <div class="sm:w-3/4 ">
                                <select name="task[task_type]" required class="form-select">
                                    <option value="1">每日签到</option>
                                    <option value="2">分享商品</option>
                                    <option value="3">浏览商品</option>
                                    <option value="4">下单购买</option>
                                </select>
                                <div class="help-block">
                                    <small>支持的任务类型：</small><br>
                                    <small>1. 每日签到 - 用户每日签到可获得积分</small><br>
                                    <small>2. 分享商品 - 用户分享商品到朋友圈或好友可获得积分</small><br>
                                    <small>3. 浏览商品 - 用户浏览指定商品可获得积分</small><br>
                                    <small>4. 下单购买 - 用户下单购买商品可获得积分</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">奖励积分 </label>
                            <div class="sm:w-3/4 ">
                                <input type="number" class="tpl-form-input" name="task[points]"
                                       placeholder="请输入奖励积分" value="0" min="0" required>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">排序 </label>
                            <div class="sm:w-3/4 ">
                                <input type="number" class="tpl-form-input" name="task[sort]"
                                       placeholder="数字越小越靠前" value="0" min="0">
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">状态 </label>
                            <div class="sm:w-3/4 ">
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="task[is_enable]" value="1" checked>
                                    启用
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="task[is_enable]" value="0">
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

        // 表单提交
        $('#my-form').superForm();
    });
</script>
