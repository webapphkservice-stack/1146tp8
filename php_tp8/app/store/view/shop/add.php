<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">添加门店</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 门店名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="shop[shop_name]"
                                           placeholder="请输入门店名称" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 门店logo </label>
                                <div class="sm:w-3/4 ">
                                    <div class="block">
                                        <div class="block">
                                            <button type="button"
                                                    class="upload-file btn btn-secondary rounded">
                                                <i class="icon-cloud-upload"></i> 选择图片
                                            </button>
                                            <div class="uploader-list ">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 联系人 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="shop[linkman]"
                                           placeholder="请输入门店联系人" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 联系电话 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="shop[phone]"
                                           placeholder="请输入门店联系电话" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 营业时间 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="shop[shop_hours]"
                                           placeholder="请输入门店营业时间" required>
                                    <small>例如：8:30-17:30</small>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 门店区域 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="x-region-select" data-region-selected>
                                        <select name="shop[province_id]" data-province required class="form-select">
                                            <option value="">请选择省份</option>
                                        </select>
                                        <select name="shop[city_id]" data-city required class="form-select">
                                            <option value="">请选择城市</option>
                                        </select>
                                        <select name="shop[region_id]" data-region required class="form-select">
                                            <option value="">请选择地区</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 详细地址 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="shop[address]"
                                           placeholder="请输入详细地址" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 门店坐标 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="block">
                                        <input type="text" style="background: none !important;" id="coordinate"
                                               class="tpl-form-input" name="shop[coordinate]"
                                               placeholder="请选择门店坐标" readonly="" required>
                                    </div>
                                    <div class="block pt-1">
                                        <iframe id="map" src="<?= url('shop/getpoint') ?>"
                                                width="915"
                                                height="610"></iframe>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> 门店简介 </label>
                                <div class="sm:w-3/4 ">
                                    <textarea class="border-green-500" rows="5" placeholder="请输入门店简介"
                                              name="shop[summary]"></textarea>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">门店排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="shop[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 自提核销 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="shop[is_check]" value="1"
                                               checked>
                                        支持
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="shop[is_check]" value="0">
                                        不支持
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 门店状态 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="shop[status]" value="1"
                                               checked>
                                        启用
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="shop[status]" value="0">
                                        禁用
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


<!-- 图片文件列表模板 -->
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/store/js/select.region.js?v=1.2"></script>
<script>
    /**
     * 设置坐标
     */
    function setCoordinate(value) {
        var $coordinate = $('#coordinate');
        $coordinate.val(value);
        // 触发验证
        $coordinate.trigger('change');
    }
</script>
<script>

    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'shop[logo_image_id]'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
