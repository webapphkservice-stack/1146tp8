<link rel="stylesheet" href="/assets/store/css/goods.css?v=<?= $version ?>">
<link rel="stylesheet" href="/assets/common/plugins/umeditor/themes/default/css/umeditor.css">

<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">基本信息</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品名称 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="text" class="tpl-form-input" name="goods[goods_name]"
                                           value="<?= $model['goods_name'] ?>" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品分类 </label>
                                <div class="sm:w-3/4 grow">
                                    <select name="goods[category_id]" required class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                            <option value="<?= $first['category_id'] ?>"
                                                <?= $model['category_id'] == $first['category_id'] ? 'selected' : '' ?>>
                                                <?= $first['name'] ?></option>
                                            <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                                                <option value="<?= $two['category_id'] ?>"
                                                    <?= $model['category_id'] == $two['category_id'] ? 'selected' : '' ?>>
                                                    　　<?= $two['name'] ?></option>
                                                <?php if (isset($two['child'])): foreach ($two['child'] as $three): ?>
                                                    <option value="<?= $three['category_id'] ?>"
                                                        <?= $model['category_id'] == $three['category_id'] ? 'selected' : '' ?>>
                                                        　　　<?= $three['name'] ?></option>
                                                <?php endforeach; endif; ?>
                                            <?php endforeach; endif; ?>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="ml-1">
                                        <a href="<?= url('goods.category/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品图片 </label>
                                <div class="sm:w-3/4 grow">
                                    <div class="block">
                                        <button type="button"
                                                class="upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list">
                                            <?php foreach ($model['image'] as $key => $item): ?>
                                                <div class="file-item">
                                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= $item['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="goods[images][]"
                                                           value="<?= $item['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="help-block mt-3">
                                        <small>尺寸750x750像素以上，大小2M以下 (可拖拽图片调整显示顺序 )</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品卖点 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="text" class="tpl-form-input" name="goods[selling_point]"
                                           value="<?= $model['selling_point'] ?>">
                                    <small>选填，商品卖点简述，例如：此款商品美观大方 性价比较高 不容错过</small>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">规格/库存</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品规格 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[spec_type]" value="10" 
                                            <?= $model['spec_type'] == 10 ? 'checked' : '' ?>
                                            <?= $isSpecLocked ? 'disabled' : '' ?>>
                                        单规格
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[spec_type]" value="20" 
                                            <?= $model['spec_type'] == 20 ? 'checked' : '' ?>
                                            <?= $isSpecLocked ? 'disabled' : '' ?>>
                                        <span>多规格</span>
                                    </label>
                                    <?php if ($isSpecLocked): ?>
                                        <div class="help-block pt-3">
                                            <small class="x-color-red">注：该商品当前正在参与其他活动，商品规格不允许更改</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- 商品多规格 -->
                            <div id="many-app" v-cloak class="goods-spec-many mb-4 flex flex-wrap items-start gap-x-2"
                                 v-bind:style="{display: '<?= $model['spec_type'] == 20 ? 'block' : 'none' ?>'}">
                                <div class="goods-spec-box sm:w-3/4 sm:ml-[16.67%]">
                                    <!-- 规格属性 -->
                                    <div class="spec-attr">
                                        <div v-for="(item, index) in spec_attr" class="spec-group-item">
                                            <div class="spec-group-name">
                                                <span>{{ item.group_name }}</span>
                                                <i v-if="!isSpecLocked" @click="onDeleteGroup(index)"
                                                   class="spec-group-delete iconfont icon-shanchu1" title="点击删除"></i>
                                            </div>
                                            <div class="spec-list">
                                                <div v-for="(val, i) in item.spec_items" class="spec-item">
                                                    <span>{{ val.spec_value }}</span>
                                                    <i v-if="!isSpecLocked" @click="onDeleteValue(index, i)"
                                                       class="spec-item-delete iconfont icon-shanchu1" title="点击删除"></i>
                                                </div>
                                                <div v-if="!isSpecLocked" class="spec-item-add">
                                                    <input type="text" v-model="item.tempValue"
                                                           class="ipt-specItem border-green-500">
                                                    <button @click="onSubmitAddValue(index)" type="button"
                                                            class="btn">添加
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 添加规格组：按钮 -->
                                    <div v-if="showAddGroupBtn && !isSpecLocked" class="spec-group-button">
                                        <button @click="onToggleAddGroupForm" type="button"
                                                class="btn">添加规格
                                        </button>
                                    </div>

                                    <!-- 添加规格：表单 -->
                                    <div v-if="showAddGroupForm" class="spec-group-add">
                                        <div class="spec-group-add-item mb-4 flex flex-col gap-1">
                                            <label class="form-require">规格名 </label>
                                            <input type="text" class="input-specName tpl-form-input"
                                                   v-model="addGroupFrom.specName"
                                                   placeholder="请输入规格名称">
                                        </div>
                                        <div class="spec-group-add-item mb-4 flex flex-col gap-1">
                                            <label class="form-require">规格值 </label>
                                            <input type="text" class="input-specValue tpl-form-input"
                                                   v-model="addGroupFrom.specValue"
                                                   placeholder="请输入规格值">
                                        </div>
                                        <div class="spec-group-add-item mt-4">
                                            <button @click="onSubmitAddGroup" type="button"
                                                    class="btn btn-xs btn-secondary"> 确定
                                            </button>
                                            <button @click="onToggleAddGroupForm" type="button"
                                                    class="btn btn-xs btn-default"> 取消
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 商品多规格sku信息 -->
                                    <div v-if="spec_list.length > 0" class="goods-sku overflow-x-auto">
                                        <!-- 分割线 -->
                                        <div class="goods-spec-line mt-6 mb-6"></div>
                                        <!-- sku 批量设置 -->
                                        <div class="spec-batch flex flex-wrap items-center gap-3">
                                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                <label class="text-sm text-gray-600">批量设置</label>
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="text" v-model="batchData.goods_no" placeholder="商家编码">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.goods_price" placeholder="销售价">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.line_price" placeholder="划线价">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.stock_num" placeholder="库存数量">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.goods_weight" placeholder="重量">
                                            </div>
                                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                <button @click="onSubmitBatchData" type="button"
                                                        class="btn btn-sm btn-secondary rounded">确定
                                                </button>
                                            </div>
                                        </div>
                                        <!-- sku table -->
                                        <table class="spec-sku-tabel table-base border text-center
                                     mb-1 whitespace-nowrap">
                                            <tbody>
                                            <tr>
                                                <th v-for="item in spec_attr">{{ item.group_name }}</th>
                                                <th>规格图片</th>
                                                <th>商家编码</th>
                                                <th>销售价</th>
                                                <th>划线价</th>
                                                <th>库存</th>
                                                <th>重量(kg)</th>
                                            </tr>
                                            <tr v-for="(item, index) in spec_list">
                                                <td v-for="td in item.rows" class="td-spec-value align-middle"
                                                    :rowspan="td.rowspan">
                                                    {{ td.spec_value }}
                                                </td>
                                                <td class="align-middle spec-image">
                                                    <div v-if="item.form.image_id" class="j-selectImg data-image"
                                                         v-bind:data-index="index">
                                                        <img :src="item.form.image_path" alt="">
                                                        <i class="iconfont icon-shanchu image-delete"
                                                           @click.stop="onDeleteSkuImage(index)"></i>
                                                    </div>
                                                    <div v-else class="j-selectImg upload-image"
                                                         v-bind:data-index="index">
                                                        <i class="iconfont icon-add"></i>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="ipt-goods-no" name="goods_no"
                                                           v-model="item.form.goods_no">
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="goods_price"
                                                           v-model="item.form.goods_price" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="line_price"
                                                           v-model="item.form.line_price">
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="stock_num"
                                                           v-model="item.form.stock_num" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="ipt-w80" name="goods_weight"
                                                           v-model="item.form.goods_weight" required>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品单规格 -->
                            <div class="goods-spec-single"
                                 style="display: <?= $model['spec_type'] == 10 ? 'block' : 'none' ?>;">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品编码 </label>
                                    <div class="sm:w-3/4 grow">
                                        <input type="text" class="tpl-form-input" name="goods[sku][goods_no]"
                                               value="<?= $model['sku'][0]['goods_no'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品价格 </label>
                                    <div class="sm:w-3/4 grow">
                                        <input type="number" class="tpl-form-input" name="goods[sku][goods_price]"
                                               value="<?= $model['sku'][0]['goods_price'] ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品划线价 </label>
                                    <div class="sm:w-3/4 grow">
                                        <input type="number" class="tpl-form-input" name="goods[sku][line_price]"
                                               value="<?= $model['sku'][0]['line_price'] ?>">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">当前库存数量 </label>
                                    <div class="sm:w-3/4 grow">
                                        <input type="number" class="tpl-form-input" name="goods[sku][stock_num]"
                                               value="<?= $model['sku'][0]['stock_num'] ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品重量(Kg) </label>
                                    <div class="sm:w-3/4 grow">
                                        <input type="number" class="tpl-form-input" name="goods[sku][goods_weight]"
                                               value="<?= $model['sku'][0]['goods_weight'] ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">库存计算方式 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[deduct_stock_type]" value="10" 
                                            <?= $model['deduct_stock_type'] == 10 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        下单减库存
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[deduct_stock_type]" value="20" 
                                            <?= $model['deduct_stock_type'] == 20 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        付款减库存
                                    </label>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">商品详情</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品详情 </label>
                                <div class="sm:w-3/4 grow">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="goods[content]" rows="15"><?= $model['content'] ?></textarea>
                                </div>
                            </div>
                            <div class="widget-head">
                                <div class="widget-title">其他设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">运费模板 </label>
                                <div class="sm:w-3/4 grow">
                                    <select name="goods[delivery_id]" required class="form-select">
                                        <option value="">请选择运费模板</option>
                                        <?php foreach ($delivery as $item): ?>
                                            <option value="<?= $item['delivery_id'] ?>"
                                                <?= $model['delivery_id'] == $item['delivery_id'] ? 'selected' : '' ?>>
                                                <?= $item['name'] ?> (<?= $item['method']['text'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="ml-1">
                                        <a href="<?= url('setting.delivery/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品状态 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[goods_status]" value="10" 
                                            <?= $model['goods_status']['value'] == 10 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        上架
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[goods_status]" value="20" 
                                            <?= $model['goods_status']['value'] == 20 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        下架
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">初始销量</label>
                                <div class="sm:w-3/4 grow">
                                    <input type="number" class="tpl-form-input" name="goods[sales_initial]"
                                           value="<?= $model['sales_initial'] ?>">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">商品排序 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="number" class="tpl-form-input" name="goods[goods_sort]"
                                           value="<?= $model['goods_sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <!-- 商品积分设置 -->
                            <div class="widget-head">
                                <div class="widget-title">积分设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0"> 是否开启积分赠送 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_points_gift]" value="1" 
                                            <?= $model['is_points_gift'] == '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_points_gift]" value="0" 
                                            <?= $model['is_points_gift'] == '0' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0"> 是否允许使用积分抵扣 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_points_discount]" value="1" 
                                            <?= $model['is_points_discount'] == '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_points_discount]" value="0" 
                                            <?= $model['is_points_discount'] == '0' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        不允许
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0"></label>
                                <div class="sm:w-3/4 grow">
                                    <div class="help-block">
                                        <small>注：如需使用积分功能必须在 [营销管理 - 积分设置] 中开启</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 会员折扣设置 -->
                            <div class="widget-head">
                                <div class="widget-title">会员折扣设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0"> 是否开启会员折扣 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_enable_grade]" value="1" 
                                            <?= $model['is_enable_grade'] == '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_enable_grade]" value="0" 
                                            <?= $model['is_enable_grade'] == '0' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>如果不开启会员折扣，该商品则不享受会员等级折扣价</small>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-grade__content <?= $model['is_enable_grade'] == '0' ? 'hide' : '' ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0"> 会员折扣设置 </label>
                                    <div class="sm:w-3/4 grow">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" name="goods[is_alone_grade]" value="0" 
                                                <?= $model['is_alone_grade'] == '0' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                            默认折扣
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" name="goods[is_alone_grade]" value="1" 
                                                <?= $model['is_alone_grade'] == '1' ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                            单独设置折扣
                                        </label>
                                        <div class="help-block">
                                            <small>默认折扣：默认为用户所属会员等级的折扣率</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-grade-alone__content <?= $model['is_alone_grade'] == '0' ? 'hide' : '' ?>">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 shrink-0"> </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 grow">
                                            <!-- 会员等级列表-->
                                            <?php foreach ($gradeList as $item): ?>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-label input-group-label__left">
                                                        <?= $item['name'] ?>：
                                                    </span>
                                                    <input type="number" class="form-input"
                                                           name="goods[alone_grade_equity][<?= $item['grade_id'] ?>]"
                                                           value="<?= isset($model['alone_grade_equity'][$item['grade_id']])
                                                               ? $model['alone_grade_equity'][$item['grade_id']] : 0 ?>"
                                                           min="0" max="10" required>
                                                    <span class="input-group-label input-group-label__right">折</span>
                                                </div>
                                            <?php endforeach; ?>
                                            <div class="help-block">
                                                <small>注：折扣率范围0-10，9.5代表9.5折，0代表不折扣</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- VIP会员专享 -->
                            <div class="widget-head">
                                <div class="widget-title">VIP会员专享</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">是否会员专享商品 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_vip]" value="1" 
                                            <?= !empty($model['is_vip']) ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        是
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_vip]" value="0" 
                                            <?= empty($model['is_vip']) ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        否
                                    </label>
                                    <div class="help-block">
                                        <small>设为"是"后，仅 VIP 会员（会员未过期）可购买；非会员下单时会被拦截</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 分销设置 -->
                            <div class="widget-head">
                                <div class="widget-title">分销设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">是否开启单独分销 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_ind_dealer]" value="0" 
                                            <?= $model['is_ind_dealer'] == false ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        关闭
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="goods[is_ind_dealer]" value="1" 
                                            <?= $model['is_ind_dealer'] == true ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                        开启
                                    </label>
                                </div>
                            </div>
                            <div class="panel-dealer__content <?= $model['is_ind_dealer'] == false ? 'hide' : '' ?>">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">分销佣金类型 </label>
                                    <div class="sm:w-3/4 grow">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" name="goods[dealer_money_type]" value="10"
                                                   
                                                <?= $model['dealer_money_type'] == 10 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                            百分比
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" name="goods[dealer_money_type]" value="20"
                                                   
                                                <?= $model['dealer_money_type'] == 20 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                            固定金额
                                        </label>
                                    </div>
                                </div>
                                <div class="pt-3">
                                    <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">单独分销设置 </label>
                                    <?php
                                    $unit = [10 => '%', 20 => '元'];
                                    ?>
                                    <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 grow">
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">一级佣金：</span>
                                            <input type="text" name="goods[first_money]"
                                                   value="<?= $model['first_money'] ?>"
                                                   class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">
                                                <?= $unit[$model['dealer_money_type']] ?>
                                            </span>
                                        </div>
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">二级佣金：</span>
                                            <input type="text" name="goods[second_money]"
                                                   value="<?= $model['second_money'] ?>"
                                                   class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">
                                                <?= $unit[$model['dealer_money_type']] ?>
                                            </span>
                                        </div>
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">三级佣金：</span>
                                            <input type="text" name="goods[third_money]"
                                                   value="<?= $model['third_money'] ?>"
                                                   class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">
                                                <?= $unit[$model['dealer_money_type']] ?>
                                            </span>
                                        </div>
                                        <div class="help-block">
                                            <p>
                                                <small>注：如需使用分销功能必须在 [分销中心 - 分销设置] 中开启</small>
                                            </p>
                                            <p>
                                                <small>注：如不开启单独分销则默认使用全局分销比例</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 表单提交按钮 -->
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary btn-sm">提交
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="/assets/common/js/vue.min.js"></script>
<script src="/assets/common/js/ddsort.js"></script>
<script src="/assets/common/plugins/umeditor/umeditor.config.js?v=<?= $version ?>"></script>
<script src="/assets/common/plugins/umeditor/umeditor.min.js"></script>

<script src="/assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 商品详情富文本编辑器
        UM.getEditor('container', {
            initialFrameWidth: '100%',
            initialFrameHeight: 600
        });

        // 选择图片
        $('.upload-file').selectImages({
            name: 'goods[images][]'
            , multiple: true
        });

        // 图片列表拖动
        $('.uploader-list').DDSort({
            target: '.file-item',
            delay: 100, // 延时处理，默认为 50 ms，防止手抖点击 A 链接无效
            floatStyle: {
                'border': '1px solid #ccc',
                'background-color': '#fff'
            }
        });

        // 切换单/多规格
        $('input:radio[name="goods[spec_type]"]').change(function (e) {
            var $goodsSpecMany = $('.goods-spec-many')
                , $goodsSpecSingle = $('.goods-spec-single');
            if (e.currentTarget.value === '10') {
                $goodsSpecMany.hide() && $goodsSpecSingle.show();
            } else {
                $goodsSpecMany.show() && $goodsSpecSingle.hide();
            }
        });

        // 注册商品多规格组件
        var specMany = new GoodsSpec({
            el: '#many-app',
            baseData: <?= $specData ?>,
            isSpecLocked: <?= $isSpecLocked ? 'true' : 'false' ?>
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
                            spec_attr: specData.spec_attr,
                            spec_list: specData.spec_list
                        }
                    }
                };
            },
            // 自定义验证
            validation: function () {
                var specType = $('input:radio[name="goods[spec_type]"]:checked').val();
                if (specType === '20') {
                    var isEmpty = specMany.appVue.isEmptySkuList();
                    isEmpty === true && layer.msg('商品规格不能为空');
                    return !isEmpty;
                }
                return true;
            }
        });

        // 是否开启单独分销
        var $panelDealer = $('.panel-dealer__content');
        $("input:radio[name='goods[is_ind_dealer]']").change(function (e) {
            // e.currentTarget.value === '0' ? $panelDealer.hide() : $panelDealer.show();
            $panelDealer.toggle();
        });

        // 选中百分比 后面显示% 选中固定金额 后面显示元
        $("input:radio[name='goods[dealer_money_type]']").change(function (e) {
            $('.widget-dealer__unit').text(e.currentTarget.value === '10' ? '%' : '元');
        });


        // 是否开启会员折扣
        var $panelGrade = $('.panel-grade__content');
        $("input:radio[name='goods[is_enable_grade]']").change(function (e) {
            // e.currentTarget.value === '0' ? $panelGrade.toggle() : $panelGrade.toggle();
            $panelGrade.toggle();
        });

        // 单独设置折扣
        var $panelGradeAlone = $('.panel-grade-alone__content');
        $("input:radio[name='goods[is_alone_grade]']").change(function (e) {
            // e.currentTarget.value !== '0' ? $panelGradeAlone.hide() : $panelGradeAlone.show();
            $panelGradeAlone.toggle();
        });

    });
</script>
