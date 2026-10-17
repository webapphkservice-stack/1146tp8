<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">转账场景管理</div>
                    <div class="widget-function float-right">
                        <a class="btn btn-primary btn-xs" href="<?= url('transfer/setting') ?>">转账接口配置</a>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <div class="w-full">
                        <div class="overflow-x-auto whitespace-nowrap">
                            <table class="table-base table-base-borderless  whitespace-nowrap">
                                <thead>
                                <tr>
                                    <th>场景 ID</th>
                                    <th>场景标识</th>
                                    <th>场景名称</th>
                                    <th>子场景</th>
                                    <th>启用状态</th>
                                    <th>备注</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!empty($scenes)): foreach ($scenes as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td><?= $item['identifier'] ?></td>
                                        <td><?= $item['name'] ?></td>
                                        <td><?= $item['sub_scene'] ?></td>
                                        <td>
                                            <?php if ($item['is_enable']): ?>
                                                <span class="badge badge-success text-xs">已启用</span>
                                            <?php else: ?>
                                                <span class="badge badge-default text-xs">已禁用</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['remark'] ?: '—' ?></td>
                                        <td>
                                            <a href="<?= url('transfer/scene_edit', ['scene_id' => $item['id']]) ?>">编辑</a>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">暂无场景数据</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
