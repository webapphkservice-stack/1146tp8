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
    <title>用户列表</title>
</head>
<body class="select-data">
<!-- 工具栏 -->
<div class="page_toolbar w-full mb-2 flex flex-wrap items-center gap-2">
    <form class="toolbar-form w-full" action="">
        <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
        <div class="w-full">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center">
                    <?php $grade = $request->get('grade'); ?>
                    <select name="grade" class="form-select w-44"
                            placeholder="请选择会员等级">
                        <option value=""></option>
                        <?php foreach ($gradeList as $item): ?>
                            <option value="<?= $item['grade_id'] ?>"
                                <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-center">
                    <?php $gender = $request->get('gender'); ?>
                    <select name="gender" class="form-select w-32"
                            placeholder="请选择性别">
                        <option value=""></option>
                        <option value="-1"
                            <?= $gender === '-1' ? 'selected' : '' ?>>全部
                        </option>
                        <option value="1"
                            <?= $gender === '1' ? 'selected' : '' ?>>男
                        </option>
                        <option value="2"
                            <?= $gender === '2' ? 'selected' : '' ?>>女
                        </option>
                        <option value="0"
                            <?= $gender === '0' ? 'selected' : '' ?>>未知
                        </option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="text" class="form-input rounded-r-none w-48" name="nickName"
                           placeholder="请输入微信昵称"
                           value="<?= $request->get('nickName') ?>">
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
            <th>微信头像</th>
            <th>微信昵称</th>
            <th>用户余额</th>
            <th>会员等级</th>
            <th>累积消费金额</th>
            <th>性别</th>
            <th>注册时间</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
            <tr>
                <td>
                    <label class="inline-flex items-center">
                        <input data-check="item" data-params='<?= json_encode([
                            'user_id' => (string)$item['user_id'],
                            'nickName' => $item['nickName'],
                            'avatarUrl' => $item['avatarUrl'],
                        ], JSON_UNESCAPED_SLASHES) ?>' type="checkbox">
                    </label>
                </td>
                <td>
                    <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                        <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                    </a>
                </td>
                <td><?= $item['nickName'] ?></td>
                <td><?= $item['balance'] ?></td>
                <td>
                    <?= !empty($item['grade']) ? $item['grade']['name'] : '--' ?>
                </td>
                <td><?= $item['expend_money'] ?></td>
                <td><?= $item['gender'] ?: '--' ?></td>
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
