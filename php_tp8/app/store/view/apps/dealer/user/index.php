<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">分销商列表</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 工具栏 -->
                    <div class="page_toolbar mb-1 ">
                        <form class="toolbar-form toolbar-form--inline" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="search"
                                                   placeholder="请输入昵称/姓名/手机号"
                                                   value="<?= $request->get('search') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="__overflow-x-auto sm:w-full pb-6">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>用户ID</th>
                                <th>微信头像</th>
                                <th>微信昵称</th>
                                <th>
                                    <p>姓名</p>
                                    <p>手机号</p>
                                </th>
                                <th>
                                    <p>累计佣金</p>
                                    <p>可提现佣金</p>
                                </th>
                                <th>推荐人</th>
                                <th>下级用户</th>
                                <th>成为时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['user_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['avatarUrl'] ?>"
                                                 width="50" height="50" alt="">
                                        </a>
                                    </td>
                                    <td class="align-middle">
                                        <p><span><?= $item['nickName'] ?></span></p>
                                    </td>
                                    <td class="align-middle">
                                        <?php if (!empty($item['real_name']) || !empty($item['mobile'])): ?>
                                            <p><?= $item['real_name'] ?: '--' ?></p>
                                            <p><?= $item['mobile'] ?: '--' ?></p>
                                        <?php else: ?>
                                            <p>--</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <p><?= sprintf('%.2f', $item['money'] + $item['freeze_money'] + $item['total_money']) ?></p>
                                        <p><?= $item['money'] ?></p>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($item['referee_id'] > 0): ?>
                                            <p><?= $item['referee']['nickName'] ?></p>
                                            <p class="text-gray-500 f-12">(ID：<?= $item['referee']['user_id'] ?>)</p>
                                        <?php else: ?>
                                            <p>平台</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle">
                                        <p>
                                            <a href="<?= url('apps.dealer.user/fans', ['user_id' => $item['user_id'], 'level' => 1]) ?>"
                                               target="_blank">一级：<?= $item['first_num'] ?></a>
                                        </p>
                                        <?php if ($basicSetting['level'] >= 2): ?>
                                            <p>
                                                <a href="<?= url('apps.dealer.user/fans', ['user_id' => $item['user_id'], 'level' => 2]) ?>"
                                                   target="_blank">二级：<?= $item['second_num'] ?></a>
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($basicSetting['level'] == 3): ?>
                                            <p>
                                                <a href="<?= url('apps.dealer.user/fans', ['user_id' => $item['user_id'], 'level' => 3]) ?>"
                                                   target="_blank">三级：<?= $item['third_num'] ?></a>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('apps.dealer.user/edit')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('apps.dealer.user/edit', ['dealer_id' => $item['user_id']]) ?>">
                                                    <i class="icon-pencil"></i> 编辑
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege([
                                                'apps.dealer.order/index',
                                                'apps.dealer.withdraw/index',
                                                'apps.dealer.user/delete',
                                                'apps.dealer.user/qrcode',
                                            ], false)): ?>
                                                <div class="operation-select relative inline-block group">
                                                    <button type="button"
                                                            class="btn btn-sm btn-secondary">
                                                        <span>操作</span>
                                                        <span class="icon-caret-down"></span>
                                                    </button>
                                                    <ul class="dropdown-menu absolute right-0 z-10 mt-1 hidden w-44 rounded border border-gray-200 bg-white shadow-lg group-hover:block" data-id="<?= $item['user_id'] ?>">
                                                        <?php if (checkPrivilege('apps.dealer.order/index')): ?>
                                                            <li>
                                                                <a class="" target="_blank"
                                                                   href="<?= url('apps.dealer.order/index', ['user_id' => $item['user_id']]) ?>">分销订单</a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (checkPrivilege('apps.dealer.withdraw/index')): ?>
                                                            <li>
                                                                <a class="" target="_blank"
                                                                   href="<?= url('apps.dealer.withdraw/index', ['user_id' => $item['user_id']]) ?>">提现明细</a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (checkPrivilege('apps.dealer.user/delete')): ?>
                                                            <li>
                                                                <a data-type="delete" class=""
                                                                   href="javascript:void(0);">删除分销商</a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if (checkPrivilege('apps.dealer.user/qrcode')): ?>
                                                            <li>
                                                                <a class=""
                                                                   href="<?= url('apps.dealer.user/qrcode', ['dealer_id' => $item['user_id']]) ?>"
                                                                   target="_blank">分销二维码</a>
                                                            </li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
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
</div>
<script>
    $(function () {

        /**
         * 注册操作事件
         * @type {jQuery|HTMLElement}
         */
        var $dropdown = $('.operation-select');
        // 下拉菜单由 CSS group-hover 控制显示，无需 amazeui dropdown 插件
        $dropdown.on('click', 'li a', function () {
            var $this = $(this)
                , id = $this.parent().parent().data('id');
            if ($this.data('type') === 'delete') {
                layer.confirm(
                    '确定要删除吗？删除后下级成员关系将会中断，不可恢复，请谨慎操作'
                    , {title: '友情提示'}
                    , function (index) {
                        $.post("<?= url('apps.dealer.user/delete') ?>", {dealer_id: id}, function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        });
                        layer.close(index);
                    }
                );
            }
        });

    });
</script>

