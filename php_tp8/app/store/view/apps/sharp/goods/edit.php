<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑秒杀商品</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 商品信息 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="goods-detail">
                                        <div class="goods-image">
                                            <img src="<?= $goods['image'][0]['file_path'] ?? '' ?>" alt="">
                                        </div>
                                        <div class="goods-info dis-flex flex-dir-column flex-x-center">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                            <p class="goods-title">ID：<?= $goods['goods_id'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品单规格 -->
                            <?php if ($goods['spec_type'] == 10): ?>
                                <div class="goods-spec-single"
                                     style="display: <?= $goods['spec_type'] == 10 ? 'block' : 'none' ?>;">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require"> 商品编码 </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                            <div class="text-sm text-gray-700"><?= $goods['sku'][0]['goods_no'] ?? '' ?: '--' ?></div>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require"> 商品售价 </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                            <div class="text-sm text-gray-700"><?= $goods['sku'][0]['goods_price'] ?? 0 ?></div>
                                        </div>
                                    </div>
                                    <div class="pt-4">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require"> 秒杀价格 </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                            <input type="number" min="0" class="tpl-form-input"
                                                   name="goods[sku][seckill_price]"
                                                   value="<?= $model['sku'][0]['seckill_price'] ?? '' ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 form-require"> 秒杀库存数量 </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                            <input type="number" min="0" class="tpl-form-input"
                                                   name="goods[sku][seckill_stock]"
                                                   value="<?= $model['sku'][0]['seckill_stock'] ?? '' ?>" required>
                                            <div class="help-block">
                                                <small>注：秒杀库存为独立库存，与主商品库存不同步</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- 商品多规格 -->
                            <?php if ($goods['spec_type'] == 20): ?>
                                <div class="pt-4">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">商品规格 </label>
                                    <div id="many-app" v-cloak class="goods-spec-many sm:w-3/4 "
                                         style="display: block">
                                        <div class="goods-spec-box style-simplify">
                                            <!-- 商品多规格sku信息 -->
                                            <div v-if="spec_list.length > 0" class="goods-sku overflow-x-auto">
                                                <!-- sku 批量设置 -->
                                                <div class="spec-batch flex flex-wrap items-center gap-3">
                                                    <div class="spec-form-inline inline-flex items-center gap-1">
                                                        <input type="number" v-model="batchData.seckill_price"
                                                               placeholder="秒杀价格">
                                                    </div>
                                                    <div class="spec-form-inline inline-flex items-center gap-1">
                                                        <input type="number" min="0" v-model="batchData.seckill_stock"
                                                               placeholder="秒杀库存">
                                                    </div>
                                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                        <button @click="onSubmitBatchData" type="button"
                                                                class="btn btn-sm btn-secondary rounded">批量设置
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- sku table -->
                                                <table class="spec-sku-tabel table-base border text-center
                                     mb-1 whitespace-nowrap">
                                                    <tbody>
                                                    <tr>
                                                        <th v-for="item in spec_attr">{{ item.group_name }}</th>
                                                        <th>商家编码</th>
                                                        <th>商品售价</th>
                                                        <th>商品库存</th>
                                                        <th class="form-require">
                                                            秒杀价格
                                                        </th>
                                                        <th class="form-require">
                                                            秒杀库存
                                                        </th>
                                                    </tr>
                                                    <tr v-for="(item, index) in spec_list">
                                                        <td v-for="td in item.rows" class="td-spec-value align-middle"
                                                            :rowspan="td.rowspan">
                                                            {{ td.spec_value }}
                                                        </td>
                                                        <td>{{ item.form.goods_no ? item.form.goods_no : '--' }}</td>
                                                        <td>{{ item.form.goods_price }}</td>
                                                        <td>{{ item.form.stock_num }}</td>
                                                        <td>
                                                            <input type="number" min="0" class="ipt-w80"
                                                                   name="seckill_price"
                                                                   v-model="item.form.seckill_price" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" min="0" class="ipt-w80"
                                                                   name="seckill_stock"
                                                                   v-model="item.form.seckill_stock" required>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <div class="help-block">
                                                    <small>注：秒杀库存为独立库存，与主商品库存不同步</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">库存计算方式 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[deduct_stock_type]" value="10"
                                            <?= $model['deduct_stock_type'] == 10 ? 'checked' : '' ?> >
                                        下单减库存
                                    </label>
                                    <!--                                    <label class="inline-flex items-center gap-2 mr-4">-->
                                    <!--                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[deduct_stock_type]" value="20"-->
                                    <!--                                            -->
                                    <? //= $model['deduct_stock_type'] == 20 ? 'checked' : '' ?><!-- >-->
                                    <!--                                        付款减库存-->
                                    <!--                                    </label>-->
                                    <div class="help-block">
                                        <small>注：秒杀商品默认为下单减库存，可在 <a
                                                    href="<?= url('apps.sharp.setting/index') ?>">基础设置</a>
                                            中配置未支付订单自动关闭释放库存
                                        </small>
                                    </div>
                                </div>
                            </div>


                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 限购数量 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[limit_num]"
                                           value="<?= $model['limit_num'] ?>" required>
                                    <small>注：每人限制购买的数量，如果填写0则不限购</small>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 商品状态 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[status]" value="1"
                                            <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        上架
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[status]" value="0"
                                            <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        下架
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[sort]"
                                           value="<?= $model['sort'] ?>" required>
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
        <input type="hidden" name="goods_id" value="{{ $value.goods_id }}">
    </div>
    {{ /each }}
</script>

<script src="assets/common/js/vue3.min.js"></script>
<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 注册商品多规格组件
        var specMany = new GoodsSpec({
            el: '#many-app',
            baseData: <?= \app\common\library\helper::jsonEncode($specData) ?>
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            // 获取多规格sku数据
            buildData: function () {
                var specData = specMany.appVue.getData();
                return {
                    goods: {
                        spec_many: {
                            spec_list: specData.spec_list
                        }
                    }
                };
            }
        });

    });
</script>
