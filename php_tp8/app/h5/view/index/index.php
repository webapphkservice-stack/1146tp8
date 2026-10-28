<?php
// web 首页 —— Element Plus + Tailwind 组件库版（Fresh Market 主题）
// 组件库资源在视图内引入，避免影响未迁移的手写样式页面
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
    @media (min-width: 900px) {
        .fm-desktop-only { display: flex !important; }
        .fm-mobile-only { display: none !important; }
        .fm-hero-pad { padding: 56px 16px 32px; }
    }
    @media (max-width: 899px) {
        .fm-desktop-only { display: none !important; }
        .fm-hero-pad { padding: 28px 16px 20px; }
    }
    .fm-goods-card { cursor: pointer; }
</style>

<div id="fm-app" v-cloak class="fm-cream">
    <!-- 顶部导航 -->
    <header class="fm-cream sticky top-0 z-50 border-b" style="border-color: rgba(45,80,22,.08)">
        <div class="mx-auto max-w-7xl px-4 h-16 flex items-center justify-between">
            <a href="/h5/index/index.html" class="text-lg fm-green" style="font-weight:700">🌿 <?= htmlspecialchars($storeName ?? '') ?></a>
            <nav class="fm-desktop-only items-center gap-4 text-sm fm-green">
                <a href="/h5/index/index.html" style="text-decoration:underline">首页</a>
                <a href="/h5/goods/list.html">全部商品</a>
                <a href="/pages/flight/index">航班查询</a>
                <a href="/pages/taxi/index">出租车预约</a>
            </nav>
            <el-badge :value="cartNum" :hidden="!cartNum">
                <a href="/h5/cart/index.html" title="购物车" class="text-xl">🛒</a>
            </el-badge>
        </div>
    </header>

    <!-- Hero 区块 -->
    <section class="text-center fm-hero-pad">
        <h1 class="fm-green" style="font-size:34px;font-weight:800;margin:0">新鲜好物</h1>
        <p class="mt-3 fm-brown text-sm">产地直供 · 每日新鲜到家</p>
        <el-button type="primary" round size="large" class="mt-4" @click="go('/h5/goods/list.html')">去逛逛</el-button>
    </section>

    <!-- 便捷服务 -->
    <div class="mx-auto max-w-7xl px-4 grid grid-cols-2 gap-4 mb-4">
        <el-card shadow="hover" class="fm-goods-card" @click="go('/pages/flight/index')">
            <div class="flex items-start flex-col">
                <span style="font-size:26px">✈️</span>
                <span class="fm-green text-sm mt-3" style="font-weight:600">航班查询</span>
                <span class="fm-brown text-xs mt-1">免费参考航班</span>
            </div>
        </el-card>
        <el-card shadow="hover" class="fm-goods-card" @click="go('/pages/taxi/index')">
            <div class="flex items-start flex-col">
                <span style="font-size:26px">🚕</span>
                <span class="fm-green text-sm mt-3" style="font-weight:600">出租车预约</span>
                <span class="fm-brown text-xs mt-1">机场接送直达酒店</span>
            </div>
        </el-card>
    </div>

    <?php if (!empty($categoryList)): ?>
    <!-- 分类药丸 -->
    <div class="mx-auto max-w-7xl px-4 flex flex-wrap gap-2 mb-4">
        <el-check-tag v-for="c in categories.slice(0, 6)" :key="c.category_id" :checked="false"
                      @change="go('/h5/goods/list.html?category_id=' + c.category_id)">{{ c.name }}</el-check-tag>
        <el-check-tag :checked="false" @change="go('/h5/goods/list.html')">全部</el-check-tag>
    </div>
    <?php endif; ?>

    <?php if (!empty($goodsList['data'])): ?>
    <!-- 推荐商品 -->
    <div class="mx-auto max-w-7xl px-4 mb-2 fm-green" style="font-size:18px;font-weight:700">推荐好物</div>
    <el-row :gutter="16" class="mx-auto" style="max-width:1200px">
        <el-col v-for="g in goods" :key="g.goods_id" :xs="12" :md="6" class="mb-4">
            <el-card shadow="hover" class="fm-goods-card" :body-style="{ padding: '0' }" @click="go('/h5/goods/detail.html?goods_id=' + g.goods_id)">
                <img :src="g.goods_image" style="width:100%;aspect-ratio:1/1;object-fit:cover;display:block" alt=""/>
                <div style="padding:10px 12px 12px">
                    <p class="fm-green text-sm truncate" style="margin:0" :title="g.goods_name">{{ g.goods_name }}</p>
                    <div class="flex items-center gap-2 mt-3">
                        <el-tag class="fm-price" color="#f5c542">¥{{ toPrice(g.goods_min_price) }}</el-tag>
                        <span class="fm-brown text-xs">已售 {{ g.goods_sales || 0 }}</span>
                    </div>
                </div>
            </el-card>
        </el-col>
    </el-row>
    <div class="text-center pb-6">
        <el-button text type="primary" @click="go('/h5/goods/list.html')">查看全部商品 →</el-button>
    </div>
    <?php else: ?>
    <el-empty description="商品即将上架，敬请期待" />
    <?php endif; ?>

    <!-- 页脚 -->
    <footer class="fm-brown text-center border-t py-4 text-xs" style="border-color: rgba(139,115,85,.18);margin-top:16px">
        © <?= date('Y') ?> <?= htmlspecialchars($storeName ?? '') ?> · 产地直供 · 每日新鲜到家
    </footer>
</div>

<script src="/assets/common/plugins/element-plus/index.full.min.js"></script>
<script src="/assets/common/plugins/element-plus/zh-cn.js"></script>
<script>
window.__FM_HOME__ = <?= json_encode([
    'categories' => array_values($categoryList ?? []),
    'goods'      => array_values($goodsList['data'] ?? []),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
(function () {
    var d = window.__FM_HOME__ || {};
    var app = Vue.createApp({
        data: function () {
            return { cartNum: window.__h5CartNum || 0, categories: d.categories || [], goods: d.goods || [] };
        },
        methods: {
            go: function (url) { window.location.href = url; },
            toPrice: function (v) { return Number(v || 0).toFixed(2); }
        }
    });
    app.use(ElementPlus, { locale: ElementPlusLocaleZhCn });
    app.mount('#fm-app');
})();
</script>
