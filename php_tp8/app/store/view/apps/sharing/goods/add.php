<link rel="stylesheet" href="assets/store/css/goods.css?v=<?= $version ?>">

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
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="goods[goods_name]"
                                           value="" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品分类 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="goods[category_id]" required class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                                            <option value="<?= $first['category_id'] ?>"><?= $first['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <?php if (checkPrivilege('apps.sharing.category/add')): ?>
                                        <small class="ml-1">
                                            <a href="<?= url('apps.sharing.category/add') ?>">去添加</a>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品图片 </label>
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
                                        <div class="help-block mt-3">
                                            <small>尺寸750x750像素以上，大小2M以下 (可拖拽图片调整显示顺序 )</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">商品卖点 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="goods[selling_point]" value="">
                                    <small>选填，商品卖点简述，例如：此款商品美观大方 性价比较高 不容错过</small>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">拼团设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">是否允许单买 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_alone]" value="0" checked>
                                        允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_alone]" value="1">
                                        不允许
                                    </label>
                                    <div class="help-block">
                                        <small>是否允许用户选择不拼团单独购买，如果允许单买，请务必设置好单买价</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/6 form-require"> 成团人数 </label>
                                <div class="sm:w-5/6">
                                    <input type="number" min="2" class="tpl-form-input" name="goods[people]" value="2"
                                           required>
                                    <small>拼团成员的总人数，最低2人</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/6 form-require"> 成团有效时长 </label>
                                <div class="sm:w-5/6">
                                    <input type="number" min="1" class="tpl-form-input" name="goods[group_time]"
                                           value="24" required>
                                    <small>注：开团后的有效时间，单位：小时，超过时长则拼团失败</small>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">规格/库存</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品规格 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[spec_type]" value="10" checked>
                                        单规格
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[spec_type]" value="20">
                                        多规格
                                    </label>
                                </div>
                            </div>

                            <!-- 商品多规格 -->
                            <div id="many-app" v-cloak class="goods-spec-many mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="goods-spec-box sm:w-3/4 sm:ml-[16.667%] ">
                                    <!-- 规格属性 -->
                                    <div class="spec-attr">
                                        <div v-for="(item, index) in spec_attr" class="spec-group-item">
                                            <div class="spec-group-name">
                                                <span>{{ item.group_name }}</span>
                                                <i @click="onDeleteGroup(index)"
                                                   class="spec-group-delete iconfont icon-shanchu1" title="点击删除"></i>
                                            </div>
                                            <div class="spec-list ">
                                                <div v-for="(val, i) in item.spec_items" class="spec-item ">
                                                    <span>{{ val.spec_value }}</span>
                                                    <i @click="onDeleteValue(index, i)"
                                                       class="spec-item-delete iconfont icon-shanchu1" title="点击删除"></i>
                                                </div>
                                                <div class="spec-item-add  ">
                                                    <input type="text" v-model="item.tempValue"
                                                           class="ipt-specItem  border-green-500">
                                                    <button @click="onSubmitAddValue(index)" type="button"
                                                            class="btn ">添加
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 添加规格组：按钮 -->
                                    <div v-if="showAddGroupBtn" class="spec-group-button">
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
                                                <label class="text-sm text-gray-600 shrink-0">批量设置</label>
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="text" v-model="batchData.goods_no" placeholder="商家编码">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.goods_price"
                                                       placeholder="单买价">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.sharing_price"
                                                       placeholder="拼团价">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" v-model="batchData.line_price"
                                                       placeholder="划线价">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" min="0" v-model="batchData.stock_num"
                                                       placeholder="库存数量">
                                            </div>
                                            <div class="spec-form-inline inline-flex items-center gap-1">
                                                <input type="number" min="0" v-model="batchData.goods_weight"
                                                       placeholder="重量">
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
                                                <th class="form-require">
                                                    拼团价
                                                </th>
                                                <th class="">
                                                    单买价
                                                </th>
                                                <th>划线价</th>
                                                <th class="form-require">
                                                    库存
                                                </th>
                                                <th class="form-require">
                                                    重量(kg)
                                                </th>
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
                                                    <input type="number" min="0.01" class="ipt-w80" name="sharing_price"
                                                           v-model="item.form.sharing_price" required>
                                                </td>
                                                <td>
                                                    <input type="number" min="0" class="ipt-w80" name="goods_price"
                                                           v-model="item.form.goods_price">
                                                </td>
                                                <td>
                                                    <input type="number" min="0" class="ipt-w80" name="line_price"
                                                           v-model="item.form.line_price">
                                                </td>
                                                <td>
                                                    <input type="number" min="0" class="ipt-w80" name="stock_num"
                                                           v-model="item.form.stock_num" required>
                                                </td>
                                                <td>
                                                    <input type="number" min="0" class="ipt-w80" name="goods_weight"
                                                           v-model="item.form.goods_weight" required>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <div class="help-block">
                                            <small>注：如不允许单买，单买价设置为0即可</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 商品单规格 -->
                            <div class="goods-spec-single">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">商品编码 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input" name="goods[sku][goods_no]"
                                               value="">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">拼团价格 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0.01" class="tpl-form-input"
                                               name="goods[sku][sharing_price]"
                                               required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">单买价格 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0" class="tpl-form-input"
                                               name="goods[sku][goods_price]" value="">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">商品划线价 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0" class="tpl-form-input"
                                               name="goods[sku][line_price]">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">当前库存数量 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0" class="tpl-form-input" name="goods[sku][stock_num]"
                                               required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">商品重量(Kg) </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="number" min="0" class="tpl-form-input"
                                               name="goods[sku][goods_weight]"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">库存计算方式 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[deduct_stock_type]" value="10">
                                        下单减库存
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[deduct_stock_type]" value="20"
                                               checked>
                                        付款减库存
                                    </label>
                                </div>
                            </div>

                            <div class="widget-head">
                                <div class="widget-title">商品详情</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">商品详情 </label>
                                <div class="sm:w-3/4 ">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="goods[content]" rows="15"></textarea>
                                </div>
                            </div>
                            <div class="widget-head">
                                <div class="widget-title">其他设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">运费模板 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="goods[delivery_id]" required class="form-select">
                                        <option value="">请选择运费模板</option>
                                        <?php foreach ($delivery as $item): ?>
                                            <option value="<?= $item['delivery_id'] ?>">
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
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品状态 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[goods_status]" value="10"
                                               checked>
                                        上架
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[goods_status]" value="20">
                                        下架
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">初始销量</label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[sales_initial]"
                                           value="0">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">商品排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="goods[goods_sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>

                            <!-- 商品积分设置 -->
                            <div class="widget-head">
                                <div class="widget-title">积分设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 是否开启积分赠送 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_points_gift]" value="1"
                                               checked>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_points_gift]" value="0">
                                        关闭
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 是否允许使用积分抵扣 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_points_discount]" value="1"
                                               checked>
                                        允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_points_discount]" value="0">
                                        不允许
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"></label>
                                <div class="sm:w-3/4 ">
                                    <div class="help-block">
                                        <small>注：如需使用积分功能必须在 [营销管理 - 积分设置] 中开启</small>
                                    </div>
                                </div>
                            </div>

                            <!-- 会员折扣设置-->
                            <div class="widget-head">
                                <div class="widget-title">会员折扣设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 是否开启会员折扣 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_enable_grade]" value="1"
                                               checked>
                                        开启
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_enable_grade]" value="0">
                                        关闭
                                    </label>
                                    <div class="help-block">
                                        <small>如果不开启会员折扣，该商品则不享受会员等级折扣价</small>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-grade__content">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require"> 会员折扣设置 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_alone_grade]" value="0"
                                                   checked>
                                            默认折扣
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_alone_grade]" value="1">
                                            单独设置折扣
                                        </label>
                                        <div class="help-block">
                                            <small>默认折扣：默认为用户所属会员等级的折扣率</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="panel-grade-alone__content hide">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0"> </label>
                                        <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                            <!-- 会员等级列表-->
                                            <?php foreach ($gradeList as $item): ?>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-label input-group-label__left">
                                                        <?= $item['name'] ?>：
                                                    </span>
                                                    <input type="number" class="form-input"
                                                           name="goods[alone_grade_equity][<?= $item['grade_id'] ?>]"
                                                           value="" min="0" max="10" required>
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

                            <!-- 分销设置-->
                            <div class="widget-head">
                                <div class="widget-title">分销设置</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">是否开启单独分销 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_ind_dealer]" value="0"
                                               checked>
                                        关闭
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[is_ind_dealer]" value="1">
                                        开启
                                    </label>
                                </div>
                            </div>
                            <div class="panel-dealer__content hide">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">分销佣金类型 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[dealer_money_type]" value="10"
                                                checked>
                                            百分比
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" name="goods[dealer_money_type]" value="20">
                                            固定金额
                                        </label>
                                    </div>
                                </div>
                                <div class="pt-3">
                                    <label class="sm:w-1/4 lg:w-1/6 form-require">单独分销设置 </label>
                                    <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">一级佣金：</span>
                                            <input type="text" name="goods[first_money]" value=""
                                                class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">%</span>
                                        </div>
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">二级佣金：</span>
                                            <input type="text" name="goods[second_money]" value=""
                                                class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">%</span>
                                        </div>
                                        <div class="input-group mb-4">
                                            <span class="input-group-label input-group-label__left">三级佣金：</span>
                                            <input type="text" name="goods[third_money]" value=""
                                                class="form-input">
                                            <span class="widget-dealer__unit input-group-label input-group-label__right">%</span>
                                        </div>
                                        <div class="help-blockx">
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/vue3.min.js"></script>
<script src="assets/common/js/ddsort.js"></script>

<script src="assets/store/js/goods.spec.js?v=<?= $version ?>"></script>
<script>

    $(function () {



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
            el: '#many-app'
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
