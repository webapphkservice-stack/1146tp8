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
    <title>砍价活动列表</title>
</head>
<body class="select-data">
<!-- 工具栏 -->
<div class="page_toolbar w-full mb-2 flex flex-wrap items-center gap-2">
    <form class="toolbar-form w-full" action="">
        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
        <div class="flex flex-wrap items-center gap-2">
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
            <th>活动ID</th>
            <th>商品信息</th>
            <th>活动时间</th>
            <th>砍价底价</th>
            <th>帮砍人数</th>
            <th>创建时间</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td>
                    <label class="inline-flex items-center">
                        <input data-check="item" data-params='<?= json_encode([
                            'active_id' => (string)$item['active_id'],
                            'goods_name' => $item['goods']['goods_name'],
                            'goods_image' => $item['goods']['goods_image'],
                            'floor_price' => $item['floor_price'],
                            'original_price' => $item['goods']['goods_sku']['goods_price'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td><?= $item['active_id'] ?></td>
                <td class="goods-detail">
                    <div class="goods-image">
                        <img src="<?= $item['goods']['goods_image'] ?>" alt="">
                    </div>
                    <div class="goods-info dis-flex flex-dir-column flex-x-center">
                        <p class="goods-title"><?= $item['goods']['goods_name'] ?></p>
                    </div>
                </td>
                <td>
                    <p><span>开始时间：</span><?=  time_text($item['start_time'])  ?></p>
                    <p><span>结束时间：</span><?=  time_text($item['end_time'])  ?></p>
                </td>
                <td><?= $item['floor_price'] ?></td>
                <td><?= $item['peoples'] ?></td>
                <td><?=  time_text($item['create_time'])  ?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td colspan="7" class="text-center">暂无记录</td>
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
