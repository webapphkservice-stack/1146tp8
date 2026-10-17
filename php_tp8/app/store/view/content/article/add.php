
<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <form id="my-form" class="tpl-form-line-form" method="post">
                    <div class="widget-body">
                        <fieldset>
                            <div class="widget-head">
                                <div class="widget-title">添加文章</div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章标题 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="text" class="tpl-form-input" name="article[article_title]"
                                           value="" placeholder="请输入标题" required>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">文章简介 </label>
                                <div class="sm:w-3/4 ">
                                    <textarea class="tpl-form-input" name="article[summary]" rows="3"
                                              placeholder="选填，用于文章列表展示的简介（建议不超过80字）"></textarea>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章分类 </label>
                                <div class="sm:w-3/4 ">
                                    <select name="article[category_id]" class="form-select">
                                        <option value=""></option>
                                        <?php if (isset($catgory)): foreach ($catgory as $item): ?>
                                            <option value="<?= $item['category_id'] ?>"><?= $item['name'] ?></option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                    <small class="ml-1">
                                        <a href="<?= url('content.article.category/add') ?>">去添加</a>
                                    </small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/6 form-require"> 列表显示方式 </label>
                                <div class="sm:w-5/6">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="article[show_type]"
                                               value="10" checked>
                                        小图模式
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="article[show_type]"
                                               value="20">
                                        大图模式
                                    </label>
                                    <div class="help-block pt-1">
                                        <small>小图模式建议封面图尺寸：300 * 188，大图模式建议封面图尺寸：750 * 455</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章封面图 </label>
                                <div class="sm:w-3/4 ">
                                    <div class="block">
                                        <div class="block">
                                            <button type="button"
                                                    class="upload-file btn btn-secondary rounded">
                                                <i class="icon-cloud-upload"></i> 选择图片（最多9张）
                                            </button>
                                            <div class="uploader-list ">
                                            </div>
                                        </div>
                                    </div>
                                    <p class="help-block">可上传多张封面图，第一张为文章主封面图，最多 9 张</p>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章内容 </label>
                                <div class="sm:w-3/4 ">
                                    <!-- 加载编辑器的容器 -->
                                    <textarea id="container" name="article[article_content]" rows="15"></textarea>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 text-sm text-gray-600 shrink-0">虚拟阅读量 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="article[virtual_views]"
                                           value="0">
                                    <small>显示的阅读量 = 实际阅读量 + 虚拟阅读量</small>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章状态 </label>
                                <div class="sm:w-3/4 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="article[article_status]" value="1"
                                               checked>
                                        显示
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" name="article[article_status]" value="0">
                                        隐藏
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 lg:w-1/6 form-require">文章排序 </label>
                                <div class="sm:w-3/4 ">
                                    <input type="number" class="tpl-form-input" name="article[article_sort]"
                                           value="100" required>
                                    <small>数字越小越靠前</small>
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
<script id="tpl-file-item" type="text/template">
    {{ each list }}
    <div class="file-item">
        <a href="{{ $value.file_path }}" title="点击查看大图" target="_blank">
            <img src="{{ $value.file_path }}">
        </a>
        <input type="hidden" name="{{ name }}" value="{{ $value.file_id }}">
        <i class="iconfont icon-shanchu file-item-delete"></i>
    </div>
    {{ /each }}
</script>

<!-- 封面图预览：竖图/横图按比例适配，限制最大尺寸 -->
<style>
    .uploader-list .file-item {
        display: inline-block;
        background: #f5f5f5;
        padding: 10px;
        border-radius: 8px;
        max-width: 320px;
    }
    .uploader-list .file-item img {
        display: block;
        max-width: 300px;
        max-height: 240px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 6px;
    }
</style>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}


<script>
    $(function () {

        // 选择图片（多图，最多9张）
        $('.upload-file').selectImages({
            name: 'article[image_ids][]'
            , multiple: true
            , limit: 9
        });



        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm();

    });
</script>
