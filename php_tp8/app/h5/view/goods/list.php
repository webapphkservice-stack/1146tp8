<?php
// 商品列表 —— Element Plus + Tailwind 组件库版（Fresh Market 主题）
?>
<link rel="stylesheet" href="/assets/common/css/tailwind.css"/>
<link rel="stylesheet" href="/assets/common/plugins/element-plus/index.css"/>
<style>
    /* Fresh Market 主题：仅覆盖 Element Plus 主题变量与少量布局辅助类 */
    #fm-app {
        --el-color-primary: #e8722a;
        --el-color-primary-light-3: #ef9a5e;
        --el-color-primary-light-5: #f4b78e;
        --el-color-primary-light-7: #f9d5bd;
        --el-color-primary-light-8: #fbe3d3;
        --el-color-primary-light-9: #fdf1e6;
        --el-color-primary-dark-2: #ba5b22;
        --el-border-radius-base: 12px;
    }
    /* 只隐藏本页容器；布局 #h5-app 的 v-cloak 在静态页不会被摘除，不能全局隐藏 */
    #fm-app[v-cloak] { display: none; }
    .fm-cream { background: #fef9f0; }
    .fm-green { color: #2d5016; }
    .fm-brown { color: #8b7355; }
    .fm-price { background: #f5c542; color: #8b4513; border: none; font-weight: 600; }
    .fm-goods-card { cursor: pointer; }
    .fm-goods-card .el-card__body { display: flex; gap: 12px; }
</style>

<div id="fm-app" v-cloak class="fm-cream">
    <!-- 搜索栏 -->
    <header class="fm-cream sticky top-0 z-50 border-b" style="border-color: rgba(45,80,22,.08)">
        <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-3">
            <a href="/h5/index/index.html" class="text-xl fm-green" title="返回首页">‹</a>
            <el-input v-model="keyword" placeholder="搜索新鲜好物" clearable
                      style="max-width:420px" @keyup.enter="search">
                <template #append>
                    <el-button @click="search">搜索</el-button>
                </template>
            </el-input>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4" style="padding-top:16px">
        <?php if (!empty($categoryList)): ?>
        <!-- 分类药丸 -->
        <div class="flex flex-wrap gap-2 mb-4">
            <el-check-tag :checked="categoryId === 0" @change="pickCategory(0)">全部</el-check-tag>
            <el-check-tag v-for="c in categories" :key="c.category_id"
                          :checked="categoryId === c.category_id"
                          @change="pickCategory(c.category_id)">{{ c.name }}</el-check-tag>
        </div>
        <?php endif; ?>

        <!-- 商品列表 -->
        <el-row :gutter="16">
            <el-col v-for="g in goods" :key="g.goods_id" :xs="24" :md="12" class="mb-4">
                <el-card shadow="hover" class="fm-goods-card" :body-style="{ padding: '12px' }"
                         @click="go('/h5/goods/detail.html?goods_id=' + g.goods_id)">
                    <img :src="g.goods_image" style="width:96px;height:96px;border-radius:12px;object-fit:cover;flex-shrink:0" alt=""/>
                    <div style="min-width:0;flex:1">
                        <p class="fm-green text-sm" style="margin:0;font-weight:600" :title="g.goods_name">{{ g.goods_name }}</p>
                        <p v-if="g.selling_point" class="fm-brown text-xs" style="margin:4px 0 0">{{ g.selling_point }}</p>
                        <div class="flex items-center gap-2" style="margin-top:10px">
                            <el-tag class="fm-price" color="#f5c542">¥{{ toPrice(g.goods_min_price) }}</el-tag>
                            <span class="fm-brown text-xs">已售 {{ g.goods_sales || 0 }}</span>
                        </div>
                    </div>
                </el-card>
            </el-col>
        </el-row>

        <el-empty v-if="!goods.length" description="暂无相关商品，去逛逛别的吧">
            <el-button type="primary" round @click="go('/h5/index/index.html')">返回首页</el-button>
        </el-empty>

        <?php if (!empty($list['last_page']) && $list['last_page'] > 1): ?>
        <!-- 分页 -->
        <div class="text-center" style="padding:8px 0 24px">
            <el-pagination layout="prev, pager, next" :total="<?= (int)($list['total'] ?? 0) ?>"
                           :page-size="<?= (int)($list['per_page'] ?? 20) ?>"
                           :current-page="<?= (int)($list['current_page'] ?? 1) ?>"
                           @current-change="pickPage"/>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="/assets/common/plugins/element-plus/index.full.min.js"></script>
<script src="/assets/common/plugins/element-plus/zh-cn.js"></script>
<script>
window.__FM_LIST__ = <?= json_encode([
    'categories' => array_values($categoryList ?? []),
    'goods'      => array_values($list['data'] ?? []),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
(function () {
    var d = window.__FM_LIST__ || {};
    var app = Vue.createApp({
        data: function () {
            return {
                keyword: <?= json_encode((string)($keyword ?? ''), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                categoryId: <?= (int)($categoryId ?? 0) ?>,
                categories: d.categories || [],
                goods: d.goods || []
            };
        },
        methods: {
            go: function (url) { window.location.href = url; },
            toPrice: function (v) { return Number(v || 0).toFixed(2); },
            search: function () {
                var qs = [];
                var kw = (this.keyword || '').trim();
                if (kw) qs.push('keyword=' + encodeURIComponent(kw));
                if (this.categoryId) qs.push('category_id=' + this.categoryId);
                this.go('/h5/goods/list.html' + (qs.length ? '?' + qs.join('&') : ''));
            },
            pickCategory: function (id) {
                var qs = [];
                if (this.keyword.trim()) qs.push('keyword=' + encodeURIComponent(this.keyword.trim()));
                if (id) qs.push('category_id=' + id);
                this.go('/h5/goods/list.html' + (qs.length ? '?' + qs.join('&') : ''));
            },
            pickPage: function (p) {
                var qs = ['page=' + p];
                if (this.keyword.trim()) qs.push('keyword=' + encodeURIComponent(this.keyword.trim()));
                if (this.categoryId) qs.push('category_id=' + this.categoryId);
                this.go('/h5/goods/list.html?' + qs.join('&'));
            }
        }
    });
    app.use(ElementPlus, { locale: ElementPlusLocaleZhCn });
    app.mount('#fm-app');
})();
</script>
