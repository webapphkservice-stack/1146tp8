<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">添加店员</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4  lg:w-1/6 form-require"> 选择用户 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="widget-become-goods block mt-4-xs">
                                        <button type="button"
                                                class="j-selectUser upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择用户
                                        </button>
                                        <div class="user-list uploader-list ">
                                        </div>
                                        <div class="block">
                                            <small>选择后不可更改</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 所属门店 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="clerk[shop_id]"
                                            required class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($shopList) && !$shopList->isEmpty()):
                                            foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"><?= $item['shop_name'] ?></option>
                                            <?php endforeach; endif; ?>
                                    </select>
                                    <div class="help-block">
                                        <small>请选择店员所属的门店，用于核销订单</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 店员姓名 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="clerk[real_name]"
                                           placeholder="请输入店员姓名" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 手机号 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="clerk[mobile]"
                                           placeholder="请输入手机号" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require"> 状态 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="clerk[status]" value="1"
                                               checked>
                                        启用
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="clerk[status]" value="0">
                                        禁用
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                    <button type="submit" class="j-submit btn btn-secondary">提交
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 图片文件列表模板 -->
<script id="tpl-user-item" type="text/template">
    {{ each $data }}
    <div class="file-item">
        <a href="{{ $value.avatarUrl }}" title="{{ $value.nickName }} (ID:{{ $value.user_id }})" target="_blank">
            <img src="{{ $value.avatarUrl }}">
        </a>
        <input type="hidden" name="clerk[user_id]" value="{{ $value.user_id }}">
    </div>
    {{ /each }}
</script>

<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>
<script>
    $(function () {

        // 选择用户
        $('.j-selectUser').click(function () {
            var $userList = $('.user-list');
            $.selectData({
                title: '选择用户',
                uri: 'user/lists',
                dataIndex: 'user_id',
                done: function (data) {
                    var user = [data[0]];
                    $userList.html(template('tpl-user-item', user));
                }
            });
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
