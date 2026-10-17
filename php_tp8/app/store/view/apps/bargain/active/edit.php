<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title"> 编辑砍价活动</div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 商品信息 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <div class="goods-detail">
                                        <div class="goods-image">
                                            <img src="<?= $goods['image'][0]['file_path'] ?>" alt="">
                                        </div>
                                        <div class="goods-info dis-flex flex-dir-column flex-x-center">
                                            <p class="goods-title"><?= $goods['goods_name'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动时间 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <!-- 会员等级列表-->
                                    <div class="input-group">
                                        <input type="text" class="j-laydate-start form-input"
                                               name="active[start_time]"
                                               placeholder="开始时间"
                                               value="<?=  time_text($model['start_time'])  ?>">
                                        <span class="input-group-label input-group-label__center">至</span>
                                        <input type="text" class="j-laydate-end form-input"
                                               name="active[end_time]"
                                               placeholder="结束时间"
                                               value="<?=  time_text($model['end_time'])  ?>">
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
                                           value="<?=  time_text($model['expiryt_time'])  ?>" placeholder="" required>
                                    <small>自用户发起砍价到砍价截止的时间，单位：小时</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 砍价底价 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0.01" class="tpl-form-input" name="active[floor_price]"
                                           value="<?= $model['floor_price'] ?>" placeholder="" required>
                                    <small>砍价商品的最低价格，单位：元</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 帮砍人数 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="1" class="tpl-form-input" name="active[peoples]"
                                           value="<?= $model['peoples'] ?>" placeholder="" required>
                                    <small>每个砍价订单的帮砍人数，达到该人数才可砍至底价</small>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 可自砍一刀 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_self_cut]" value="1"
                                            <?= $model['is_self_cut'] == 1 ? 'checked' : '' ?>>
                                        允许
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_self_cut]" value="0"
                                            <?= $model['is_self_cut'] == 0 ? 'checked' : '' ?>>
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
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_floor_buy]" value="1"
                                            <?= $model['is_floor_buy'] == 1 ? 'checked' : '' ?>>
                                        是
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[is_floor_buy]" value="0"
                                            <?= $model['is_floor_buy'] == 0 ? 'checked' : '' ?>>
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
                                           value="<?= $model['initial_sales'] ?>" required>
                                    <small>注：前台展示的销量 = 初始销量 + 实际销量</small>
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 分享标题 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="active[share_title]"
                                           value="<?= $model['share_title'] ?>"
                                           placeholder="麻烦帮我砍一刀！我真的很想要了，爱你哟！(๑′ᴗ‵๑)" required>
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 砍价助力语 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="text" class="tpl-form-input" name="active[prompt_words]"
                                           value="<?= $model['prompt_words'] ?>"
                                           placeholder="&#34;朋友一生一起走，帮砍一刀有没有&#34;">
                                </div>
                            </div>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 活动状态 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[status]" value="1"
                                            <?= $model['status'] == 1 ? 'checked' : '' ?>>
                                        启用
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="active[status]" value="0"
                                            <?= $model['status'] == 0 ? 'checked' : '' ?>>
                                        禁用
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 md:w-1/2 lg:w-5/12 ">
                                    <input type="number" min="0" class="tpl-form-input" name="active[sort]"
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
<script src="assets/common/plugins/laydate/laydate.js"></script>
<script>
    $(function () {

        // 时间选择器
        laydate.render({
            elem: '.j-laydate-start'
            , type: 'datetime'
        });

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
