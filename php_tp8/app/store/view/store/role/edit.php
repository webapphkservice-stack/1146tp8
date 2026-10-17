<link rel="stylesheet" href="assets/common/plugins/jstree/themes/default/style.min.css"/>
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">编辑角色</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">角色名称 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="role[role_name]"
                                           value="<?= $model['role_name'] ?>" placeholder="请输入角色名称" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">上级角色 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="role[parent_id]" class="form-select">
                                        <option value="0"> 顶级角色</option>
                                        <?php if (isset($roleList)): foreach ($roleList as $role): ?>
                                            <option value="<?= $role['role_id'] ?>"
                                                <?= $model['parent_id'] == $role['role_id'] ? 'selected' : '' ?>
                                                <?= $model['role_id'] == $role['role_id'] ? 'disabled' : '' ?>>
                                                <?= $role['role_name_h1'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">权限列表 </label>
                                <div class="sm:w-3/4 ">
                                    <div id="jstree"></div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" min="0" class="tpl-form-input" name="role[sort]"
                                           value="<?= $model['sort'] ?>">
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
<script src="assets/common/js/jstree.min.js"></script>
<script>
    $(function () {

        var $jstree = $('#jstree');
        $jstree.jstree({
            icon: false,
            plugins: ['checkbox'],
            core: {
                themes: {icons: false},
                checkbox: {
                    keep_selected_style: false
                },
                data: <?= $accessList ?>
            }
        });

        // 读取选中的条目
        $.jstree.core.prototype.get_all_checked = function (full) {
            var obj = this.get_selected(), i, j;
            for (i = 0, j = obj.length; i < j; i++) {
                obj = obj.concat(this.get_node(obj[i]).parents);
            }
            obj = $.grep(obj, function (v) {
                return v !== '#';
            });
            obj = obj.filter(function (itm, i, a) {
                return i === a.indexOf(itm);
            });
            return full ? $.map(obj, $.proxy(function (i) {
                return this.get_node(i);
            }, this)) : obj;
        };

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            buildData: function () {
                return {
                    role: {
                        access: $jstree.jstree('get_all_checked')
                    }
                }
            }
        });

    });
</script>
