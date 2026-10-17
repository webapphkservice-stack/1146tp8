<div class="h5-shell">
    <!-- 自定义顶部标题栏 -->
    <div class="h5-nav-bar" :style="{ background: page.style.titleBackgroundColor }">
        <h4 :style="{ color: page.style.titleTextColor }">{{ page.params.title || '首页' }}</h4>
    </div>

    <!-- 组件流 -->
    <div class="h5-body">
        <template v-for="(item, idx) in items" :key="idx">
            <!-- 搜索框 -->
            <template v-if="item.type === 'search'">
                <div class="h5-search">
                    <div class="h5-search__inner" :class="item.style.searchStyle || 'square'">
                        <svg class="h5-search__icon" @click.stop="doSearch" style="cursor:pointer" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" class="h5-search__input" :placeholder="item.params.placeholder"
                               :style="{ textAlign: item.style.textAlign }"
                               v-model="searchKeyword"
                               @keyup.enter="doSearch"/>
                    </div>
                </div>
            </template>

            <!-- 图片轮播 -->
            <template v-else-if="item.type === 'banner'">
                <div class="h5-banner" v-if="item.data && item.data.length"
                     :style="{
                         paddingTop: (item.style.paddingTop || 0) + 'px',
                         paddingLeft: (item.style.paddingLeft || 0) + 'px',
                         paddingRight: (item.style.paddingRight || 0) + 'px',
                         background: item.style.background
                     }">
                    <div class="h5-banner__track">
                        <div class="h5-banner__slide" v-for="(b, bi) in item.data" :key="bi"
                             :class="{ active: bi === bannerIdx }"
                             @click="onLink(b.linkUrl)">
                            <img :src="b.imgUrl" alt=""/>
                        </div>
                    </div>
                    <div class="h5-banner__dots" :class="item.style.btnShape">
                        <span v-for="(b, bi) in item.data" :key="bi"
                              :class="{ active: bi === bannerIdx }"
                              :style="{ background: item.style.btnColor }"
                              @click="bannerIdx = bi"></span>
                    </div>
                </div>
            </template>

            <!-- 单图组 -->
            <template v-else-if="item.type === 'imageSingle'">
                <div class="h5-image-single" :style="{ background: item.style.background, paddingTop: item.style.paddingTop + 'px' }">
                    <div v-for="(img, ii) in item.data" :key="ii"
                         class="h5-image-single__wrap"
                         :style="{ padding: item.style.paddingTop + 'px ' + item.style.paddingLeft + 'px' }"
                         @click="onLink(img.linkUrl)">
                        <img :src="img.imgUrl" alt=""/>
                    </div>
                </div>
            </template>

            <!-- 图片橱窗 -->
            <template v-else-if="item.type === 'window'">
                <div class="h5-window" :style="{ background: item.style.background, padding: item.style.paddingTop + 'px ' + item.style.paddingLeft + 'px' }">
                    <ul class="h5-window__grid" :style="{ gridTemplateColumns: 'repeat(' + item.style.layout + ', minmax(0, 1fr))' }">
                        <li v-for="(w, wi) in item.data" :key="wi" @click="onLink(w.linkUrl)">
                            <img :src="w.imgUrl" alt=""/>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- 导航组 -->
            <template v-else-if="item.type === 'navBar'">
                <div class="h5-navbar" :style="{ background: item.style.background }">
                    <ul class="h5-navbar__list" :class="'cols-' + (item.style.rowsNum || '4')">
                        <li v-for="(n, ni) in item.data" :key="ni" @click="onLink(n.linkUrl)">
                            <img class="h5-navbar__icon" :src="n.imgUrl" alt=""/>
                            <p class="h5-navbar__text" :style="{ color: n.color }">{{ n.text }}</p>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- 公告组 -->
            <template v-else-if="item.type === 'notice'">
                <div class="h5-notice" :style="{ padding: item.style.paddingTop + 'px 12px', background: item.style.background }">
                    <img class="h5-notice__icon" :src="item.params.icon" alt=""/>
                    <div class="h5-notice__text">
                        <span :style="{ color: item.style.textColor }">{{ item.params.text }}</span>
                    </div>
                </div>
            </template>

            <!-- 头条快报 -->
            <template v-else-if="item.type === 'special'">
                <div class="h5-special">
                    <img class="h5-special__icon" :src="item.style.image" alt=""/>
                    <div class="h5-special__list">
                        <span v-for="(s, si) in pickList(item)" :key="si">{{ s.article_title }}</span>
                    </div>
                    <span class="h5-special__more">›</span>
                </div>
            </template>

            <!-- 商品组 -->
            <template v-else-if="item.type === 'goods'">
                <div class="h5-goods" :style="{ background: item.style.background }">
                    <ul class="h5-goods__list" :class="'column-' + item.style.column">
                        <li v-for="(g, gi) in pickList(item)" :key="gi" class="h5-goods__item"
                            @click="onLink('/h5/goods/detail.html?goods_id=' + g.goods_id)">
                            <img class="h5-goods__img" :src="g.image" alt=""/>
                            <div class="h5-goods__info">
                                <p class="h5-goods__name">{{ g.goods_name }}</p>
                                <p class="h5-goods__price">
                                    <span class="h5-goods__now">¥{{ g.goods_price }}</span>
                                    <span v-if="g.line_price && Number(g.line_price) > 0" class="h5-goods__old">¥{{ g.line_price }}</span>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- 优惠券组 -->
            <template v-else-if="item.type === 'coupon'">
                <div class="h5-coupon" :style="{ background: item.style.background, paddingTop: item.style.paddingTop + 'px' }">
                    <div v-for="(c, ci) in item.data" :key="ci" class="h5-coupon__item" :style="{ background: c.color }">
                        <div class="h5-coupon__left">
                            <div class="h5-coupon__price"><em>￥</em><b>{{ c.reduce_price }}</b></div>
                            <div class="h5-coupon__condition">满{{ c.min_price }}可用</div>
                        </div>
                        <div class="h5-coupon__right">立即领取</div>
                    </div>
                </div>
            </template>

            <!-- 拼团商品 -->
            <template v-else-if="item.type === 'sharingGoods'">
                <div class="h5-sharing" :style="{ background: item.style.background }">
                    <div v-for="(g, gi) in pickList(item)" :key="gi" class="h5-sharing__item"
                         @click="onLink('/h5/goods/detail.html?goods_id=' + g.goods_id)">
                        <img class="h5-sharing__img" :src="g.image" alt=""/>
                        <div class="h5-sharing__info">
                            <p class="h5-sharing__name">{{ g.goods_name }}</p>
                            <p class="h5-sharing__desc">{{ g.selling_point }}</p>
                            <p class="h5-sharing__price">
                                <span class="h5-sharing__now">¥{{ g.sharing_price }}</span>
                                <span v-if="g.line_price" class="h5-sharing__old">¥{{ g.line_price }}</span>
                            </p>
                        </div>
                        <button class="h5-sharing__btn">去拼团</button>
                    </div>
                </div>
            </template>

            <!-- 砍价商品 -->
            <template v-else-if="item.type === 'bargainGoods'">
                <div class="h5-bargain" :style="{ background: item.style.background }">
                    <div v-for="(g, gi) in pickList(item)" :key="gi" class="h5-bargain__item"
                         @click="onLink('/h5/goods/detail.html?goods_id=' + g.goods_id)">
                        <img class="h5-bargain__img" :src="g.goods_image" alt=""/>
                        <div class="h5-bargain__info">
                            <p class="h5-bargain__name">{{ g.goods_name }}</p>
                            <div class="h5-bargain__price">
                                <span class="h5-bargain__floor">最低¥{{ g.floor_price }}</span>
                                <span class="h5-bargain__orig">¥{{ g.original_price }}</span>
                            </div>
                            <button class="h5-bargain__btn">立即参加</button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 秒杀商品 -->
            <template v-else-if="item.type === 'sharpGoods'">
                <div class="h5-sharp" :style="{ background: item.style.background }">
                    <div class="h5-sharp__head">
                        <span class="h5-sharp__title">⚡ 限时秒杀</span>
                        <span class="h5-sharp__more">更多 ›</span>
                    </div>
                    <ul class="h5-sharp__list" :class="'column-' + item.style.column">
                        <li v-for="(g, gi) in item.data" :key="gi" class="h5-sharp__item">
                            <img class="h5-sharp__img" :src="g.goods_image" alt=""/>
                            <p class="h5-sharp__name">{{ g.goods_name }}</p>
                            <p class="h5-sharp__price">
                                <span class="h5-sharp__now">¥{{ g.seckill_price }}</span>
                                <span v-if="g.original_price" class="h5-sharp__old">¥{{ g.original_price }}</span>
                            </p>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- 文章组 -->
            <template v-else-if="item.type === 'article'">
                <div class="h5-article">
                    <div v-for="(a, ai) in pickList(item)" :key="ai" class="h5-article__item"
                         @click="onLink('/article/detail?article_id=' + a.article_id)">
                        <img class="h5-article__img" v-if="a.image" :src="a.image" alt=""/>
                        <div class="h5-article__info">
                            <p class="h5-article__title">{{ a.article_title }}</p>
                            <p class="h5-article__views">{{ (a.show_views || a.views_num || 0) }} 次浏览</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 视频组 -->
            <template v-else-if="item.type === 'video'">
                <div class="h5-video" :style="{ paddingTop: item.style.paddingTop + 'px' }">
                    <video :src="item.params.videoUrl" :poster="item.params.poster"
                           :style="{ height: item.style.height + 'px' }"
                           :autoplay="item.params.autoplay == 1" controls playsinline></video>
                </div>
            </template>

            <!-- 富文本 -->
            <template v-else-if="item.type === 'richText'">
                <div class="h5-richtext" :style="{ paddingTop: item.style.paddingTop + 'px', paddingLeft: item.style.paddingLeft + 'px', background: item.style.background }">
                    <div v-html="item.params.content"></div>
                </div>
            </template>

            <!-- 辅助空白 -->
            <template v-else-if="item.type === 'blank'">
                <div class="h5-blank" :style="{ height: item.style.height + 'px', background: item.style.background }"></div>
            </template>

            <!-- 辅助线 -->
            <template v-else-if="item.type === 'guide'">
                <div class="h5-guide" :style="{ paddingTop: item.style.paddingTop + 'px', borderTop: item.style.lineHeight + 'px ' + item.style.lineStyle + ' ' + item.style.lineColor }"></div>
            </template>

            <!-- 线下门店 -->
            <template v-else-if="item.type === 'shop'">
                <div class="h5-shop">
                    <div v-for="(s, si) in pickList(item)" :key="si" class="h5-shop__item">
                        <img class="h5-shop__logo" :src="s.logo_image" alt=""/>
                        <div class="h5-shop__info">
                            <p class="h5-shop__name">{{ s.shop_name }}</p>
                            <p class="h5-shop__addr">{{ ((s.region && s.region.province) || '') + ((s.region && s.region.city) || '') + ((s.region && s.region.region) || '') + (s.address || '') }}</p>
                            <p class="h5-shop__phone">📞 {{ s.phone }}</p>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 在线客服（悬浮按钮） -->
            <template v-else-if="item.type === 'service'">
                <div class="h5-service" :style="{ bottom: item.style.bottom + '%', right: item.style.right + '%', opacity: item.style.opacity / 100 }">
                    <img :src="item.params.image" alt="客服"/>
                </div>
            </template>

            <!-- 关注公众号 -->
            <template v-else-if="item.type === 'officialAccount'">
                <div class="h5-official">
                    <div class="h5-official__inner">
                        <p>扫码关注公众号</p>
                        <p class="h5-official__tips">需在小程序环境中展示</p>
                    </div>
                </div>
            </template>
        </template>
    </div>
</div>

<script>
// 由后端注入装修数据
window.H5_PAGE_DATA = <?= json_encode($pageData, JSON_UNESCAPED_UNICODE) ?>;
</script>