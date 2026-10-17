<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 新增砍价活动</div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 选择商品 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="block mt-4-xs">
                                        <button type="button"
                                                class="j-selectGoods upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择商品
                                        </button>
                                        <div class="widget-goods-list uploader-list ">
                                        </div>
                                    </div>
                                    <div class="help-block">
                                        <small>注：砍价活动仅支持单规格商品 或 同价的多规格商品</small>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动时间 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <!-- 会员等级列表-->
                                    <div class="input-group">
                                        <input type="text" class="j-laydate-start form-input"
                                               name="active[start_time]"
                                               placeholder="开始时间">
                                        <span class="input-group-label input-group-label__center">至</span>
                                        <input type="text" class="j-laydate-end form-input"
                                               name="active[end_time]"
                                               placeholder="结束时间">
                                    </div>
                                    <div class="help-block">
                                        <small>砍价活动的开始时间与截止时间</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 砍价有效期 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="1" class="tpl-form-input" name="active[expiryt_time]"
                                           value="24" placeholder="" required>
                                    <small>自用户发起砍价到砍价截止的时间，单位：小时</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 砍价底价 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0.01" class="tpl-form-input" name="active[floor_price]"
                                           placeholder="" required>
                                    <small>砍价商品的最低价格，单位：元</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 帮砍人数 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="1" class="tpl-form-input" name="active[peoples]"
                                           placeholder="" required>
                                    <small>每个砍价订单的帮砍人数，达到该人数才可砍至底价</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 可自砍一刀 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_self_cut]" value="1" checked>
                                        允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_self_cut]" value="0">
                                        不允许
                                    </label>
                                    <div class="help-block">
                                        <small>砍价发起人自己砍一刀</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 必须底价购买 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_floor_buy]" value="1">
                                        是
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_floor_buy]" value="0"
                                               checked>
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small>只有砍到底价才可以购买</small>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 初始销量 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0" class="tpl-form-input" name="active[initial_sales]"
                                           required>
                                    <small>注：前台展示的销量 = 初始销量 + 实际销量</small>
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 分享标题 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="active[share_title]"
                                           value="麻烦帮我砍一刀！我真的很想要了，爱你哟！(๑′ᴗ‵๑)" required>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> 砍价助力语 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="active[prompt_words]"
                                           value="&#34;朋友一生一起走，帮砍一刀有没有&#34;">
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动状态 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[status]" value="1"
                                               checked>
                                        启用
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[status]" value="0">
                                        禁用
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0" class="tpl-form-input" name="active[sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-sm btn-secondary"> 提交
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

<!-- 商品列表 -->
<script id="tpl-goods-list-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.image }}" title="{{ $value.goods_name }}" target="_blank">
            <img src="{{ $value.image }}">
        </a>
        <input type="hidden" name="active[goods_id]" value="{{ $value.goods_id }}">
    </div>
    {{ /each }}
</script>

<script src="assets/common/plugins/laydate/laydate.js"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 选择商品
        var $goodsList = $('.widget-goods-list');
        $('.j-selectGoods').selectData({
            title: '选择商品',
            uri: 'goods/lists',
            dataIndex: 'goods_id',
            done: function (data) {
                data = [data[0]];
                var $html = $(template('tpl-goods-list-item', data));
                $goodsList.html($html);
            }
        });

        // 时间选择器
        laydate.render({
            elem: '.j-laydate-start'
            , type: 'datetime'
        });

        // $('.j-laydate-start').blur()
        // $activeTimeInput.blur()


        // 时间选择器
        laydate.render({
            elem: '.j-laydate-end'
            , type: 'datetime'
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
