<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="get">
                    <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                    <input type="hidden" name="step" value="2">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">第一步：选择商品</div>
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
                                    <div class="help-block pt-4">
                                        <small>注：添加秒杀商品后，将不允许修改主商品的规格属性</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-5/6 sm:ml-[16.667%] mt-6">
                                    <button type="submit" class="j-submit btn btn-sm btn-secondary">确认并下一步
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
        <input type="hidden" name="goods_id" value="{{ $value.goods_id }}">
    </div>
    {{ /each }}
</script>

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

    });
</script>
