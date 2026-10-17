<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="renderer" content="webkit"/>
    <link rel="stylesheet" href="assets/common/css/tailwind.css"/>
    <link rel="stylesheet" href="assets/store/css/app.css?v=<?= $version ?>"/>
    <script src="assets/common/js/jquery.min.js"></script>
    <script src="assets/common/js/jquery4-polyfill.js"></script>
    <title>拼团商品列表</title>
</head>
<body class="select-data">
<!-- 工具栏 -->
<div class="page_toolbar w-full mb-2 flex flex-wrap items-center gap-2">
    <form class="toolbar-form w-full" action="">
        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center">
                <?php $categoryId = $request->get('category_id') ?: null; ?>
                <select name="category_id" class="form-select w-44"
                        placeholder="商品分类">
                    <option value=""></option>
                    <?php if (isset($catgory)): foreach ($catgory as $first): ?>
                        <option value="<?= $first['category_id'] ?>"
                            <?= $categoryId == $first['category_id'] ? 'selected' : '' ?>>
                            <?= $first['name'] ?></option>
                        <?php if (isset($first['child'])): foreach ($first['child'] as $two): ?>
                            <option value="<?= $two['category_id'] ?>"
                                <?= $categoryId == $two['category_id'] ? 'selected' : '' ?>>
                                　　<?= $two['name'] ?></option>
                        <?php endforeach; endif; ?>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="flex items-center">
                <?php $status = $request->get('status') ?: null; ?>
                <select name="status" class="form-select w-32"
                        placeholder="商品状态">
                    <option value=""></option>
                    <option value="10"
                        <?= $status == 10 ? 'selected' : '' ?>>上架
                    </option>
                    <option value="20"
                        <?= $status == 20 ? 'selected' : '' ?>>下架
                    </option>
                </select>
            </div>
            <div class="flex items-center">
                <input type="text" class="form-input rounded-r-none w-48" name="search"
                       placeholder="请输入商品名称"
                       value="<?= $request->get('search') ?>">
                <button class="btn btn-default rounded-l-none" type="submit">搜索</button>
            </div>
        </div>
    </form>
</div>
<div class="w-full overflow-x-auto">
    <table class="table-base tpl-table-black whitespace-nowrap">
        <thead>
        <tr>
            <th class="w-10">
                <label class="inline-flex items-center">
                    <input data-check="all" type="checkbox">
                </label>
            </th>
            <th>商品ID</th>
            <th>商品图片</th>
            <th>商品名称</th>
            <th>商品分类</th>
            <th>成团人数</th>
            <th>成团有效时长</th>
            <th>添加时间</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td>
                    <label class="inline-flex items-center">
                        <input data-check="item" data-params='<?= json_encode([
                            'goods_id' => (string)$item['goods_id'],
                            'goods_name' => $item['goods_name'],
                            'selling_point' => $item['selling_point'],
                            'image' => $item['image'][0]['file_path'],
                            'goods_image' => $item['image'][0]['file_path'],
                            'sharing_price' => $item['sku'][0]['sharing_price'],
                            'line_price' => $item['sku'][0]['line_price'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td><?= $item['goods_id'] ?></td>
                <td>
                    <a href="<?= $item['image'][0]['file_path'] ?>" title="点击查看大图" target="_blank">
                        <img src="<?= $item['image'][0]['file_path'] ?>" width="50" height="50" alt="商品图片">
                    </a>
                </td>
                <td>
                    <p class="item-title"><?= $item['goods_name'] ?></p>
                </td>
                <td><?= $item['category']['name'] ?></td>
                <td><?= $item['people'] ?></td>
                <td><?=  time_text($item['group_time'])  ?>小时</td>
                <td><?=  time_text($item['create_time'])  ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="8" class="text-center">暂无记录</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<div class="w-full flex items-center justify-between mt-3">
    <div class="pagination-total mr-2">总记录：<?= $list->total() ?></div>
    <div><?= $list->render() ?> </div>
</div>

<script>
    /**
     * 获取已选择的数据
     * @returns {Array}
     */
    function getSelectedData() {
        var data = [];
        $('input[data-check=item]:checked').each(function () {
            data.push($(this).data('params'));
        });
        return data;
    }

    $(function () {

        // 全选框元素
        var $checkAll = $('input[data-check=all]')
            , $checkItem = $('input[data-check=item]')
            , itemCount = $checkItem.length;

        // 复选框: 全选和反选
        $checkAll.change(function () {
            $checkItem.prop('checked', this.checked);
        });

        // 复选框: 子元素
        $checkItem.change(function () {
            if (!this.checked) {
                $checkAll.prop('checked', false);
            } else {
                var checkedItemNum = $checkItem.filter(':checked').length;
                checkedItemNum === itemCount && $checkAll.prop('checked', true);
            }
        });

    });
</script>
</body>
</html>
