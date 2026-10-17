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
    <title>门店列表</title>
</head>
<body class="select-data">
<!-- 工具栏 -->
<div class="page_toolbar w-full mb-2 flex flex-wrap items-center gap-2">
    <form class="toolbar-form w-full" action="">
        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
        <div class="w-full md:w-1/4">
            <div class="flex items-center">
                <?php if (checkPrivilege('shop/add')): ?>
                    <a class="btn btn-primary" href="<?= url('shop/add') ?>">
                        <span>+</span> 新增
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="w-full md:w-3/4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center">
                    <?php $isCheck = $request->get('is_check'); ?>
                    <select name="is_check" class="form-select w-36"
                            placeholder="自提核销">
                        <option value=""></option>
                        <option value="-1" <?= $isCheck === '-1' ? 'selected' : '' ?>>全部</option>
                        <option value="1" <?= $isCheck === '1' ? 'selected' : '' ?>>支持</option>
                        <option value="0"<?= $isCheck === '0' ? 'selected' : '' ?>>不支持</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="text" class="form-input rounded-r-none w-64" name="search"
                           placeholder="请输入门店名称/联系人/电话"
                           value="<?= $request->get('search') ?>">
                    <button class="btn btn-default rounded-l-none" type="submit">搜索</button>
                </div>
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
            <th>门店ID</th>
            <th>门店logo</th>
            <th>门店名称</th>
            <th>自提核销</th>
            <th>门店状态</th>
            <th>创建时间</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td>
                    <label class="inline-flex items-center">
                        <input data-check="item" data-params='<?= json_encode([
                            'shop_id' => (string)$item['shop_id'],
                            'shop_name' => $item['shop_name'],
                            'logo_image' => $item['logo']['file_path'],
                            'phone' => $item['phone'],
                            'region' => $item['region'],
                            'address' => $item['address'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td><?= $item['shop_id'] ?></td>
                <td>
                    <a href="<?= $item['logo']['file_path'] ?>" title="点击查看大图" target="_blank">
                        <img src="<?= $item['logo']['file_path'] ?>" width="72" height="72" alt="">
                    </a>
                </td>
                <td><?= $item['shop_name'] ?></td>
                <td>
                    <span class="badge badge-<?= $item['is_check'] ? 'success' : 'warning' ?>">
                       <?= $item['is_check'] ? '支持' : '不支持' ?>
                   </span>
                </td>
                <td>
                    <span class="badge badge-<?= $item['status'] ? 'success' : 'warning' ?>">
                       <?= $item['status'] ? '启用' : '禁用' ?>
                   </span>
                </td>
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
