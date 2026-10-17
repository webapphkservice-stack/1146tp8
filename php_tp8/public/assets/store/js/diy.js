(function () {

    // 解决火狐浏览器拖动新增tab
    document.body.ondrop = function (event) {
        event.preventDefault();
        event.stopPropagation();
    };

    // 默认数据（由 PHP 注入）
    var defaultData = {};



    /***
     * 前端可视化 diy
     * Vue 3 负责响应式渲染，jQuery UI 1.14.2 负责组件库拖拽 / 手机预览排序
     * @constructor
     */
    function diyPhone(initalData, diyData, opts) {
        defaultData = initalData;

        var app = Vue.createApp({
            data: function () {
                return {
                    // diy数据
                    diyData: diyData,
                    // 当前选中的元素（下标）
                    selectedIndex: -1,
                    // 当前选中的diy元素
                    curItem: {},
                    // 外部数据
                    opts: opts
                };
            },

            mounted: function () {
                var self = this;
                self.initLibraryDrag();
                self.initSortable();
            },

            watch: {
                /**
                 * 监听当前选中组件的 auto 参数变化,实时刷新预览里的商品/文章数据
                 * 用户选分类、排序、显示数量时触发
                 */
                'curItem.params.auto.category': function () {
                    this.refreshAutoSourceData();
                },
                'curItem.params.auto.goodsSort': function () {
                    this.refreshAutoSourceData();
                },
                'curItem.params.auto.showNum': function () {
                    this.refreshAutoSourceData();
                }
            },

            methods: {

                /**
                 * 刷新 auto source 组件的 data(商品/文章列表)
                 * 调后端接口,拿到数据后覆盖 curItem.data
                 */
                refreshAutoSourceData: function () {
                    var self = this;
                    var item = self.curItem;
                    if (!item || !item.type) return;
                    if ((item.params.source || 'auto') !== 'auto') return;

                    if (item.type === 'goods') {
                        $.ajax({
                            url: 'wxapp.page/goods.html',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                category:  (item.params.auto && item.params.auto.category)  || 0,
                                goodsSort: (item.params.auto && item.params.auto.goodsSort) || 'all',
                                showNum:   (item.params.auto && item.params.auto.showNum)   || 10
                            },
                            success: function (res) {
                                if (res && res.code === 1 && res.data) {
                                    item.data = res.data;
                                }
                            },
                            error: function () { /* 静默失败 */ }
                        });
                    } else if (item.type === 'article') {
                        $.ajax({
                            url: 'wxapp.page/article.html',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                category: (item.params.auto && item.params.auto.category) || 0,
                                showNum:  (item.params.auto && item.params.auto.showNum)  || 10
                            },
                            success: function (res) {
                                if (res && res.code === 1 && res.data) {
                                    item.data = res.data;
                                }
                            },
                            error: function () { /* 静默失败 */ }
                        });
                    }
                },

                /**
                 * 初始化组件库拖拽（jQuery UI draggable -> 手机预览 droppable/sortable）
                 */
                initLibraryDrag: function () {
                    var self = this;
                    $('#diy-menu .navs-components nav[data-type]').draggable({
                        connectToSortable: '#phone-main',
                        helper: 'clone',
                        cursor: 'move',
                        zIndex: 9999,
                        opacity: 0.85,
                        start: function (event, ui) {
                            var type = $(this).data('type');
                            ui.helper.data('type', type);
                            $('#phone-main').addClass('drag-active');
                        },
                        stop: function () {
                            $('#phone-main').removeClass('drag-active');
                        }
                    });
                },

                /**
                 * 初始化手机预览排序（jQuery UI sortable）
                 */
                initSortable: function () {
                    var self = this;
                    var $main = $('#phone-main');
                    if ($main.hasClass('ui-sortable')) {
                        $main.sortable('destroy');
                    }
                    self._dragOldIndex = null;
                    $main.sortable({
                        items: '.drag.optional',
                        cancel: '.drag__nomove',
                        placeholder: 'diy-item-placeholder',
                        tolerance: 'pointer',
                        start: function (event, ui) {
                            self._dragOldIndex = ui.item.index();
                        },
                        update: function (event, ui) {
                            self.onSortUpdate(ui);
                        },
                        receive: function (event, ui) {
                            self.onSortReceive(ui);
                        }
                    });
                },

                /**
                 * 拖动排序完成后同步 diyData.items
                 */
                onSortUpdate: function (ui) {
                    var oldIndex = this._dragOldIndex;
                    var newIndex = ui.item.index();
                    if (oldIndex === null || oldIndex === newIndex) {
                        return;
                    }
                    var arr = this.diyData.items;
                    arr.splice(newIndex, 0, arr.splice(oldIndex, 1)[0]);
                    var self = this;
                    this.$nextTick(function () {
                        self.initSortable();
                        if (self.selectedIndex !== -1 && self.selectedIndex !== 'page') {
                            self.selectedIndex = $.inArray(self.curItem, self.diyData.items);
                        }
                    });
                },

                /**
                 * 从组件库拖入新组件
                 */
                onSortReceive: function (ui) {
                    var self = this;
                    var type = ui.item.data('type');
                    var idx = ui.item.index();
                    ui.item.remove();
                    if (!this.onCheckAddItem(type)) {
                        this.$nextTick(function () { self.initSortable(); });
                        return;
                    }
                    var item = $.extend(true, {}, defaultData[type]);
                    this.diyData.items.splice(idx, 0, item);
                    this.$nextTick(function () {
                        self.initSortable();
                        self.onEditer(idx);
                        // 新组件如果是 auto source,立即刷新预览数据
                        self.refreshAutoSourceData();
                    });
                },

                /**
                 * 新增Diy组件（点击组件库）
                 * @param key
                 */
                onAddItem: function (key) {
                    // 验证新增Diy组件
                    if (!this.onCheckAddItem(key)) {
                        return false;
                    }
                    // 复制默认diy组件数据
                    var data = $.extend(true, {}, defaultData[key]);
                    // 新增到diy列表数据
                    this.diyData.items.push(data);
                    // 编辑当前选中的元素
                    this.onEditer(this.diyData.items.length - 1);
                    var self = this;
                    this.$nextTick(function () {
                        self.initSortable();
                        // 新组件如果是 auto source,立即刷新预览数据
                        self.refreshAutoSourceData();
                    });
                },

                /**
                 * 验证新增Diy组件
                 * @param key
                 */
                onCheckAddItem: function (key) {
                    // 验证关注公众号组件只能存在一个
                    if (key === 'officialAccount') {
                        for (var index in this.diyData.items) {
                            if (this.diyData.items.hasOwnProperty(index)) {
                                var item = this.diyData.items[index];
                                if (item.type === 'officialAccount') {
                                    layer.msg('该组件最多存在一个', {anim: 6});
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                },

                /**
                 * 编辑当前选中的Diy元素
                 * @param index
                 */
                onEditer: function (index) {
                    // 记录当前选中元素的索引
                    this.selectedIndex = index;
                    // 当前选中的元素数据
                    this.curItem = this.selectedIndex === 'page' ? this.diyData.page
                        : this.diyData.items[this.selectedIndex];
                    // 注册编辑器事件
                    this.initEditor();
                },

                /**
                 * 删除diy元素
                 * @param index
                 */
                onDeleleItem: function (index) {
                    var _this = this;
                    layer.confirm('确定要删除吗？', function (layIdx) {
                        _this.diyData.items.splice(index, 1);
                        _this.selectedIndex = -1;
                        layer.close(layIdx);
                        _this.$nextTick(function () {
                            _this.initSortable();
                        });
                    });
                },

                /**
                 * 编辑器：选择图片
                 * @param source
                 * @param index
                 */
                onEditorSelectImage: function (source, index) {
                    var self = this;
                    $.fileLibrary({
                        type: 'image',
                        done: function (images) {
                            source[index] = images[0]['file_path'];
                        }
                    });
                },

                /**
                 * 编辑器：重置颜色
                 * @param holder
                 * @param attribute
                 * @param color
                 */
                onEditorResetColor: function (holder, attribute, color) {
                    holder[attribute] = color;
                },

                /**
                 * 编辑器：删除data元素
                 * @param index
                 * @param selectedIndex
                 */
                onEditorDeleleData: function (index, selectedIndex) {
                    if (this.diyData.items[selectedIndex].data.length <= 1) {
                        layer.msg('至少保留一个', {anim: 6});
                        return false;
                    }
                    this.diyData.items[selectedIndex].data.splice(index, 1);
                },

                /**
                 * 编辑器：添加data元素
                 */
                onEditorAddData: function () {
                    // 新增data数据
                    var newDataItem = $.extend(true, {}, defaultData[this.curItem.type].data[0]);
                    this.curItem.data.push(newDataItem);
                },

                /**
                 * 注册编辑器事件
                 */
                initEditor: function () {
                    var self = this;
                    this.$nextTick(function () {
                        // 注册html组件
                        self.editorHtmlComponent();
                        // 富文本事件
                        if (self.curItem.type === 'richText') {
                            self.onRichText(self.curItem);
                        }
                    });
                },

                /**
                 * 编辑器事件：html组件
                 */
                editorHtmlComponent: function () {
                    var $editor = $(this.$refs['diy-editor']);
                    // 单/多选框（Amaze uCheck，jQuery4 下可能不可用，做兼容判断）
                    if ($.fn.uCheck) {
                        $editor.find('input[type=checkbox], input[type=radio]').uCheck();
                    }
                },

                /**
                 * 编辑器事件：拼团商品选择
                 * @param item
                 */
                onSelectGoods: function (item) {
                    var uris = {
                        goods: 'goods/lists?status=10',
                        sharingGoods: 'sharing.goods/lists?status=10',
                        bargainGoods: 'bargain.goods/lists?status=10',
                        sharpGoods: 'sharp.goods/lists?status=10'
                    };
                    var self = this;
                    $.selectData({
                        title: '选择商品',
                        uri: uris[item.type],
                        duplicate: false,
                        dataIndex: 'goods_id',
                        done: function (data) {
                            data.forEach(function (itm) {
                                item.data.push(itm)
                            });
                        },
                        getExistData: function () {
                            var existData = [];
                            item.data.forEach(function (goods) {
                                if (goods.hasOwnProperty('goods_id')) {
                                    existData.push(goods.goods_id);
                                }
                            });
                            return existData;
                        }
                    });
                },

                /**
                 * 选择线下门店
                 * @param item
                 */
                onSelectShop: function (item) {
                    $.selectData({
                        title: '选择门店',
                        uri: 'shop/lists',
                        duplicate: false,
                        dataIndex: 'shop_id',
                        done: function (data) {
                            data.forEach(function (itm) {
                                item.data.push(itm)
                            });
                        },
                        getExistData: function () {
                            var existData = [];
                            item.data.forEach(function (shop) {
                                if (shop.hasOwnProperty('shop_id')) {
                                    existData.push(shop.shop_id);
                                }
                            });
                            return existData;
                        }
                    });
                },

                /**
                 * 编辑器事件：富文本
                 */
                onRichText: function (item) {
                    var $editor = $('#ume-editor');
                    // 富文本改用原生 textarea（不再依赖 umeditor / jQuery4 不兼容）
                    if (!$editor.length) {
                        return;
                    }
                    if (!$editor.is('textarea')) {
                        $editor.replaceWith(
                            '<textarea id="ume-editor" class="richtext" ' +
                            'style="width:100%;height:320px;"></textarea>'
                        );
                    }
                    var $ta = $('#ume-editor');
                    $ta.val(item.params.content || '');
                    $ta.off('input propertychange').on('input propertychange', function () {
                        item.params.content = $ta.val();
                    });
                },

                /**
                 * 提交后端保存
                 * @returns {boolean}
                 */
                onSubmit: function () {
                    if (this.diyData.items.length <= 0) {
                        layer.msg('至少存在一个组件', {anim: 6});
                        return false;
                    }
                    var data = JSON.stringify(this.diyData);
                    var self = this;
                    $.post('', {data: data}, function (result) {
                        result.code === 1 ? $.show_success(result.msg, result.url)
                            : $.show_error(result.msg);
                    });
                }

            }
        });

        app.mount('#app');
    }

    window.diyPhone = diyPhone;

})(window);
