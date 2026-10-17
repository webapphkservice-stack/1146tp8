<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title"><?= $model ? '编辑' : '新增' ?>外送</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form"
                          action="<?= $model ? url('delivery/edit', ['delivery_id' => $model['delivery_id']]) : url('delivery/add') ?>"
                          method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">名称 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="delivery[title]"
                                       value="<?= $model ? $model['title'] : '' ?>" placeholder="如：吉隆坡本地配送">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">封面图 </label>
                            <div class="sm:w-3/4">
                                <div class="block">
                                    <button type="button" class="upload-file btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="uploader-list ">
                                        <?php if ($model && $model['image']): ?>
                                            <div class="file-item">
                                                <img src="<?= $model['image']['file_path'] ?>">
                                                <input type="hidden" name="delivery[image_id]" value="<?= $model['image']['file_id'] ?>">
                                                <i class="iconfont icon-shanchu file-item-delete"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">适用国家 </label>
                            <div class="sm:w-3/4">
                                <select name="delivery[country]" class="form-select">
                                    <?php $country = $model ? $model['country'] : 'MY'; ?>
                                    <option value="MY" <?= $country == 'MY' ? 'selected' : '' ?>>马来西亚</option>
                                    <option value="CN" <?= $country == 'CN' ? 'selected' : '' ?>>中国大陆</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">所属店铺 </label>
                            <div class="sm:w-3/4">
                                <select name="delivery[shop_id]" class="form-select">
                                    <option value="0">未归属</option>
                                    <?php foreach ($shops as $sid => $sname): ?>
                                        <option value="<?= $sid ?>" <?= ($model && $model['shop_id'] == $sid) ? 'selected' : '' ?>><?= $sname ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small>外送商品归属于某个店铺（店铺为主，外送商品是店铺的功能）</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">描述 </label>
                            <div class="sm:w-3/4">
                                <textarea name="delivery[description]" rows="3" style="width:100%"><?= $model ? $model['description'] : '' ?></textarea>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">配送范围 </label>
                            <div class="sm:w-3/4">
                                <?php
                                    $klRegions = \app\common\model\LocalDelivery::getKlRegions();
                                    $rangeValue = $model ? $model['delivery_range'] : '';
                                    $checkedRegions = $rangeValue !== '' ? explode('、', $rangeValue) : [];
                                ?>
                                <input type="hidden" name="delivery[delivery_range]" id="delivery-range-value"
                                       value="<?= htmlspecialchars($rangeValue) ?>">
                                <div style="margin-bottom:8px">
                                    <a href="javascript:;" id="j-range-all">全选</a>
                                    &nbsp;|&nbsp;
                                    <a href="javascript:;" id="j-range-clear">清空</a>
                                </div>
                                <div style="max-height:220px;overflow-y:auto;border:1px solid #e5e5e5;padding:8px;border-radius:4px">
                                    <?php foreach ($klRegions as $region): ?>
                                        <label style="display:inline-block;width:32%;min-width:180px;margin:3px 0;font-weight:normal">
                                            <input type="checkbox" class="j-range-item" value="<?= $region ?>"
                                                <?= in_array($region, $checkedRegions) ? 'checked' : '' ?>> <?= $region ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <small>勾选可配送的吉隆坡区域（与小程序收货地址城市列表一致），展示在小程序外送卡片上</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">参考价格 </label>
                            <div class="sm:w-3/4">
                                <input type="number" step="0.01" name="delivery[price]" value="<?= $model ? $model['price'] : 0 ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">起送价 </label>
                            <div class="sm:w-3/4">
                                <input type="number" step="0.01" name="delivery[min_order]" value="<?= $model ? $model['min_order'] : 0 ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">配送费 </label>
                            <div class="sm:w-3/4">
                                <input type="number" step="0.01" name="delivery[delivery_fee]" value="<?= $model ? $model['delivery_fee'] : 0 ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">排序 </label>
                            <div class="sm:w-3/4">
                                <input type="number" name="delivery[sort]" value="<?= $model ? $model['sort'] : 0 ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">状态 </label>
                            <div class="sm:w-3/4">
                                <select name="delivery[status]" class="form-select">
                                    <?php $status = $model ? $model['status'] : 1; ?>
                                    <option value="1" <?= $status == 1 ? 'selected' : '' ?>>启用</option>
                                    <option value="0" <?= $status == 0 ? 'selected' : '' ?>>禁用</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商户侧回写地址 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="delivery[notify_url]"
                                       value="<?= $model ? (isset($model['notify_url']) ? $model['notify_url'] : '') : '' ?>"
                                       placeholder="https://商户系统域名/delivery/notify">
                                <small>用户确认收货后，本系统会向该地址 POST 订单状态，保持两端状态一致（留空则不回写）</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%]">
                                <button type="submit" class="btn btn-primary btn-xs j-submit">保存</button>
                            </div>
                        </div>
                    </form>
                </div>
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
        // 选择图片（项目标准文件库组件）
        $('.upload-file').selectImages({
            name: 'delivery[image_id]'
        });

        // 配送范围：勾选项实时同步到隐藏字段（顿号分隔），随表单提交
        function syncRange() {
            var arr = [];
            $('.j-range-item:checked').each(function () {
                arr.push($(this).val());
            });
            $('#delivery-range-value').val(arr.join('、'));
        }
        $('.j-range-item').on('change', syncRange);
        $('#j-range-all').on('click', function () {
            $('.j-range-item').prop('checked', true);
            syncRange();
        });
        $('#j-range-clear').on('click', function () {
            $('.j-range-item').prop('checked', false);
            syncRange();
        });

        $('#my-form').superForm();
    });
</script>
