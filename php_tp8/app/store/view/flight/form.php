<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title"><?= $model ? '编辑' : '新增' ?>默认航班</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form"
                          action="<?= $model ? url('flight/edit', ['flight_id' => $model['flight_id']]) : url('flight/add') ?>"
                          method="post">
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">标题 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="flight[title]"
                                       value="<?= $model ? $model['title'] : '' ?>" placeholder="如：北京→上海（参考）">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">航班号 </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="flight[flight_no]"
                                       value="<?= $model ? $model['flight_no'] : '' ?>" placeholder="如 CA1831（可选）">
                                <small>该路由的推荐/默认航班号，可在小程序默认航班中展示</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">出发地(三字码) </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="flight[origin]" id="origin"
                                       value="<?= $model ? $model['origin'] : '' ?>" placeholder="如 PEK">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">目的地(三字码) </label>
                            <div class="sm:w-3/4">
                                <input type="text" class="tpl-form-input" name="flight[destination]" id="destination"
                                       value="<?= $model ? $model['destination'] : '' ?>" placeholder="如 SHA">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">出发日期 </label>
                            <div class="sm:w-3/4">
                                <input type="date" name="flight[departure_date]" id="departure_date"
                                       value="<?= $model ? $model['departure_date'] : '' ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">返程日期(可选) </label>
                            <div class="sm:w-3/4">
                                <input type="date" name="flight[return_date]" id="return_date"
                                       value="<?= $model ? $model['return_date'] : '' ?>">
                                <small>填写则为往返行程</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">舱位 </label>
                            <div class="sm:w-3/4">
                                <select name="flight[cabin]" id="cabin" class="form-select">
                                    <?php $cabin = $model ? $model['cabin'] : 1; ?>
                                    <option value="1" <?= $cabin == 1 ? 'selected' : '' ?>>经济舱</option>
                                    <option value="2" <?= $cabin == 2 ? 'selected' : '' ?>>超级经济舱</option>
                                    <option value="3" <?= $cabin == 3 ? 'selected' : '' ?>>商务舱</option>
                                    <option value="4" <?= $cabin == 4 ? 'selected' : '' ?>>头等舱</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">成人数量 </label>
                            <div class="sm:w-3/4">
                                <input type="number" min="1" name="flight[adult]" id="adult"
                                       value="<?= $model ? $model['adult'] : 1 ?>">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">货币 </label>
                            <div class="sm:w-3/4">
                                <input type="text" name="flight[currency]" id="currency"
                                       value="<?= $model ? $model['currency'] : 'CNY' ?>" placeholder="CNY / USD">
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">排序 </label>
                            <div class="sm:w-3/4">
                                <input type="number" name="flight[sort]" value="<?= $model ? $model['sort'] : 0 ?>">
                                <small>数值越小越靠前</small>
                            </div>
                        </div>
                        <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">航班数据(JSON) </label>
                            <div class="sm:w-3/4">
                                <button type="button" class="btn btn-warning btn-xs" id="btn-fetch">从接口拉取并填充</button>
                                <textarea name="flight[data]" id="flight-data" rows="14" style="width:100%;font-family:monospace"><?= $model ? json_encode($model['data'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '' ?></textarea>
                                <small>结构示例：[{ "airline":"航司", "flight_no":"CA1234", "departure_airport":"北京(PEK)", "departure_time":"08:00", "arrival_airport":"上海(SHA)", "arrival_time":"10:20", "duration":"2小时20分", "stops":0, "cabin":"经济舱", "price":980, "extensions":["准点率 85%"] }]</small>
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
        $('#my-form').superForm();
        $('#btn-fetch').click(function () {
            var origin = $('#origin').val();
            var destination = $('#destination').val();
            var date = $('#departure_date').val();
            if (!origin || !destination || !date) {
                layer.alert('请先填写出发地、目的地与出发日期');
                return;
            }
            var index = layer.load();
            $.post("<?= url('flight/preview') ?>", {
                origin: origin,
                destination: destination,
                date: date,
                return_date: $('#return_date').val(),
                cabin: $('#cabin').val(),
                adult: $('#adult').val(),
                currency: $('#currency').val()
            }, function (res) {
                layer.close(index);
                if (res.code === 1 && res.data.flights) {
                    $('#flight-data').val(JSON.stringify(res.data.flights, null, 2));
                    layer.msg('已拉取 ' + res.data.flights.length + ' 条，请检查后保存');
                } else {
                    layer.alert(res.msg ? res.msg : '拉取失败');
                }
            });
        });
    });
</script>
