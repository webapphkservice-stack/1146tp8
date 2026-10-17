<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">图标管理</div>
                </div>
                <div class="widget-body float-right">
                    <form id="my-form" class="tpl-form-line-form" method="post">
                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">默认头像图标 </label>
                            <div class="sm:w-3/4 ">
                                <div class="block">
                                    <button type="button"
                                            class="upload-file-icon btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="help-block">
                                        <small>建议尺寸：64×64像素，支持jpg、png格式</small>
                                    </div>
                                </div>
                                <div class="file-list">
                                    <?php if (!empty($model['user_icon'])): ?>
                                        <div class="file-item">
                                            <img src="<?= $model['user_icon'] ?>" alt="">
                                            <input type="hidden" name="icon[user_icon]" value="<?= $model['user_icon'] ?>">
                                            <i class="iconfont icon-shanchu file-item-delete"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-item">
                                            <img src="/assets/user/avatar.png" alt="默认头像">
                                            <span class="file-item-label">默认</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 form-require">首页按钮图标 </label>
                            <div class="sm:w-3/4 ">
                                <div class="block">
                                    <button type="button"
                                            class="upload-file-icon btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="help-block">
                                        <small>建议尺寸：64×64像素，支持jpg、png格式</small>
                                    </div>
                                </div>
                                <div class="file-list">
                                    <?php if (!empty($model['home_icon'])): ?>
                                        <div class="file-item">
                                            <img src="<?= $model['home_icon'] ?>" alt="">
                                            <input type="hidden" name="icon[home_icon]" value="<?= $model['home_icon'] ?>">
                                            <i class="iconfont icon-shanchu file-item-delete"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-item">
                                            <img src="/assets/user/home.png" alt="默认首页图标">
                                            <span class="file-item-label">默认</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">登录页 LOGO </label>
                            <div class="sm:w-3/4 ">
                                <div class="block">
                                    <button type="button"
                                            class="upload-file-icon btn btn-secondary rounded">
                                        <i class="icon-cloud-upload"></i> 选择图片
                                    </button>
                                    <div class="help-block">
                                        <small>用于小程序授权登录页顶部展示，建议尺寸：200×200像素，支持jpg、png格式</small>
                                    </div>
                                </div>
                                <div class="file-list">
                                    <?php if (!empty($model['logo'])): ?>
                                        <div class="file-item">
                                            <img src="<?= $model['logo'] ?>" alt="">
                                            <input type="hidden" name="icon[logo]" value="<?= $model['logo'] ?>">
                                            <i class="iconfont icon-shanchu file-item-delete"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                            <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                <button type="submit" class="j-submit btn btn-secondary">提交保存</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{include file="layouts/_template/file_library" /}}

<style>
    .file-item {
        position: relative;
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
    }
    .file-item img {
        width: 64px;
        height: 64px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    .file-item-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 12px;
        text-align: center;
        padding: 2px 0;
        border-radius: 0 0 4px 4px;
    }
</style>
<script src="assets/store/js/file.library.js"></script>
<script>
    $(function () {
        // 图片上传（按表单组顺序区分字段：0=user_icon 1=home_icon 2=logo）
        $('.upload-file-icon').selectImages({
            name: 'icon[user_icon]',
            multiple: false,
            done: function (data, $this) {
                var groupIndex = $this.parents('.form-group').index();
                var field = groupIndex === 0 ? 'user_icon'
                    : (groupIndex === 1 ? 'home_icon' : 'logo');
                var $list = $this.parents('.form-group').find('.file-list');
                $list.html('');
                $list.append('<div class="file-item">' +
                    '<img src="' + data[0].file_path + '" alt="">' +
                    '<input type="hidden" name="icon[' + field + ']" value="' + data[0].file_path + '">' +
                    '<i class="iconfont icon-shanchu file-item-delete"></i>' +
                    '</div>');
            }
        });

        // 删除图片（事件委托绑定在 form 上，对任意表单组内的图片均生效）
        $('#my-form').on('click', '.file-item-delete', function () {
            $(this).parent().remove();
        });

        // 表单提交
        $('#my-form').superForm();
    });
</script>
