<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑运费模版</div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">模版名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="delivery[name]"
                                           value="<?= $model['name'] ?>" required>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">计费方式 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="delivery[method]" value="10"
                                            <?= $model['method']['value'] == 10 ? 'checked' : '' ?>> 按件数
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="delivery[method]" value="20"
                                            <?= $model['method']['value'] == 20 ? 'checked' : '' ?>>
                                        按重量
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">
                                    配送区域及运费
                                </label>
                                <div class="sm:w-3/4 lg:w-5/6 ">
                                    <div class=" overflow-x-auto">
                                        <table class="regional-table table-base border
                                         text-center mb-1">
                                            <tbody>
                                            <tr>
                                                <th width="42%">可配送区域</th>
                                                <th>
                                                    <span class="first">
                                                        <?= $model['method']['value'] == 10 ? '首件 (个)' : '首重 (Kg)' ?>
                                                    </span>
                                                </th>
                                                <th>运费 (元)</th>
                                                <th>
                                                    <span class="additional">
                                                          <?= $model['method']['value'] == 10 ? '续件 (个)' : '续重 (Kg)' ?>
                                                    </span>
                                                </th>
                                                <th>续费 (元)</th>
                                            </tr>
                                            <?php foreach ($model['rule'] as $item) : ?>
                                                <tr>
                                                    <td class="text-left">
                                                        <p class="selected-content mb-1">
                                                            <?= $item['region_content'] ?>
                                                        </p>
                                                        <p class="operation mb-1">
                                                            <a class="edit" href="javascript:;">编辑</a>
                                                            <a class="delete" href="javascript:;">删除</a>
                                                        </p>
                                                        <input type="hidden" name="delivery[rule][region][]"
                                                               value="<?= $item['region'] ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="delivery[rule][first][]"
                                                               value="<?= $item['first'] ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="delivery[rule][first_fee][]"
                                                               value="<?= $item['first_fee'] ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="delivery[rule][additional][]"
                                                               value="<?= $item['additional'] ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="delivery[rule][additional_fee][]"
                                                               value="<?= $item['additional_fee'] ?>">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <td colspan="5" class="text-left">
                                                    <a class="add-region btn btn-default btn-xs"
                                                       href="javascript:;">
                                                        <i class="iconfont icon-dingwei"></i>
                                                        点击添加可配送区域和运费
                                                    </a>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="delivery[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
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
<div class="regional-choice"></div>
<script src="assets/store/js/delivery.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 初始化区域选择界面
        var datas = JSON.parse('<?= $regionData ?>');

        // 配送区域表格
        new Delivery({
            table: '.regional-table',
            regional: '.regional-choice',
            datas: datas
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
