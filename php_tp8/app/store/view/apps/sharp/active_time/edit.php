<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div id="app" v-cloak class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 编辑活动场次</div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动日期 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="text-sm text-gray-700"><?= $model['active']['active_date'] ?></div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动场次 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="text-sm text-gray-700"><?=  time_text($model['active_time'])  ?></div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 选择商品 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-2/3 ">
                                    <div class="block mt-4-xs">
                                        <button type="button"
                                                class="upload-file btn btn-secondary rounded"
                                                @click.stop="onSelectGoods">
                                            <i class="icon-cloud-upload"></i> 选择秒杀商品
                                        </button>
                                        <div v-if="goodsList.length > 0" class="widget-goods-list pt-4">
                                            <table width="100%"
                                                   class="table-base  tpl-table-black whitespace-nowrap">
                                                <thead>
                                                <tr>
                                                    <th>商品ID</th>
                                                    <th>商品图片</th>
                                                    <th>商品名称</th>
                                                    <th>操作</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr v-for="(item, index) in goodsList">
                                                    <td class="align-middle">
                                                        <input type="hidden" name="active[sharp_goods][]"
                                                               :value="item.goods_id">
                                                        {{ item.goods_id }}
                                                    </td>
                                                    <td class="align-middle">
                                                        <a :href="item.goods_image"
                                                           title="点击查看大图" target="_blank">
                                                            <img :src="item.goods_image"
                                                                 width="50" height="50" alt="商品图片">
                                                        </a>
                                                    </td>
                                                    <td class="align-middle">
                                                        <p class="item-title">{{ item.goods_name }}</p>
                                                    </td>
                                                    <td class="align-middle">
                                                        <a href="javascript:void(0);"
                                                           @click.stop="onDeleteGoods(index)">删除</a>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
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

<script src="assets/common/js/vue3.min.js?v=<?= $version ?>"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        Vue.createApp({
            data() {
                return {
                    // 商品列表
                    goodsList: <?= \app\common\library\helper::jsonEncode($goodsList) ?>
                };
            },
            methods: {
                // 选择商品
                onSelectGoods: function () {
                    var app = this;
                    $.selectData({
                        title: '选择商品',
                        uri: 'sharp.goods/lists?status=10',
                        duplicate: false,
                        dataIndex: 'goods_id',
                        done: function (data) {
                            data.forEach(function (item) {
                                app.goodsList.push(item);
                            });
                        },
                        getExistData: function () {
                            var goodsIds = [];
                            app.goodsList.forEach(function (item) {
                                goodsIds.push(item.goods_id);
                            });
                            return goodsIds;
                        }
                    });
                },
                // 删除商品
                onDeleteGoods: function (index) {
                    var app = this;
                    return app.goodsList.splice(index, 1);
                }
            }
        }).mount('#app');

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
