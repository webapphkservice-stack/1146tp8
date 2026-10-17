<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">拼单记录</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full">
                        <div class="overflow-x-auto sm:w-full">
                            <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                                <thead>
                                <tr>
                                    <th>拼单ID</th>
                                    <th>商品图片</th>
                                    <th>商品名称</th>
                                    <th>成团人数</th>
                                    <th>已拼人员</th>
                                    <th>发起人（团长）</th>
                                    <th>拼单结束时间</th>
                                    <th>拼单状态</th>
                                    <th>发起时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                    <tr>
                                        <td class="align-middle"><?= $item['active_id'] ?></td>
                                        <td class="align-middle">
                                            <a href="<?= $item['goods']['image'][0]['file_path'] ?>"
                                               title="点击查看大图" target="_blank">
                                                <img src="<?= $item['goods']['image'][0]['file_path'] ?>"
                                                     width="50" height="50" alt="商品图片">
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <p class="item-title"><?= $item['goods']['goods_name'] ?></p>
                                        </td>
                                        <td class="align-middle"><?= $item['people'] ?>人</td>
                                        <td class="align-middle">
                                            <p><?= $item['actual_people'] ?>人</p>
                                        </td>
                                        <td class="align-middle">
                                            <p class=""><?= $item['user']['nickName'] ?></p>
                                            <p class="text-gray-500">(用户ID：<?= $item['user']['user_id'] ?>)</p>
                                        </td>
                                        <td class="align-middle"><?= $item['end_time']['text'] ?></td>
                                        <td class="align-middle">
                                            <?php if ($item['status']['value'] == 10): ?>
                                                <p>
                                                    <span class="badge badge-secondary"><?= $item['status']['text'] ?></span>
                                                </p>
                                                <?php if ($item['actual_people'] < $item['people']): ?>
                                                    <p>
                                                <span class="badge badge-warning">
                                                    还差 <?= $item['people'] - $item['actual_people'] ?>人
                                                </span>
                                                    </p>
                                                <?php endif; ?>
                                            <?php elseif ($item['status']['value'] == 20): ?>
                                                <span class="badge badge-success"><?= $item['status']['text'] ?></span>
                                            <?php elseif ($item['status']['value'] == 30): ?>
                                                <span class="badge badge-danger"><?= $item['status']['text'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                        <td class="align-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('apps.sharing.active/users')): ?>
                                                    <a class="tpl-table-black-operation-default"
                                                       href="<?= url('apps.sharing.active/users', ['active_id' => $item['active_id']]) ?>">
                                                        <i class="iconfont icon-chengyuan"></i> 拼团成员
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('apps.sharing.order/index')): ?>
                                                    <a class="tpl-table-black-operation-default"
                                                       href="<?= url('apps.sharing.order/index', ['active_id' => $item['active_id']]) ?>">
                                                        <i class="iconfont icon-order-o"></i> 拼团订单
                                                    </a>
                                                <?php endif; ?>
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
<script>
  $(function () {

  });
</script>

