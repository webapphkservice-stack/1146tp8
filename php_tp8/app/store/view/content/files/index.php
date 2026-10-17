<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">文件列表</div>
                </div>
                <div class="widget-body float-right">
                    <div class="sm:w-full md:w-1/2 lg:w-1/2">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                        </div>
                    </div>
                    <div class="overflow-x-auto sm:w-full">
                        <table width="100%" class="table-base  
                         tpl-table-black whitespace-nowrap">
                            <thead>
                            <tr>
                                <th>文件ID</th>
                                <th>文件名称</th>
                                <th>所属分组</th>
                                <th>存储方式</th>
                                <th>存储域名</th>
                                <th>文件大小</th>
                                <th>文件类型</th>
                                <th>文件后缀</th>
                                <th>上传时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="align-middle"><?= $item['file_id'] ?></td>
                                    <td class="align-middle">
                                        <a href="<?= $item['file_path'] ?>"
                                           target="_blank"><?= $item['file_name'] ?></a>
                                    </td>
                                    <td class="align-middle">
                                        <?= !empty($item['upload_group']) ? $item['upload_group']['group_name'] : '--' ?>
                                    </td>
                                    <td class="align-middle"><?= $item['storage'] ?></td>
                                    <td class="align-middle"><?= $item['file_url'] ?: '--' ?></td>
                                    <td class="align-middle"><?= $item['file_size'] ?></td>
                                    <td class="align-middle"><?= $item['file_type'] ?></td>
                                    <td class="align-middle"><?= $item['extension'] ?></td>
                                    <td class="align-middle"><?=  time_text($item['create_time'])  ?></td>
                                    <td class="align-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('content.files/recycle')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-del"
                                                   data-id="<?= $item['file_id'] ?>">
                                                    <i class="icon-trash"></i> 移入回收站
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

        // 删除元素
        var url = "<?= url('content.files/recovery') ?>";
        $('.item-delete').delete('file_id', url, '确定要移入回收站吗？');

    });
</script>

