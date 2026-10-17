<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">默认航班管理</div>
                    <div class="widget-function float-right">
                        <a class="btn btn-success btn-xs" href="<?= url('flight/add') ?>">新增默认航班</a>
                        <a class="btn btn-primary btn-xs" href="<?= url('flight/setting') ?>">航班接口配置</a>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <form class=" " action="<?= url('flight/index') ?>" method="get">
                        <div class="form-group ">
                            <input type="text" name="origin" placeholder="出发地三字码" value="<?= request()->param('origin') ?>" style="width:130px">
                        </div>
                        <div class="form-group ">
                            <input type="text" name="destination" placeholder="目的地三字码" value="<?= request()->param('destination') ?>" style="width:130px">
                        </div>
                        <div class="form-group ">
                            <button class="btn btn-default" type="submit">搜索</button>
                        </div>
                    </form>
                    <div class="w-full">
                        <div class="overflow-x-auto whitespace-nowrap">
                            <table class="table-base table-base-borderless  whitespace-nowrap">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>标题</th>
                                    <th>航班号</th>
                                    <th>出发地</th>
                                    <th>目的地</th>
                                    <th>出发日期</th>
                                    <th>返程</th>
                                    <th>舱位</th>
                                    <th>货币</th>
                                    <th>命中</th>
                                    <th>排序</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                    <tr>
                                        <td><?= $item['flight_id'] ?></td>
                                        <td><?= $item['title'] ?></td>
                                        <td><?= $item['flight_no'] ?: '—' ?></td>
                                        <td><?= $item['origin'] ?></td>
                                        <td><?= $item['destination'] ?></td>
                                        <td><?= $item['departure_date'] ?></td>
                                        <td><?= $item['return_date'] ?: '—' ?></td>
                                        <td><?= ['', '经济', '超经', '商务', '头等'][$item['cabin']] ?: $item['cabin'] ?></td>
                                        <td><?= $item['currency'] ?></td>
                                        <td><?= $item['hits'] ?></td>
                                        <td><?= $item['sort'] ?></td>
                                        <td>
                                            <a href="<?= url('flight/edit', ['flight_id' => $item['flight_id']]) ?>">编辑</a>
                                            <span class="j-delete" data-id="<?= $item['flight_id'] ?>" style="cursor:pointer;color:#e91e63">删除</span>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="12" class="text-center">暂无默认航班，请点击右上角"新增默认航班"</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="lg:w-full ">
                            <div class="float-right"><?= $list->render() ?></div>
                            <div class="">接口缓存命中数（非默认）：<?= $cache_count ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.j-delete').delete('flight_id', "<?= url('flight/delete') ?>", '确定要删除该默认航班吗？');
    });
</script>
