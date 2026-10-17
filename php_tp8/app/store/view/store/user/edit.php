<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑管理员</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">用户名 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="user[user_name]"
                                           value="<?= $model['user_name'] ?>" placeholder="请输入用户名" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">所属角色 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="role-select">
                                        <div class="role-select-toggle">
                                            <span class="role-select-label role-select-placeholder"
                                                  data-placeholder="请选择角色">请选择角色</span>
                                            <span class="icon-caret-down"></span>
                                        </div>
                                        <div class="role-select-menu hidden">
                                            <?php $checkedRoleIds = (array)($roleIds ?? []); ?>
                                            <?php if (!empty($roleList)): foreach ($roleList as $role): ?>
                                                <label class="role-select-item">
                                                    <input type="checkbox" name="user[role_ids][]"
                                                           value="<?= $role['role_id'] ?>"
                                                           data-name="<?= $role['role_name_h1'] ?>"
                                                        <?= in_array($role['role_id'], $checkedRoleIds) ? 'checked' : '' ?>>
                                                    <span><?= $role['role_name_h1'] ?></span>
                                                </label>
                                            <?php endforeach; else: ?>
                                                <div class="role-select-empty">暂无可选角色，请先到「角色管理」添加角色</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="help-block">
                                        <small>注：支持多选</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">登录密码 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="password" class="tpl-form-input" name="user[password]"
                                           value="" placeholder="请输入登录密码">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">确认密码 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="password" class="tpl-form-input" name="user[password_confirm]"
                                           value="" placeholder="请输入确认密码">
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">姓名 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="user[real_name]"
                                           value="<?= $model['real_name'] ?>">
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
<script>
    $(function () {

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
