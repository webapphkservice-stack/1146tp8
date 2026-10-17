<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">出租车设置</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" action="<?= url('taxi/setting') ?>" method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">启用出租车 </label>
                            <div class="sm:w-3/4">
                                <select name="taxi[enable]" class="form-select">
                                    <?php $enable = isset($values['enable']) ? $values['enable'] : 1; ?>
                                    <option value="1" <?= $enable == 1 ? 'selected' : '' ?>>开启</option>
                                    <option value="0" <?= $enable == 0 ? 'selected' : '' ?>>关闭</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">计价货币 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="taxi[currency]"
                                       value="<?= isset($values['currency']) ? $values['currency'] : 'RM' ?>" placeholder="如：RM">
                            </div>
                        </div>

                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">机场与价格 </label>
                            <div class="sm:w-3/4">
                                <div id="airport-list">
                                    <?php $airports = isset($values['airports']) && is_array($values['airports']) ? $values['airports'] : []; ?>
                                    <?php foreach ($airports as $i => $a): ?>
                                    <div class="airport-item mb-1">
                                        <input type="text" name="taxi[airports][<?= $i ?>][name]" value="<?= isset($a['name']) ? $a['name'] : '' ?>" placeholder="机场名称" style="width:160px;display:inline-block">
                                        <input type="number" step="0.01" name="taxi[airports][<?= $i ?>][price_normal]" value="<?= isset($a['price_normal']) ? $a['price_normal'] : '' ?>" placeholder="普通价" style="width:110px;display:inline-block">
                                        <input type="number" step="0.01" name="taxi[airports][<?= $i ?>][price_business]" value="<?= isset($a['price_business']) ? $a['price_business'] : '' ?>" placeholder="商务价" style="width:110px;display:inline-block">
                                        <a href="javascript:;" class="btn btn-xs btn-danger j-del">删除</a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="javascript:;" class="btn btn-xs btn-default mt-4-xs j-add">+ 添加机场</a>
                                <p class="text-xs mt-4-xs">每个机场可设置「普通」与「商务」两种车型价格；小程序端根据用户选择展示对应价格。</p>
                            </div>
                        </div>

                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">车辆管理（派车）</label>
                            <div class="sm:w-3/4">
                                <div id="vehicle-list">
                                    <?php $vehicles = isset($values['vehicles']) && is_array($values['vehicles']) ? $values['vehicles'] : []; ?>
                                    <?php foreach ($vehicles as $i => $v): ?>
                                    <div class="vehicle-item mb-1">
                                        <input type="text" name="taxi[vehicles][<?= $i ?>][type]" value="<?= isset($v['type']) ? $v['type'] : '' ?>" placeholder="车型" style="width:110px;display:inline-block">
                                        <input type="text" name="taxi[vehicles][<?= $i ?>][brand]" value="<?= isset($v['brand']) ? $v['brand'] : '' ?>" placeholder="品牌" style="width:110px;display:inline-block">
                                        <input type="text" name="taxi[vehicles][<?= $i ?>][number]" value="<?= isset($v['number']) ? $v['number'] : '' ?>" placeholder="车号" style="width:110px;display:inline-block">
                                        <input type="text" name="taxi[vehicles][<?= $i ?>][whatsapp]" value="<?= isset($v['whatsapp']) ? $v['whatsapp'] : '' ?>" placeholder="司机WhatsApp" style="width:140px;display:inline-block">
                                        <a href="javascript:;" class="btn btn-xs btn-danger j-del-vehicle">删除</a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="javascript:;" class="btn btn-xs btn-default mt-4-xs j-add-vehicle">+ 添加车辆</a>
                                <p class="text-xs mt-4-xs">维护可派车辆（车型 / 品牌 / 车号 / 司机 WhatsApp），小程序端用户可在出租车页查看司机与车辆信息。</p>
                            </div>
                        </div>

                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%]">
                                <button type="submit" class="btn btn-primary btn-xs j-submit">保存</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        var tpl = '<div class="airport-item mb-1">' +
            '<input type="text" name="taxi[airports][__ID__][name]" placeholder="机场名称" style="width:160px;display:inline-block">' +
            '<input type="number" step="0.01" name="taxi[airports][__ID__][price_normal]" placeholder="普通价" style="width:110px;display:inline-block">' +
            '<input type="number" step="0.01" name="taxi[airports][__ID__][price_business]" placeholder="商务价" style="width:110px;display:inline-block">' +
            '<a href="javascript:;" class="btn btn-xs btn-danger j-del">删除</a></div>';
        $('.j-add').on('click', function () {
            var id = $('#airport-list .airport-item').length;
            $('#airport-list').append(tpl.replace(/__ID__/g, id));
        });
        $('#airport-list').on('click', '.j-del', function () {
            $(this).closest('.airport-item').remove();
        });
        var vTpl = '<div class="vehicle-item mb-1">' +
            '<input type="text" name="taxi[vehicles][__ID__][type]" placeholder="车型" style="width:110px;display:inline-block">' +
            '<input type="text" name="taxi[vehicles][__ID__][brand]" placeholder="品牌" style="width:110px;display:inline-block">' +
            '<input type="text" name="taxi[vehicles][__ID__][number]" placeholder="车号" style="width:110px;display:inline-block">' +
            '<input type="text" name="taxi[vehicles][__ID__][whatsapp]" placeholder="司机WhatsApp" style="width:140px;display:inline-block">' +
            '<a href="javascript:;" class="btn btn-xs btn-danger j-del-vehicle">删除</a></div>';
        $('.j-add-vehicle').on('click', function () {
            var id = $('#vehicle-list .vehicle-item').length;
            $('#vehicle-list').append(vTpl.replace(/__ID__/g, id));
        });
        $('#vehicle-list').on('click', '.j-del-vehicle', function () {
            $(this).closest('.vehicle-item').remove();
        });
        $('#my-form').superForm();
    });
</script>
