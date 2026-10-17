<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-body">
                    <div class="widget-head">
                        <div class="widget-title">分销海报设置</div>
                    </div>
                    <div class="tips mb-4">
                        <div class="pre">
                            <p> 注：可拖动头像、二维码、昵称调整位置，如修改</p>
                            <p> 注：修改后如需生效请前往 <a href="<?= url('setting.cache/clear') ?>" target="_blank">设置-清理缓存</a>，清除临时图片
                            </p>
                        </div>
                    </div>
                    <div id="app" v-cloak class="poster-pannel  pb-8">
                        <div class="pannel__left ">
                            <div id="j-preview" ref="preview" class="poster-preview">
                                <img id="preview-backdrop" class="backdrop" :src="backdrop.src" alt="">
                                <div id="j-qrcode" ref="qrcode" class="drag pre-qrcode" v-bind:class="qrcode.style"
                                     v-bind:style="{ width: qrcode.width + 'px', height: qrcode.width + 'px', top: qrcode.top + 'px', left: qrcode.left + 'px' }">
                                    <img src="assets/store/img/dealer/qrcode.png" alt="">
                                </div>
                                <div id="j-avatar" ref="avatar" v-drag class="drag pre-avatar"
                                     v-bind:class="avatar.style"
                                     v-bind:style="{ width: avatar.width + 'px', height: avatar.width + 'px', top: avatar.top + 'px', left: avatar.left + 'px' }">
                                    <img src="assets/store/img/dealer/avatar.png" alt="">
                                </div>
                                <div id="j-nickName" ref="nickName" class="drag pre-nickName"
                                     v-bind:style="{ fontSize: nickName.fontSize + 'px', color: nickName.color, top: nickName.top + 'px', left: nickName.left + 'px' }">
                                    <span>这里是昵称</span>
                                </div>
                            </div>
                        </div>

                        <div class="pannel__right ">
                            <form id="my-form" class="tpl-form-line-form" method="post">

                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 lg:w-1/3 form-require">海报背景图 </label>
                                    <div class="sm:w-2/3 ">
                                        <div class="block">
                                            <div class="block">
                                                <button type="button"
                                                        class="j-image upload-file btn btn-secondary rounded">
                                                    <i class="icon-cloud-upload"></i> 选择图片
                                                </button>
                                            </div>
                                            <div class="help-block">
                                                <small>尺寸：宽750像素 高大于(等于)1200像素</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 头像宽度 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <input type="number" min="30" class="tpl-form-input" v-model="avatar.width"
                                               required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 头像样式 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="square"
                                                   v-model="avatar.style"> 正方形
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="circle"
                                                   v-model="avatar.style"> 圆形
                                        </label>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 昵称字体大小 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <input type="number" min="12" class="tpl-form-input"
                                               v-model="nickName.fontSize" required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 昵称字体颜色 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <input class="tpl-form-input" type="color" v-model="nickName.color">
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 小程序码宽度 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <input type="number" min="50" class="tpl-form-input" v-model="qrcode.width"
                                               required>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 md:w-1/3 form-require"> 小程序码样式 </label>
                                    <div class="sm:w-3/4 md:w-2/3">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="square" v-model="qrcode.style"
                                                   checked> 正方形
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="circle" v-model="qrcode.style"> 圆形
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <div class="sm:w-3/4 sm:ml-[25%] mt-6">
                                        <button type="submit" class="j-submit btn btn-secondary">提交
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/vue3.min.js"></script>
<script>

    $(function () {

        var appVue = Vue.createApp({
            data() {
                return <?= $data ?>;
            },
            created: function () {
                /**
                 * 注册拖拽事件
                 */
                this.$nextTick(function () {
                    this.dragEvent('qrcode');
                    this.dragEvent('avatar');
                    this.dragEvent('nickName');
                });
            },
            methods: {
                /**
                 * 注册拖拽事件
                 * @param ele
                 */
                dragEvent: function (ele) {
                    var _this = this
                        , $preview = this.$refs.preview
                        , $ele = this.$refs[ele]
                        , l = 0
                        , t = 0
                        , r = $preview.offsetWidth - $ele.offsetWidth
                        , b = $preview.offsetHeight - $ele.offsetHeight;
                    $ele.onmousedown = function (ev) {
                        var sentX = ev.clientX - $ele.offsetLeft;
                        var sentY = ev.clientY - $ele.offsetTop;
                        document.onmousemove = function (ev) {
                            var slideLeft = ev.clientX - sentX;
                            var slideTop = ev.clientY - sentY;
                            slideLeft <= l && (slideLeft = l);
                            slideLeft >= r && (slideLeft = r);
                            slideTop <= t && (slideTop = t);
                            slideTop >= b && (slideTop = b);

                            _this[ele].left = slideLeft;
                            _this[ele].top = slideTop;
                        };
                        document.onmouseup = function () {
                            document.onmousemove = null;
                            document.onmouseup = null;
                        };
                        return false;
                    }
                }
            }
        });
        appVue.mount('#app');

        // 选择图片：分销中心首页
        $('.j-image').selectImages({
            multiple: false,
            done: function (data) {
                appVue.$data.backdrop.src = data[0].file_path;
            }
        });

        /**
         * 表单验证提交
         * @type {*}
         */
        $('#my-form').superForm({
            buildData: function () {
                return {
                    qrcode: appVue.$data
                };
            }
        });

    });

</script>
