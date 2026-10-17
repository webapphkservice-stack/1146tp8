<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title"><?= $model ? '编辑' : '新增' ?>店铺</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form"
                          action="<?= $model ? url('delivery/shopEdit', ['shop_id' => $model['shop_id']]) : url('delivery/shopAdd') ?>"
                          method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 form-require">店名 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="shop[shop_name]"
                                       value="<?= $model ? $model['shop_name'] : '' ?>" placeholder="请输入店铺名称" required>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">店铺图片 </label>
                            <div class="sm:w-3/4">
                                <div class="block">
                                    <button type="button" class="upload-file btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="uploader-list ">
                                        <?php if ($model && $model['image']): ?>
                                            <div class="file-item">
                                                <img src="<?= $model['image']['file_path'] ?>">
                                                <input type="hidden" name="shop[image_id]" value="<?= $model['image']['file_id'] ?>">
                                                <i class="iconfont icon-shanchu file-item-delete"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <small>用于小程序端外送店铺展示</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 form-require">店铺地址 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="shop[address]"
                                       value="<?= $model ? $model['address'] : '' ?>" placeholder="请输入店铺地址" required>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">店铺WhatsApp </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="shop[whatsapp]"
                                       value="<?= $model ? $model['whatsapp'] : '' ?>" placeholder="如：60123456789（含国家区号）">
                                <small>用于小程序联系商家，点击可直接发起 WhatsApp 会话</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">营业时间 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="shop[business_hours]"
                                       value="<?= $model ? $model['business_hours'] : '' ?>" placeholder="如：每日 10:00-22:00">
                                <small>展示在小程序店铺信息中，告知顾客营业时段</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">接单功能 </label>
                            <div class="sm:w-3/4">
                                <select name="shop[accept_order]" class="select">
                                    <?php $accept = $model ? intval($model['accept_order']) : 1; ?>
                                    <option value="1" <?= $accept === 1 ? 'selected' : '' ?>>接单中</option>
                                    <option value="0" <?= $accept === 0 ? 'selected' : '' ?>>暂不接单</option>
                                </select>
                                <small>关闭后，小程序端该店铺商品仍可查看但不可下单</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">配送功能 </label>
                            <div class="sm:w-3/4">
                                <select name="shop[delivery_enabled]" class="select">
                                    <?php $delivery = $model ? intval($model['delivery_enabled']) : 1; ?>
                                    <option value="1" <?= $delivery === 1 ? 'selected' : '' ?>>开启配送</option>
                                    <option value="0" <?= $delivery === 0 ? 'selected' : '' ?>>仅到店自提</option>
                                </select>
                                <small>关闭后，小程序端该店铺商品标注「到店自提」，不可走外送下单</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">外送商品说明 </label>
                            <div class="sm:w-3/4">
                                <textarea name="shop[description]" rows="4" style="width:100%"
                                          placeholder="描述该店铺可提供的外送商品/经营范围，例如：现做糕点、生日蛋糕、咖啡等"><?= $model ? $model['description'] : '' ?></textarea>
                                <small>说明该店铺的外送商品是什么，展示在店铺信息中</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">排序 </label>
                            <div class="sm:w-3/4">
                                <input type="number" name="shop[sort]" value="<?= $model ? $model['sort'] : 0 ?>">
                                <small>数字越小越靠前</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">状态 </label>
                            <div class="sm:w-3/4">
                                <select name="shop[status]" class="form-select">
                                    <?php $status = $model ? $model['status'] : 1; ?>
                                    <option value="1" <?= $status == 1 ? 'selected' : '' ?>>启用</option>
                                    <option value="0" <?= $status == 0 ? 'selected' : '' ?>>禁用</option>
                                </select>
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
            name: 'shop[image_id]'
        });

        $('#my-form').superForm();
    });
</script>
