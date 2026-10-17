<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">马来西亚本地外送</div>
                    <div class="widget-function float-right">
                        <a class="btn btn-success btn-xs" href="<?= url('delivery/add') ?>">新增外送</a>
                        <a class="btn btn-primary btn-xs" href="<?= url('delivery/setting') ?>">外送设置</a>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <form class=" " action="<?= url('delivery/index') ?>" method="get">
                        <div class="form-group ">
                            <input type="text" name="title" placeholder="名称" value="<?= request()->param('title') ?>" style="width:160px">
                        </div>
                        <div class="form-group ">
                            <select name="country" style="width:120px" class="form-select">
                                <option value="">国家</option>
                                <option value="MY" <?= request()->param('country')=='MY'?'selected':'' ?>>马来西亚</option>
                                <option value="CN" <?= request()->param('country')=='CN'?'selected':'' ?>>中国大陆</option>
                            </select>
                        </div>
                        <div class="form-group ">
                            <select name="shop_id" style="width:160px" class="form-select">
                                <option value="">全部店铺</option>
                                <?php foreach ($shops as $sid => $sname): ?>
                                    <option value="<?= $sid ?>" <?= request()->param('shop_id')==$sid?'selected':'' ?>><?= $sname ?></option>
                                <?php endforeach; ?>
                            </select>
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
                                    <th>封面</th>
                                    <th>名称</th>
                                    <th>所属店铺</th>
                                    <th>描述</th>
                                    <th>配送范围</th>
                                    <th>国家</th>
                                    <th>参考价</th>
                                    <th>起送价</th>
                                    <th>配送费</th>
                                    <th>排序</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                    <tr>
                                        <td><?= $item['delivery_id'] ?></td>
                                        <td><img src="<?= $item['image_url'] ?: '/assets/store/img/blank.png' ?>" width="40" height="40"></td>
                                        <td><?= $item['title'] ?></td>
                                        <td><?= ($item['shop_id'] && isset($shops[$item['shop_id']])) ? $shops[$item['shop_id']] : '<span style="color:#999">未归属</span>' ?></td>
                                        <td>
                                            <?php if ($item['description']): ?>
                                                <div style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                                     title="<?= htmlspecialchars($item['description']) ?>"><?= $item['description'] ?></div>
                                            <?php else: ?>
                                                <span style="color:#999">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($item['delivery_range']) && $item['delivery_range']): ?>
                                                <div style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                                                     title="<?= htmlspecialchars($item['delivery_range']) ?>"><?= $item['delivery_range'] ?></div>
                                            <?php else: ?>
                                                <span style="color:#999">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['country'] == 'MY' ? '马来西亚' : '中国大陆' ?></td>
                                        <td><?= $item['price'] ?></td>
                                        <td><?= $item['min_order'] ?></td>
                                        <td><?= $item['delivery_fee'] ?></td>
                                        <td><?= $item['sort'] ?></td>
                                        <td><?= $item['status'] == 1 ? '启用' : '禁用' ?></td>
                                        <td>
                                            <a href="<?= url('delivery/edit', ['delivery_id' => $item['delivery_id']]) ?>">编辑</a>
                                            <span class="j-delete" data-id="<?= $item['delivery_id'] ?>" style="cursor:pointer;color:#e91e63">删除</span>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="13" class="text-center">暂无外送记录</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="lg:w-full ">
                            <div class="float-right"><?= $list->render() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.j-delete').delete('delivery_id', "<?= url('delivery/delete') ?>", '确定要删除该外送吗？');
    });
</script>
