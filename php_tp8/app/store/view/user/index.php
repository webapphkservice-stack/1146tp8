<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">用户列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <?php $grade = $request->get('grade'); ?>
                            <select name="grade" class="form-select">
                                <option value="">全部会员等级</option>
                                <?php foreach ($gradeList as $item): ?>
                                    <option value="<?= $item['grade_id'] ?>"
                                        <?= $grade == $item['grade_id'] ? 'selected' : '' ?>><?= $item['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php $gender = $request->get('gender'); ?>
                            <select name="gender" class="form-select">
                                <option value="">全部性别</option>
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
                            <div class="input-group input-group-sm tpl-form-border-form">
                                <input type="text" class="form-input" name="nickName"
                                       placeholder="请输入微信昵称"
                                       value="<?= $request->get('nickName') ?>">
                                <div class="input-group-btn">
                                    <button class="btn btn-default icon-search"
                                            type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>用户头像</th>
                                <th>微信昵称</th>
                                <th>注册方式</th>
                                <th>用户余额</th>
                                <th>可用积分</th>
                                <th>会员等级</th>
                                <th>实际消费金额</th>
                                <!-- 暂时注释: 性别/国家/省份/城市
                                <th>性别</th>
                                <th>国家</th>
                                <th>省份</th>
                                <th>城市</th>
                                -->
                                <th>注册时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['user_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle"><?= $item['nickName'] ?></td>
                                    <td class="align-middle"><?= $item['register_method'] ?></td>
                                    <td class="align-middle"><?= $item['balance'] ?></td>
                                    <td class="align-middle"><?= $item['points'] ?></td>
                                    <td class="align-middle">
                                        <?= !empty($item['grade']) ? $item['grade']['name'] : '--' ?>
                                    </td>
                                    <td class="align-middle"><?= $item['expend_money'] ?></td>
                                    <!-- 暂时注释: 性别/国家/省份/城市
                                    <td class="align-middle"><?= $item['gender'] ?></td>
                                    <td class="align-middle"><?= $item['country'] ?: '--' ?></td>
                                    <td class="align-middle"><?= $item['province'] ?: '--' ?></td>
                                    <td class="align-middle"><?= $item['city'] ?: '--' ?></td>
                                    -->
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('user/recharge')): ?>
                                                <a class="j-recharge tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   title="用户充值"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   data-balance="<?= $item['balance'] ?>"
                                                   data-points="<?= $item['points'] ?>"
                                                >
                                                    <i class="iconfont icon-qiandai"></i>
                                                    充值
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/grade')): ?>
                                                <a class="j-grade tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>"
                                                   data-grade="<?= $item['grade_id'] ?>"
                                                   title="修改会员等级">
                                                    <i class="iconfont icon-grade-o"></i>
                                                    会员等级
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('user/delete')): ?>
                                                <a class="j-delete tpl-table-black-operation-default"
                                                   href="javascript:void(0);"
                                                   data-id="<?= $item['user_id'] ?>" title="删除用户">
                                                    <i class="icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <div class="j-opSelect operation-select relative inline-block group">
                                                <button type="button"
                                                        class="btn btn-sm btn-secondary">
                                                    <span>更多</span>
                                                    <span class="icon-caret-down"></span>
                                                </button>
                                                <ul class="dropdown-menu absolute right-0 z-10 mt-1 hidden w-44 rounded border border-gray-200 bg-white shadow-lg group-hover:block" data-id="<?= $item['user_id'] ?>">
                                                    <?php if (checkPrivilege('order/all_list')): ?>
                                                        <li>
                                                            <a class="" target="_blank"
                                                               href="<?= url('order/all_list', ['user_id' => $item['user_id']]) ?>">用户订单</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.recharge/order')): ?>
                                                        <li>
                                                            <a class="" target="_blank"
                                                               href="<?= url('user.recharge/order', ['user_id' => $item['user_id']]) ?>">充值记录</a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <?php if (checkPrivilege('user.balance/log')): ?>
                                                        <li>
                                                            <a class="" target="_blank"
                                                               href="<?= url('user.balance/log', ['user_id' => $item['user_id']]) ?>">余额明细</a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="lg:w-full ">
                        <div class="float-right"><?= $list->render() ?> </div>
                        <div class="float-right pagination-total mr-4">
                            <div class="align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 模板：修改会员等级 -->
