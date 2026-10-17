<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">商品评价详情</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">用户 </label>
                                <div class="sm:w-3/4 grow pt-1">
                                    <small class="text-sm"><?= $model['user']['nickName'] ?></small>
                                    <small class="">(用户id：<?= $model['user']['user_id'] ?>)</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品图片 </label>
                                <div class="sm:w-3/4 grow">
                                    <a href="<?= $model['order_goods']['image']['file_path'] ?>"
                                       title="点击查看大图" target="_blank">
                                        <img src="<?= $model['order_goods']['image']['file_path'] ?>" alt="商品图片"
                                             width="80" height="80">
                                    </a>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">商品名称 </label>
                                <div class="sm:w-3/4 grow pt-1">
                                    <small class="text-sm"><?= $model['order_goods']['goods_name'] ?></small>
                                </div>
                            </div>
                            <div class="mt-4-xl">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">评分 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="comment[score]" value="10" 
                                            <?= $model['score'] == 10 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        好评
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="comment[score]" value="20" 
                                            <?= $model['score'] == 20 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        中评
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="comment[score]" value="30" 
                                            <?= $model['score'] == 30 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        差评
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">评价内容 </label>
                                <div class="sm:w-3/4 grow pt-1">
                                    <textarea class="border-green-500" rows="5" placeholder="请输入评价内容"
                                              name="comment[content]" required><?= $model['content'] ?></textarea>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">评价图片 </label>
                                <div class="sm:w-3/4 grow">
                                    <div class="block">
                                        <button type="button" class="upload-file btn btn-secondary rounded">
                                            <i class="icon-cloud-upload"></i> 选择图片
                                        </button>
                                        <div class="uploader-list">
                                            <?php foreach ($model['image'] as $key => $item): ?>
                                                <div class="file-item">
                                                    <a href="<?= $item['file_path'] ?>" title="点击查看大图" target="_blank">
                                                        <img src="<?= $item['file_path'] ?>">
                                                    </a>
                                                    <input type="hidden" name="comment[images][]"
                                                           value="<?= $item['image_id'] ?>">
                                                    <i class="iconfont icon-shanchu file-item-delete"></i>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="help-block mt-3">
                                        <small>最多允许6张，可拖拽调整显示顺序</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">评价排序 </label>
                                <div class="sm:w-3/4 grow">
                                    <input type="number" min="0" class="tpl-form-input" name="comment[sort]"
                                           value="<?= $model['sort'] ?>" required>
                                    <small>数字越小越靠前</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="form-require sm:w-1/4 lg:w-1/6 shrink-0">显示状态 </label>
                                <div class="sm:w-3/4 grow">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="comment[status]" value="1" 
                                            <?= $model['status'] == 1 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        显示
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" name="comment[status]" value="0" 
                                            <?= $model['status'] == 0 ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600" >
                                        隐藏
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 shrink-0">评论时间 </label>
                                <div class="sm:w-3/4 grow pt-1">
                                    <small class="text-sm"><?=  time_text($model['create_time'])  ?></small>
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
{{include file="layouts/_template/tpl_file_item" /}}

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/ddsort.js"></script>
<script>
    $(function () {

        // 选择图片
        $('.upload-file').selectImages({
            name: 'comment[images][]'
            , multiple: true
            , limit: 6
        });

        // 图片列表拖动
        $('.uploader-list').DDSort({
            target: '.file-item',
            delay: 100, // 延时处理，默认为 50 ms，防止手抖点击 A 链接无效
            floatStyle: {
                'border': '1px solid #ccc',
                'background-color': '#fff'
            }
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
