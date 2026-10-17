<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" enctype="multipart/form-data" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">添加优惠券</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">优惠券名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="coupon[name]"
                                           value="" placeholder="请输入优惠券名称" required>
                                    <small>例如：满100减10</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">优惠券颜色 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[color]" value="10" checked>
                                        蓝色
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[color]" value="20">
                                        红色
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[color]" value="30">
                                        紫色
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[color]" value="40">
                                        黄色
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2" data-x-switch>
                                <label class="sm:w-1/4 lg:w-1/6 form-require">优惠券类型 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[coupon_type]" value="10" checked
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__10">
                                        满减券
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[coupon_type]" value="20"
                                               data-switch-box="switch-coupon_type"
                                               data-switch-item="coupon_type__20">
                                        折扣券
                                    </label>
                                </div>
                            </div>
                            <div class="switch-coupon_type coupon_type__10">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">减免金额 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0.01" class="tpl-form-input" name="coupon[reduce_price]"
                                           value="" placeholder="请输入减免金额" required>
                                </div>
                            </div>
                            <div class="switch-coupon_type coupon_type__20 hide">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">折扣率 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" max="10" class="tpl-form-input"
                                           name="coupon[discount]"
                                           value="" placeholder="请输入折扣率" required>
                                    <small>折扣率范围0-10，9.5代表9.5折，0代表不折扣</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">最低消费金额 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="1" class="tpl-form-input" name="coupon[min_price]"
                                           value="" placeholder="请输入最低消费金额" required>
                                </div>
                            </div>

                            <div class="pt-4" data-x-switch>
                                <label class="sm:w-1/4 lg:w-1/6 form-require">适用范围 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[apply_range]" value="10"
                                               data-switch-box="switch-apply_range"
                                               data-switch-item="apply_range__10" checked>
                                        全部商品
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[apply_range]" value="20"
                                               data-switch-box="switch-apply_range"
                                               data-switch-item="apply_range__20">
                                        指定商品
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[apply_range]" value="30"
                                               data-switch-box="switch-apply_range"
                                               data-switch-item="apply_range__30">
                                        排除商品
                                    </label>
                                </div>
                            </div>
                            <div class="switch-apply_range apply_range__20 hide">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> </label>
                                <div class="sm:w-3/4 ">
                                    <div class="widget-become-goods block mt-4-xs">
                                        <button type="button" @click.stop="onSelectGoods"
                                                class="j-selectGoods upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择商品
                                        </button>
                                        <div class="widget-goods-list uploader-list ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="switch-apply_range apply_range__30 hide">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> </label>
                                <div class="sm:w-3/4 ">
                                    <div class="widget-become-goods block mt-4-xs">
                                        <button type="button" @click.stop="onSelectGoods"
                                                class="j-selectGoods2 upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择商品
                                        </button>
                                        <div class="widget-goods-list2 uploader-list ">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4" data-x-switch>
                                <label class="sm:w-1/4 lg:w-1/6 form-require">到期类型 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[expire_type]" value="10" checked
                                               data-switch-box="switch-expire_type"
                                               data-switch-item="expire_type__10">
                                        领取后生效
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="coupon[expire_type]" value="20"
                                               data-switch-box="switch-expire_type"
                                               data-switch-item="expire_type__20">
                                        固定时间
                                    </label>
                                </div>
                            </div>
                            <div class="switch-expire_type expire_type__10">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">有效天数 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="1" class="tpl-form-input" name="coupon[expire_day]"
                                           value="3" placeholder="请输入有效天数" required>
                                </div>
                            </div>
                            <div class="switch-expire_type expire_type__20 hide">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">时间范围 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="date" class="j-startTime form-input mb-3"
                                           name="coupon[start_time]" placeholder="请选择开始日期" required>
                                    <input type="date" class="j-endTime form-input" name="coupon[end_time]"
                                           placeholder="请选择结束日期" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">发放总数量 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="-1" class="tpl-form-input" name="coupon[total_num]"
                                           value="-1" required>
                                    <small>限制领取的优惠券数量，-1为不限制</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="coupon[sort]" value="100"
                                           required>
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

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<!-- 指定的 商品列表 -->
<script id="tpl-goods-list-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.image }}" title="{{ $value.goods_name }}" target="_blank">
            <img src="{{ $value.image }}">
        </a>
        <input type="hidden" name="coupon[apply_range_config][applyGoodsIds][]" value="{{ $value.goods_id }}">
        <i class="iconfont icon-shanchu file-item-delete" data-name="商品"></i>
    </div>
    {{ /each }}
</script>

<!-- 排除的 商品列表 -->
<script id="tpl-goods-list-item2" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.image }}" title="{{ $value.goods_name }}" target="_blank">
            <img src="{{ $value.image }}">
        </a>
        <input type="hidden" name="coupon[apply_range_config][excludedGoodsIds][]" value="{{ $value.goods_id }}">
        <i class="iconfont icon-shanchu file-item-delete" data-name="商品"></i>
    </div>
    {{ /each }}
</script>

<script src="assets/common/js/vue3.min.js?v=<?= $version ?>"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    /**
     * 时间选择（amazeui 已从项目移除，改用原生 date 控件，仅保留开始/结束联动）
     */
    $(function () {
        var $startTime = $('.j-startTime');
        var $endTime = $('.j-endTime');
        $startTime.on('change', function () {
            if (this.value) {
                $endTime.attr('min', this.value);
                if ($endTime.val() && $endTime.val() < this.value) {
                    $endTime.val(this.value);
                }
            }
        });
        if ($startTime.val()) {
            $endTime.attr('min', $startTime.val());
        }
    });
</script>

<script>
    $(function () {

        // swith切换
        var $mySwitch = $('[data-x-switch]');
        $mySwitch.find('[data-switch-item]').click(function () {
            var $mySwitchBox = $('.' + $(this).data('switch-box'));
            $mySwitchBox.hide().filter('.' + $(this).data('switch-item')).show();
        });

        // 选择商品
        var $goodsList = $('.widget-goods-list');
        $('.j-selectGoods').selectData({
            title: '选择商品',
            uri: 'goods/lists',
            dataIndex: 'goods_id',
            done: function (data) {
                var $html = $(template('tpl-goods-list-item', data));
                // 删除文件
                $html.find('.file-item-delete').on('click', function () {
                    $(this).parent().remove();
                });
                $goodsList.append($html);
            }
        });

        // 选择商品
        var $goodsList2 = $('.widget-goods-list2');
        $('.j-selectGoods2').selectData({
            title: '选择商品',
            uri: 'goods/lists',
            dataIndex: 'goods_id',
            done: function (data) {
                var $html = $(template('tpl-goods-list-item2', data));
                // 删除文件
                $html.find('.file-item-delete').on('click', function () {
                    $(this).parent().remove();
                });
                $goodsList2.append($html);
            }
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