<script id="tpl-grade" type="text/template">
    <div class="p-1 pt-4">
        <form class="tpl-form-line-form" method="post" action="">
            <div class="p-0">
                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                    <label class="sm:w-1/4 form-require">
                        会员等级
                    </label>
                    <div class="sm:w-2/3 ">
                        <select name="grade[grade_id]" class="form-select">
                            <option value="0">无等级</option>
                            <?php foreach ($gradeList as $item): ?>
                                <option value="<?= $item['grade_id'] ?>" {{
                                        grade== '<?= $item['grade_id'] ?>' ? 'selected' : '' }}><?= $item['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 管理员备注 </label>
                    <div class="sm:w-2/3 ">
                                <textarea rows="2" name="grade[remark]" placeholder="请输入管理员备注"
                                          class="border-green-500"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 模板：用户充值 -->
<script id="tpl-recharge" type="text/template">
    <div class="p-1 pt-3">
        <form class="tpl-form-line-form" method="post" action="">
            <div class="j-tabs js-tabs">

                <ul class="flex flex-wrap border-b border-gray-200 mb-4">
                    <li class="mr-1 -mb-px"><a class="inline-block py-2 px-4 border-b-2 border-blue-500 text-blue-600" href="javascript:;" data-target="#tab1">充值余额</a></li>
                    <li class="mr-1 -mb-px"><a class="inline-block py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700" href="javascript:;" data-target="#tab2">充值积分</a></li>
                </ul>

                <div class="p-1">

                    <div class="js-tab-panel p-0" id="tab1">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                当前余额
                            </label>
                            <div class="sm:w-2/3 ">
                                <div class="text-sm text-gray-700">{{ balance }}</div>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                充值方式
                            </label>
                            <div class="sm:w-2/3 ">
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[balance][mode]"
                                           value="inc" checked>
                                    增加
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[balance][mode]" value="dec">
                                    减少
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[balance][mode]" value="final">
                                    最终金额
                                </label>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                变更金额
                            </label>
                            <div class="sm:w-2/3 ">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的金额" name="recharge[balance][money]" value="" required>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                管理员备注
                            </label>
                            <div class="sm:w-2/3 ">
                                <textarea rows="2" name="recharge[balance][remark]" placeholder="请输入管理员备注"
                                          class="border-green-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="js-tab-panel p-0 hidden" id="tab2">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                当前积分
                            </label>
                            <div class="sm:w-2/3 ">
                                <div class="text-sm text-gray-700">{{ points }}</div>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                充值方式
                            </label>
                            <div class="sm:w-2/3 ">
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[points][mode]"
                                           value="inc" checked>
                                    增加
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[points][mode]" value="dec">
                                    减少
                                </label>
                                <label class="inline-flex items-center gap-2 mr-4">
                                    <input type="radio" class="w-4 h-4 accent-blue-600" name="recharge[points][mode]" value="final">
                                    最终积分
                                </label>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                变更数量
                            </label>
                            <div class="sm:w-2/3 ">
                                <input type="number" min="0" class="tpl-form-input"
                                       placeholder="请输入要变更的数量" name="recharge[points][value]" value="" required>
                            </div>
                        </div>
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">
                                管理员备注
                            </label>
                            <div class="sm:w-2/3 ">
                                <textarea rows="2" name="recharge[points][remark]" placeholder="请输入管理员备注"
                                          class="border-green-500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</script>

<script>
    $(function () {

        /**
         * 账户充值
         */
        $('.j-recharge').on('click', function () {
            var $tabs, data = $(this).data();
            $.showModal({
                title: '用户充值'
                , area: '460px'
                , content: template('tpl-recharge', data)
                , uCheck: true
                , success: function ($content) {
                    $tabs = $content.find('.j-tabs');
                    // amazeui 移除后自实现切换（与 layout 的 js-tabs 行为一致）
                    $tabs.find('ul > li > a[href="javascript:;"]').on('click', function (e) {
                        e.preventDefault();
                        var $a = $(this), target = $a.data('target');
                        $tabs.find('ul > li > a')
                            .removeClass('border-blue-500 text-blue-600')
                            .addClass('border-transparent text-gray-500 hover:text-gray-700');
                        $a.removeClass('border-transparent text-gray-500 hover:text-gray-700')
                            .addClass('border-blue-500 text-blue-600');
                        $tabs.find('.js-tab-panel').addClass('hidden');
                        $tabs.find(target).removeClass('hidden');
                    });
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/recharge') ?>',
                        data: {
                            user_id: data.id,
                            source: $tabs.find('.js-tab-panel').index($tabs.find('.js-tab-panel:not(.hidden)'))
                        }
                    });
                    return true;
                }
            });
        });

        /**
         * 修改会员等级
         */
        $('.j-grade').on('click', function () {
            var data = $(this).data();
            $.showModal({
                title: '修改会员等级'
                , area: '460px'
                , content: template('tpl-grade', data)
                , uCheck: true
                , success: function ($content) {
                }
                , yes: function ($content) {
                    $content.find('form').myAjaxSubmit({
                        url: '<?= url('user/grade') ?>',
                        data: {user_id: data.id}
                    });
                    return true;
                }
            });
        });

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.j-opSelect');
        $dropdown.on('click', 'li a', function () {
            var $this = $(this);
            var id = $this.parent().parent().data('id');
            var type = $this.data('type');
            if (type === 'delete') {
                layer.confirm('删除后不可恢复，确定要删除吗？', function (index) {
                    $.post("index.php?s=/store/apps.dealer.user/delete", {dealer_id: id}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url)
                            : $.show_error(result.msg);
                    });
                    layer.close(index);
                });
            }
        });

        // 删除元素
        var url = "<?= url('user/delete') ?>";
        $('.j-delete').delete('user_id', url, '删除后不可恢复，确定要删除吗？');

    });
</script>

