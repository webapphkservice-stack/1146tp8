<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">编辑转账场景 — <?= $scene['name'] ?>（<?= $scene['identifier'] ?>）</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('transfer/scene_edit', ['scene_id' => $scene['id']]) ?>" method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">场景 ID </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" value="<?= $scene['id'] ?>" disabled>
                                <small>微信支付定义的场景编号，不可修改</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">场景标识 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" value="<?= $scene['identifier'] ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">场景名称 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" value="<?= $scene['name'] ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">子场景描述 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" value="<?= $scene['sub_scene'] ?>" disabled>
                                <small>该场景下可使用的报告类型</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">启用状态 </label>
                            <div class="sm:w-3/4">
                                <select name="is_enable" class="form-select">
                                    <option value="1" <?= $scene['is_enable'] == 1 ? 'selected' : '' ?>>已启用</option>
                                    <option value="0" <?= $scene['is_enable'] == 0 ? 'selected' : '' ?>>已禁用</option>
                                </select>
                                <small>禁用后该场景将不会出现在转账场景选择中</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">备注信息 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="remark"
                                       value="<?= $scene['remark'] ?>" placeholder="可填写该场景的使用说明">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%]">
                                <button type="submit" class="btn btn-primary btn-xs j-submit">保存</button>
                                <a href="<?= url('transfer/scenes') ?>" class="btn btn-default btn-xs">返回</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('#my-form').superForm();
    });
</script>
