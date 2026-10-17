<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div id="app" class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">发送推送消息</div>
                            </div>
                            <div class="tips mb-4 sm:w-full">
                                <div class="pre">
                                    <p>
                                        注：模板消息只能发送给活跃用户，<a href="<?= url('market.push/user') ?>" target="_blank">查看活跃用户列表</a>，建议每次发送不超过10人。
                                    </p>
                                    <p>注：根据腾讯官方规定，滥用模板消息接口有被封号的风险，请谨慎使用！</p>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/4 form-require">用户ID </label>
                                <div class="sm:w-2/3 ">
                                    <input type="text" class="tpl-form-input" name="send[user_id]"
                                           value="" placeholder="请输入用户ID" required>
                                    <small>如需发送多个用户，请使用英文逗号 <code>,</code> 隔开；例如：10001,10002</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/4 form-require">
                                    模板消息ID <span class="tpl-form-line-small-title">Template ID</span>
                                </label>
                                <div class="sm:w-2/3 ">
                                    <input type="text" class="tpl-form-input" name="send[template_id]"
                                           value="" placeholder="请输入模板消息ID" required>
                                    <small class="ml-1">
                                        <a href="<?= url('store/setting.help/tplmsg') ?>"
                                           target="_blank">如何获取模板消息ID？</a>
                                    </small>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/4 text-sm text-gray-600 shrink-0"> 模板内容1 </label>
                                <div class="sm:w-2/3 ">
                                    <input type="text" class="tpl-form-input" name="send[content][]"
                                           value="" placeholder="请输入模板消息第1行的内容" required>
                                </div>
                            </div>

                            <?php $limit = !!$request->param('more') ? 10 : 5; ?>
                            <?php for ($i = 2; $i <= $limit; $i++): ?>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/4 text-sm text-gray-600 shrink-0"> 模板内容<?= $i ?> </label>
                                    <div class="sm:w-2/3 ">
                                        <input type="text" class="tpl-form-input" name="send[content][]"
                                               value="" placeholder="请输入模板消息第<?= $i ?>行的内容（没有则不填）">
                                    </div>
                                </div>
                            <?php endfor; ?>

                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/4 form-require"> 跳转的页面</label>
                                <div class="sm:w-2/3 ">
                                    <input type="text" class="tpl-form-input" name="send[page]"
                                           value="pages/index/index" placeholder="请输入小程序页面地址" required>
                                    <small class="ml-1">
                                        <span>用户点击消息进入的<a href="<?= url('store/wxapp.page/links') ?>" target="_blank">小程序页面</a>，例如：<code>pages/index/index</code></span>
                                    </small>
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
        $('#my-form').superForm({
            success: function (result) {
                var content = '';
                result.data['stateSet'].forEach(function (value) {
                    content += '<p>' + value + '</p>';
                });
                $.showModal({
                    title: '发送状态'
                    , closeBtn: 0
                    , area: '440px'
                    , btn: ['确定']
                    , btnAlign: 'c'
                    , content: '<div class="p-4 x-f-13">' + content + '</div>'
                    , yes: function () {
                        // window.location.reload();
                        return true;
                    }
                });


            }
        });

    });
</script>
