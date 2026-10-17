
<link rel="stylesheet" href="assets/store/css/diy.css?v=<?= $version ?>">
<link rel="stylesheet" href="/assets/h5/css/style.css?v=<?= $version ?>">
<div class="row-content">
    <div class="widget">
        <div class="widget-body">
            <!-- diy 工作区 -->
            <div id="app" class="work-diy dis-flex flex-x-between">
                <!-- 工具栏 -->
                <div id="diy-menu" class="diy-menu">
                    <div class="menu-title"><span>组件库</span></div>
                    <div class="navs">
                        <div class="navs-group">
                            <div class="title">媒体组件</div>
                            <div class="navs-components ">
                                <nav class="special" @click="onAddItem('banner')" data-type="banner">
                                    <p class="item-icon"><i class="iconfont icon-tupianlunbo"></i></p>
                                    <p>图片轮播</p>
                                </nav>
                                <nav class="special" @click="onAddItem('imageSingle')" data-type="imageSingle">
                                    <p class="item-icon"><i class="iconfont icon-tupian1"></i></p>
                                    <p>单图组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('window')" data-type="window">
                                    <p class="item-icon"><i class="iconfont icon-newbilayout"></i></p>
                                    <p>图片橱窗</p>
                                </nav>
                                <nav class="special" @click="onAddItem('video')" data-type="video">
                                    <p class="item-icon"><i class="iconfont icon-shipin7"></i></p>
                                    <p>视频组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('article')" data-type="article">
                                    <p class="item-icon"><i class="iconfont icon-zixun"></i></p>
                                    <p>文章组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('special')" data-type="special">
                                    <p class="item-icon"><i class="iconfont icon-toutiao"></i></p>
                                    <p>头条快报</p>
                                </nav>
                            </div>
                            <div class="title">商城组件</div>
                            <div class="navs-components ">
                                <nav class="special" @click="onAddItem('search')" data-type="search">
                                    <p class="item-icon"><i class="iconfont icon-wxbsousuotuiguang"></i></p>
                                    <p>搜索框</p>
                                </nav>
                                <nav class="special" @click="onAddItem('notice')" data-type="notice">
                                    <p class="item-icon"><i class="iconfont icon-gonggao"></i></p>
                                    <p>公告组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('navBar')" data-type="navBar">
                                    <p class="item-icon"><i class="iconfont icon-daohang"></i></p>
                                    <p>导航组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('goods')" data-type="goods">
                                    <p class="item-icon"><i class="iconfont icon-shangpin5"></i></p>
                                    <p>商品组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('coupon')" data-type="coupon">
                                    <p class="item-icon"><i class="iconfont icon-youhuiquan2"></i></p>
                                    <p>优惠券组</p>
                                </nav>
                                <nav class="special" @click="onAddItem('sharingGoods')" data-type="sharingGoods">
                                    <p class="item-icon"><i class="iconfont icon-shangpin5"></i></p>
                                    <p>拼团商品</p>
                                </nav>
                                <nav class="special" @click="onAddItem('bargainGoods')" data-type="bargainGoods">
                                    <p class="item-icon"><i class="iconfont icon-kanjia"></i></p>
                                    <p>砍价商品</p>
                                </nav>
                                <nav class="special" @click="onAddItem('sharpGoods')" data-type="sharpGoods">
                                    <p class="item-icon"><i class="iconfont icon-miaosha"></i></p>
                                    <p>秒杀商品</p>
                                </nav>
                                <nav class="special" @click="onAddItem('shop')" data-type="shop">
                                    <p class="item-icon"><i class="iconfont icon-mendian"></i></p>
                                    <p>线下门店</p>
                                </nav>
                            </div>
                            <div class="title">工具组件</div>
                            <div class="navs-components ">
                                <nav class="special" @click="onAddItem('service')" data-type="service">
                                    <p class="item-icon"><i class="iconfont icon-kefu"></i></p>
                                    <p>在线客服</p>
                                </nav>
                                <nav class="special" @click="onAddItem('officialAccount')" data-type="officialAccount">
                                    <p class="item-icon"><i class="iconfont icon-weixin"></i></p>
                                    <p>关注公众号</p>
                                </nav>
                                <nav class="special" @click="onAddItem('richText')" data-type="richText">
                                    <p class="item-icon"><i class="iconfont icon-wenbenbianji"></i></p>
                                    <p>富文本</p>
                                </nav>
                                <nav class="special" @click="onAddItem('blank')" data-type="blank">
                                    <p class="item-icon"><i class="iconfont icon-kongbai"></i></p>
                                    <p>辅助空白</p>
                                </nav>
                                <nav class="special" @click="onAddItem('guide')" data-type="guide">
                                    <p class="item-icon"><i class="iconfont icon-fengexian1"></i></p>
                                    <p>辅助线</p>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="action">
                        <button @click.stop="onSubmit" type="button" class="btn btn-xs btn-secondary">
                            保存页面
                        </button>
                    </div>
                </div>

                <!--手机diy容器-->
                <div class="diy-phone" v-cloak>
                    <!-- 手机顶部标题 -->
                    <div id="diy-page" class="phone-top optional" @click.stop="onEditer('page')"
                         :class="{selected: 'page' === selectedIndex}"
                         :style="{background: diyData.page.style.titleBackgroundColor}">
                        <h4 :style="{color: diyData.page.style.titleTextColor}">{{ diyData.page.params.title }}</h4>
                    </div>
                    <!-- 小程序内容区域 -->
                    <div id="phone-main" class="phone-main dragArea h5-body" v-cloak>
                            <template v-for="(item, index) in diyData.items" :key="index">

                                <!-- diy元素: 图片轮播（H5 同款） -->
                                <template v-if="item.type == 'banner'">
                                    <div class="drag optional" @click.stop="onEditer(index)"
                                         :class="{selected: index === selectedIndex}">
                                        <div class="h5-banner" v-if="item.data && item.data.length"
                                             :style="{
                                                 paddingTop: (item.style.paddingTop || 0) + 'px',
                                                 paddingLeft: (item.style.paddingLeft || 0) + 'px',
                                                 paddingRight: (item.style.paddingRight || 0) + 'px',
                                                 background: item.style.background
                                             }">
                                            <div class="h5-banner__track">
                                                <div class="h5-banner__slide" v-for="(b, bi) in item.data" :key="bi"
                                                     :class="{ active: bi === 0 }">
                                                    <img :src="b.imgUrl" alt=""/>
                                                </div>
                                            </div>
                                            <div class="h5-banner__dots" :class="item.style.btnShape">
                                                <span v-for="(b, bi) in item.data" :key="bi"
                                                      :class="{ active: bi === 0 }"
                                                      :style="{ background: item.style.btnColor }"></span>
                                            </div>
                                        </div>
                                        <div class="btn-edit-del">
                                            <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 单图组（H5 同款） -->
                                <template v-else-if="item.type == 'imageSingle'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-image-single"
                                                 :style="{ background: item.style.background, paddingTop: item.style.paddingTop + 'px' }">
                                                <div v-for="(img, ii) in item.data" :key="ii"
                                                     class="h5-image-single__wrap"
                                                     :style="{ padding: item.style.paddingTop + 'px ' + item.style.paddingLeft + 'px' }">
                                                    <img :src="img.imgUrl" alt=""/>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 图片橱窗（H5 同款） -->
                                <template v-else-if="item.type == 'window'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-window"
                                                 :style="{ background: item.style.background, padding: item.style.paddingTop + 'px ' + item.style.paddingLeft + 'px' }">
                                                <ul class="h5-window__grid"
                                                    :style="{ gridTemplateColumns: 'repeat(' + (item.style.layout > 0 ? item.style.layout : 2) + ', minmax(0, 1fr))' }">
                                                    <li v-for="(w, wi) in item.data" :key="wi">
                                                        <img :src="w.imgUrl" alt=""/>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 视频组（H5 同款） -->
                                <template v-else-if="item.type == 'video'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-video" :style="{ paddingTop: item.style.paddingTop + 'px' }">
                                                <video :style="{ height: item.style.height + 'px' }"
                                                       :src="item.params.videoUrl"
                                                       :poster="item.params.poster"
                                                       controls playsinline>
                                                    您的浏览器不支持 video 标签
                                                </video>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 文章组（H5 同款） -->
                                <template v-else-if="item.type == 'article'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-article">
                                                <div class="h5-article__item"
                                                     v-for="(a, ai) in (item.params.source == 'choice' ? item.data : item.defaultData)"
                                                     :key="ai">
                                                    <img class="h5-article__img" v-if="a.image" :src="a.image" alt=""/>
                                                    <div class="h5-article__info">
                                                        <p class="h5-article__title">{{ a.article_title }}</p>
                                                        <p class="h5-article__views">{{ (a.show_views || a.views_num || 0) }} 次浏览</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 头条快报（H5 同款） -->
                                <template v-else-if="item.type == 'special'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-special">
                                                <img class="h5-special__icon" :src="item.style.image" alt=""/>
                                                <div class="h5-special__list">
                                                    <span v-for="(s, si) in (item.params.source == 'choice' ? item.data : item.defaultData)"
                                                          :key="si">{{ s.article_title }}</span>
                                                </div>
                                                <span class="h5-special__more">›</span>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 搜索栏（H5 同款） -->
                                <template v-else-if="item.type == 'search'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-search">
                                                <div class="h5-search__inner"
                                                     :class="item.style.searchStyle || 'square'">
                                                    <svg class="h5-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                                    <input type="text" class="h5-search__input"
                                                           :placeholder="item.params.placeholder"
                                                           :style="{ textAlign: item.style.textAlign }"/>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 公告组（H5 同款） -->
                                <template v-else-if="item.type == 'notice'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-notice"
                                                 :style="{ padding: item.style.paddingTop + 'px 12px', background: item.style.background }">
                                                <img class="h5-notice__icon" :src="item.params.icon" alt=""/>
                                                <div class="h5-notice__text">
                                                    <span :style="{ color: item.style.textColor }">{{ item.params.text }}</span>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 导航组（H5 同款） -->
                                <template v-else-if="item.type == 'navBar'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-navbar" :style="{ background: item.style.background }">
                                                <ul class="h5-navbar__list" :class="'cols-' + (item.style.rowsNum || '4')">
                                                    <li v-for="(n, ni) in item.data" :key="ni">
                                                        <img class="h5-navbar__icon" :src="n.imgUrl" alt=""/>
                                                        <p class="h5-navbar__text" :style="{ color: n.color }">{{ n.text }}</p>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 商品组（H5 同款） -->
                                <template v-else-if="item.type == 'goods'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected: index === selectedIndex}">
                                            <div class="h5-goods" :style="{ background: item.style.background }">
                                                <ul class="h5-goods__list" :class="'column-' + item.style.column">
                                                    <li v-for="(g, gi) in (item.params.source == 'choice' ? item.data : item.defaultData)"
                                                        :key="gi" class="h5-goods__item">
                                                        <img class="h5-goods__img" :src="g.image" alt=""/>
                                                        <div class="h5-goods__info">
                                                            <p class="h5-goods__name">{{ g.goods_name }}</p>
                                                            <p class="h5-goods__price">
                                                                <span class="h5-goods__now">¥{{ g.goods_price }}</span>
                                                                <span v-if="g.line_price && Number(g.line_price) > 0"
                                                                      class="h5-goods__old">¥{{ g.line_price }}</span>
                                                            </p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 优惠券组（H5 同款） -->
                                <template v-else-if="item.type == 'coupon'">
                                    <div @click.stop="onEditer(index)"
                                         class="drag optional" :class="{selected: index === selectedIndex}">
                                        <div class="h5-coupon"
                                             :style="{ background: item.style.background, paddingTop: item.style.paddingTop + 'px' }">
                                            <div v-for="(c, ci) in item.data" :key="ci"
                                                 class="h5-coupon__item" :style="{ background: c.color }">
                                                <div class="h5-coupon__left">
                                                    <div class="h5-coupon__price"><em>￥</em><b>{{ c.reduce_price }}</b></div>
                                                    <div class="h5-coupon__condition">满{{ c.min_price }}可用</div>
                                                </div>
                                                <div class="h5-coupon__right">立即领取</div>
                                            </div>
                                        </div>
                                        <div class="btn-edit-del">
                                            <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 拼团商品组（H5 同款） -->
                                <template v-else-if="item.type == 'sharingGoods'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-sharing" :style="{ background: item.style.background }">
                                                <div v-for="(g, gi) in item.data" :key="gi" class="h5-sharing__item">
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
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 砍价商品组（H5 同款） -->
                                <template v-else-if="item.type == 'bargainGoods'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-bargain" :style="{ background: item.style.background }">
                                                <div v-for="(g, gi) in item.data" :key="gi" class="h5-bargain__item">
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
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 秒杀商品组（H5 同款） -->
                                <template v-else-if="item.type == 'sharpGoods'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected: index === selectedIndex}">
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
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 线下门店（H5 同款） -->
                                <template v-else-if="item.type == 'shop'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-shop">
                                                <div v-for="(s, si) in (item.params.source == 'choice' ? item.data : item.defaultData)"
                                                     :key="si" class="h5-shop__item">
                                                    <img class="h5-shop__logo" :src="s.logo_image" alt=""/>
                                                    <div class="h5-shop__info">
                                                        <p class="h5-shop__name">{{ s.shop_name }}</p>
                                                        <p class="h5-shop__addr">{{ ((s.region && s.region.province) || '') + ((s.region && s.region.city) || '') + ((s.region && s.region.region) || '') + (s.address || '') }}</p>
                                                        <p class="h5-shop__phone">📞 {{ s.phone }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 辅助空白 -->
                                <template v-else-if="item.type == 'blank'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-blank"
                                                 :style="{ height: item.style.height + 'px', background: item.style.background }">
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 辅助线 -->
                                <template v-else-if="item.type == 'guide'">
                                    <div @click.stop="onEditer(index)"
                                         class="drag optional" :class="{selected: index === selectedIndex}">
                                        <div class="h5-guide"
                                             :style="{ paddingTop: item.style.paddingTop + 'px', borderTop: item.style.lineHeight + 'px ' + item.style.lineStyle + ' ' + item.style.lineColor }">
                                        </div>
                                        <div class="btn-edit-del">
                                            <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 在线客服（H5 同款） -->
                                <template v-else-if="item.type == 'service'">
                                    <div class="h5-service drag optional drag__nomove" @click.stop="onEditer(index)"
                                         :class="{selected: index === selectedIndex}"
                                         :style="{ right: item.style.right + '%', bottom: item.style.bottom + '%', opacity: item.style.opacity / 100 }">
                                        <img :src="item.params.image" alt="客服"/>
                                        <div class="btn-edit-del">
                                            <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 关注公众号 -->
                                <template v-else-if="item.type == 'officialAccount'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected:index === selectedIndex}">
                                            <div class="h5-official">
                                                <div class="h5-official__inner">
                                                    <p>扫码关注公众号</p>
                                                    <p class="h5-official__tips">需在小程序环境中展示</p>
                                                </div>
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- diy元素: 富文本 -->
                                <template v-else-if="item.type == 'richText'">
                                    <div @click.stop="onEditer(index)">
                                        <div class="drag optional" :class="{selected: index === selectedIndex}">
                                            <div class="h5-richtext"
                                                 :style="{ paddingTop: item.style.paddingTop + 'px', paddingLeft: item.style.paddingLeft + 'px', background: item.style.background }"
                                                 v-html="item.params.content">
                                            </div>
                                            <div class="btn-edit-del">
                                                <div class="btn-del" @click.stop="onDeleleItem(index)">删除</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                            </template>
                    </div>
                </div>

                <!-- 编辑器容器 -->
                <div id="diy-editor" ref="diy-editor" class="diy-editor form-horizontal"
                     :style="{ visibility: selectedIndex != -1 ? 'visible' : 'hidden' } " v-cloak>

                    <!-- 编辑器: 标题栏 -->
                    <div id="tpl_editor_page" v-if="selectedIndex === 'page'">
                        <div class="editor-title"><span>{{ diyData.page.name }}</span></div>
                        <form class="tpl-form-line-form">
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">页面名称 </label>
                                <div class="sm:w-2/3 ">
                                    <input class="tpl-form-input" type="text"
                                           v-model="diyData.page.params.name">
                                    <div class="help-block mt-4-xs">
                                        <small>页面名称仅用于后台查找</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">页面标题 </label>
                                <div class="sm:w-2/3 ">
                                    <input class="tpl-form-input" type="text"
                                           v-model="diyData.page.params.title">
                                    <div class="help-block mt-4-xs">
                                        <small>小程序端顶部显示的标题</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">分享标题 </label>
                                <div class="sm:w-2/3 ">
                                    <input class="tpl-form-input" type="text"
                                           v-model="diyData.page.params.share_title">
                                    <div class="help-block mt-4-xs">
                                        <small>小程序端转发时显示的标题</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">标题栏文字 </label>
                                <div class="sm:w-2/3 ">
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" value="black"
                                               v-model="diyData.page.style.titleTextColor">
                                        黑色
                                    </label>
                                    <label class="inline-flex items-center gap-2 mr-4">
                                        <input type="radio" class="w-4 h-4 accent-blue-600" value="white"
                                               v-model="diyData.page.style.titleTextColor">
                                        白色
                                    </label>
                                </div>
                            </div>
                            <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">标题栏背景 </label>
                                <div class="sm:w-2/3 ">
                                    <input class="" type="color"
                                           v-model="diyData.page.style.titleBackgroundColor">
                                    <button type="button" class="btn-resetColor btn btn-xs"
                                            @click.stop="onEditorResetColor(diyData.page.style, 'titleBackgroundColor', '#ffffff')">
                                        重置
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <template v-if="diyData.items.length && curItem">

                        <!--编辑器: 在线客服-->
                        <div id="tpl_editor_service" v-if="curItem.type == 'service'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 底边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.bottom" min="0" max="100">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.bottom }}</span>%
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 右边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.right" min="0" max="100">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.right }}</span>%
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 不透明度 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.opacity" min="0" max="100">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.opacity }}</span>%
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 客服类型 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="chat"
                                                   v-model="curItem.params.type"> 在线聊天
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="phone"
                                                   v-model="curItem.params.type"> 拨打电话
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2" v-show="curItem.params.type == 'phone'">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 电话号码 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="text" class="tpl-form-input" placeholder="请输入电话号码"
                                               v-model="curItem.params.phone_num">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 客服图标 </label>
                                    <div class="sm:w-2/3 ">
                                        <div class="data-image">
                                            <img :src="curItem.params.image"
                                                 style="height: 45px;" title="点击更换图片" alt=""
                                                 @click="onEditorSelectImage(curItem.params, 'image')">
                                        </div>
                                        <div class="help-block">
                                            <small>建议尺寸：90×90</small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 图片轮播 -->
                        <div id="tpl_editor_banner" v-if="curItem.type == 'banner'">
                            <div class="editor-title"><span>{{curItem.name}}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="help-block"><small>上下留白，单位 px（0~50）</small></div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">左右边距 </label>
                                    <div class="sm:w-3/4 flex items-center gap-3">
                                        <span class="text-sm text-gray-600">左</span>
                                        <input class="tpl-form-input flex-1" type="range"
                                               v-model="curItem.style.paddingLeft" min="0" max="50">
                                        <span class="text-sm text-gray-500 w-8 text-center">
                                            {{ curItem.style.paddingLeft }}px
                                        </span>
                                    </div>
                                    <div class="sm:w-3/4 flex items-center gap-3">
                                        <span class="text-sm text-gray-600">右</span>
                                        <input class="tpl-form-input flex-1" type="range"
                                               v-model="curItem.style.paddingRight" min="0" max="50">
                                        <span class="text-sm text-gray-500 w-8 text-center">
                                            {{ curItem.style.paddingRight }}px
                                        </span>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">指示点形状 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="round"
                                                   v-model="curItem.style.btnShape"> 圆形
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="square"
                                                   v-model="curItem.style.btnShape"> 正方形
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="rectangle"
                                                   v-model="curItem.style.btnShape"> 长方形
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">指示点颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.btnColor">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'btnColor', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 切换时间 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="number"
                                               v-model="curItem.params.interval">
                                        <div class="help-block">
                                            <small>轮播图自动切换的间隔时间，单位：毫秒</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-items">
                                    <div :list="curItem.data"
                                               :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                        <div class="form-item"
                                             v-for="(banner, index) in curItem.data">
                                            <i class="iconfont icon-shanchu item-delete"
                                               @click="onEditorDeleleData(index, selectedIndex)"></i>
                                            <div class="item-inner">
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">图片 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <div class="data-image">
                                                            <img :src="banner.imgUrl" alt=""
                                                                 @click="onEditorSelectImage(banner, 'imgUrl')">
                                                        </div>
                                                        <div class="help-block">
                                                            <small>建议尺寸750x360</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">链接地址 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="text" value=""
                                                               v-model='banner.linkUrl'>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="j-data-add form-item-add" @click="onEditorAddData">
                                    <i class="fa fa-plus"></i> 添加一个
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 单图组-->
                        <div id="tpl_editor_imageSingle" v-if="curItem.type == 'imageSingle'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">左右边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingLeft" min="0"
                                               max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingLeft }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="form-items">
                                    <div :list="curItem.data"
                                               :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                        <div class="form-item drag" v-for="(imageSingle, index) in curItem.data">
                                            <i class="iconfont icon-shanchu item-delete"
                                               @click="onEditorDeleleData(index,selectedIndex)"></i>
                                            <div class="item-inner">
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">图片 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <div class="data-image">
                                                            <img :src="imageSingle.imgUrl" alt=""
                                                                 @click="onEditorSelectImage(imageSingle, 'imgUrl')">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">链接地址 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="text" value="" v-model='imageSingle.linkUrl'>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="j-data-add form-item-add" @click="onEditorAddData">
                                    <i class="fa fa-plus"></i> 添加一个
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 图片橱窗-->
                        <div id="tpl_editor_window" v-if="curItem.type == 'window'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">左右边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingLeft" min="0"
                                               max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingLeft }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">布局方式 </label>
                                    <div class="j-switch-help sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="2"
                                                   v-model="curItem.style.layout"> 堆积两列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="3"
                                                   v-model="curItem.style.layout"> 堆积三列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="4"
                                                   v-model="curItem.style.layout"> 堆积四列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="-1"
                                                   v-model="curItem.style.layout"> 橱窗样式
                                            <small class="help hidden">
                                                最多显示四张图片，超出隐藏
                                            </small>
                                        </label>
                                        <div class="help-block mt-4-xs" data-default="请确保所有图片的尺寸/比例相同。">
                                            <small>请确保所有图片的尺寸/比例相同。</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-items">
                                    <div :list="curItem.data"
                                               :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                        <div class="form-item drag" v-for="(item, index) in curItem.data">
                                            <i class="iconfont icon-shanchu item-delete"
                                               @click="onEditorDeleleData(index,selectedIndex)">
                                            </i>
                                            <div class="item-inner">
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">图片 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <div class="data-image">
                                                            <img :src="item.imgUrl" alt=""
                                                                 @click="onEditorSelectImage(item, 'imgUrl')">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">链接地址 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="text" v-model="item.linkUrl">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="j-data-add form-item-add" @click="onEditorAddData">
                                    <i class="fa fa-plus"></i> 添加一个
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 视频组-->
                        <div id="tpl_editor_video" v-if="curItem.type == 'video'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">视频高度 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.height" min="50" max="500">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.height }}</span>px
                                            (像素)
                                        </div>
                                        <div class="help-block mt-4-xs">
                                            <small>滑块可用左右方向键精确调整</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">视频封面 </label>
                                    <div class="sm:w-2/3 ">
                                        <div class="data-image">
                                            <img :src="curItem.params.poster" alt=""
                                                 @click="onEditorSelectImage(curItem.params, 'poster')">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">视频地址 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="url"
                                               v-model="curItem.params.videoUrl">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">自动播放 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="0"
                                                   v-model="curItem.params.autoplay"> 否
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="1"
                                                   v-model="curItem.params.autoplay"> 是
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 文章组-->
                        <div id="tpl_editor_article" v-if="curItem.type == 'article'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文章分类 </label>
                                    <div class="sm:w-2/3 ">
                                        <select v-model="curItem.params.auto.category" class="form-select">
                                            <option value="0"> -- 全部分类 --</option>
                                            <template v-for="item in opts.articleCatgory">
                                                <option :value="item.category_id"> {{ item.name }}</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示数量 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="number" min="1"
                                               v-model="curItem.params.auto.showNum">
                                        <div class="help-block pt-1">
                                            <small>文章数据请到 <a href="<?= url('content.article/index') ?>" target="_blank">内容管理
                                                    - 文章列表</a>
                                                中管理
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 头条快报 -->
                        <div id="tpl_editor_special" v-if="curItem.type == 'special'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文章分类 </label>
                                    <div class="sm:w-2/3 ">
                                        <select v-model="curItem.params.auto.category" class="form-select">
                                            <option value="0"> -- 全部分类 --</option>
                                            <template v-for="item in opts.articleCatgory">
                                                <option :value="item.category_id"> {{ item.name }}</option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示数量 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="number" min="1"
                                               v-model="curItem.params.auto.showNum">
                                        <div class="help-block pt-1">
                                            <small>文章数据请到 <a href="<?= url('content.article/index') ?>" target="_blank">内容管理
                                                    - 文章列表</a>
                                                中管理
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <hr class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0 form-require">图片 </label>
                                    <div class="sm:w-2/3 ">
                                        <div class="data-image">
                                            <img :src="curItem.style.image" style="height: 38px;" alt=""
                                                 @click="onEditorSelectImage(curItem.style, 'image')">
                                        </div>
                                        <div class="help-block pt-1">
                                            <small>建议尺寸140×38</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0 form-require"> 显示类型 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="1" v-model="curItem.style.display">
                                            单行
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="2" v-model="curItem.style.display">
                                            两行 </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 搜索栏-->
                        <div id="tpl_editor_search" v-if="curItem.type == 'search'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">提示文字 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="text"
                                               v-model="curItem.params.placeholder">
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">搜索框样式 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="square"
                                                   v-model="curItem.style.searchStyle"> 方形
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="radius"
                                                   v-model="curItem.style.searchStyle"> 圆角
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="round"
                                                   v-model="curItem.style.searchStyle"> 圆弧
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文字对齐 </label>
                                    <div class="sm:w-3/4 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="left"
                                                   v-model="curItem.style.textAlign">
                                            居左
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="center"
                                                   v-model="curItem.style.textAlign">
                                            居中
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="right"
                                                   v-model="curItem.style.textAlign">
                                            居右
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 公告组-->
                        <div id="tpl_editor_notice" v-if="curItem.type == 'notice'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文字颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.textColor">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'textColor', '#000000')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">公告图标 </label>
                                    <div class="sm:w-2/3 ">
                                        <div class="data-image">
                                            <img :src="curItem.params.icon"
                                                 @click="onEditorSelectImage(curItem.params, 'icon')"
                                                 style="height: 30px;" alt="">
                                        </div>
                                        <div class="help-block">
                                            <small>建议尺寸：32×32</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">公告内容</label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="text"
                                               v-model="curItem.params.text">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 导航组-->
                        <div id="tpl_editor_navBar" v-if="curItem.type == 'navBar'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">每行数量 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="3"
                                                   v-model="curItem.style.rowsNum"> 3个
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="4"
                                                   v-model="curItem.style.rowsNum"> 4个
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="5"
                                                   v-model="curItem.style.rowsNum"> 5个
                                        </label>
                                    </div>
                                </div>
                                <div class="form-items">
                                    <div :list="curItem.data"
                                               :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                        <div class="form-item drag" v-for="(navBar, index) in curItem.data">
                                            <i class="iconfont icon-shanchu item-delete"
                                               @click="onEditorDeleleData(index, selectedIndex)"></i>
                                            <div class="item-inner">
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">图片 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <div class="data-image">
                                                            <img :src="navBar.imgUrl" alt=""
                                                                 @click="onEditorSelectImage(navBar, 'imgUrl')">
                                                        </div>
                                                        <div class="help-block">
                                                            <small>建议尺寸100x100</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文字内容 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="text" v-model="navBar.text">
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">文字颜色 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="color" v-model="navBar.color">
                                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                                @click.stop="onEditorResetColor(navBar, 'color', '#666666')">
                                                            重置
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">链接地址 </label>
                                                    <div class="sm:w-2/3 ">
                                                        <input type="text" v-model="navBar.linkUrl">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="j-data-add form-item-add" @click="onEditorAddData">
                                    <i class="fa fa-plus"></i> 添加一个
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 商品组-->
                        <div id="tpl_editor_goods" v-if="curItem.type == 'goods'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <!--商品数据-->
                                <div class="j-switch-box" data-item-class="switch-source">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品来源 </label>
                                        <div class="sm:w-2/3 ">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="auto"
                                                       v-model="curItem.params.source"> 自动获取
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="choice"
                                                       v-model="curItem.params.source"> 手动选择
                                            </label>
                                        </div>
                                    </div>
                                    <!--手动选择-->
                                    <div class="switch-source __choice "
                                         v-show="curItem.params.source == 'choice'">
                                        <div class="form-items __goods ">
                                            <div :list="curItem.data"
                                                       :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                                <div v-for="(goods,index) in curItem.data"
                                                     class="form-item drag">
                                                    <i class="iconfont icon-shanchu item-delete" data-no-confirm="true"
                                                       @click="onEditorDeleleData(index, selectedIndex)"></i>
                                                    <div class="item-inner">
                                                        <div class="data-image">
                                                            <img :src="goods.image" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="j-selectGoods form-item-add" @click.stop="onSelectGoods(curItem)">
                                            <i class="fa fa-plus"></i> 选择商品
                                        </div>
                                    </div>
                                    <!-- 自动获取 -->
                                    <div class="switch-source"
                                         v-show="curItem.params.source == 'auto'">
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品分类 </label>
                                            <div class="sm:w-2/3 ">
                                                <select v-model="curItem.params.auto.category" class="form-select">
                                                    <option value="0"> -- 全部分类 --</option>
                                                    <template v-for="first in opts.catgory">
                                                        <option :value="first.category_id"> {{ first.name }}</option>
                                                        <template v-if="first.child">
                                                            <option v-for="two in first.child" :value="two.category_id">
                                                                　{{ two.name }}
                                                            </option>
                                                        </template>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 商品排序 </label>
                                            <div class="sm:w-2/3 ">
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="all"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    综合
                                                </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="sales"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    销量 </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="price"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    价格 </label>
                                            </div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 显示数量 </label>
                                            <div class="sm:w-2/3 ">
                                                <input class="tpl-form-input" type="number" min="1"
                                                       v-model="curItem.params.auto.showNum">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--分割线-->
                                <hr style="" class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4"/>
                                <!--组件样式-->
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#f3f3f3')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示类型 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="list"
                                                   v-model="curItem.style.display"> 列表平铺
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="slide"
                                                   v-model="curItem.style.display"
                                                   :disabled="curItem.style.column == 1"> 横向滑动
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">分列数量 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="1"
                                                   v-model="curItem.style.column"
                                                   :disabled="curItem.style.display == 'slide'"> 单列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="2"
                                                   v-model="curItem.style.column"> 两列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="3"
                                                   v-model="curItem.style.column"> 三列
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示内容 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.goodsName"> 商品名称
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.goodsPrice"> 商品价格
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.linePrice"> 划线价格
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4" v-show="curItem.style.column == 1">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.sellingPoint"> 商品卖点
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4" v-show="curItem.style.column == 1">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.goodsSales"> 商品销量
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 优惠券组 -->
                        <div id="tpl_editor_coupon" v-if="curItem.type == 'coupon'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input type="color" v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示数量 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.params.limit" min="1" max="5">
                                        <div class="display-value">
                                            最多<span class="value">{{ curItem.params.limit }}</span>个
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 拼团商品组 -->
                        <div id="tpl_editor_sharingGoods" v-if="curItem.type == 'sharingGoods'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <!--商品数据-->
                                <div class="j-switch-box" data-item-class="switch-source">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品来源 </label>
                                        <div class="sm:w-2/3 ">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="auto"
                                                       v-model="curItem.params.source"> 自动获取
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="choice"
                                                       v-model="curItem.params.source"> 手动选择
                                            </label>
                                        </div>
                                    </div>
                                    <!--手动选择-->
                                    <div class="switch-source __choice" v-show="curItem.params.source == 'choice'">
                                        <div class="form-items __goods ">
                                            <div :list="curItem.data"
                                                       :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                                <div v-for="(goods, index) in curItem.data"
                                                     class="form-item drag">
                                                    <i class="iconfont icon-shanchu item-delete" data-no-confirm="true"
                                                       @click="onEditorDeleleData(index,selectedIndex)"></i>
                                                    <div class="item-inner">
                                                        <div class="data-image">
                                                            <img :src="goods.image" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="j-selectGoods form-item-add" @click.stop="onSelectGoods(curItem)">
                                            <i class="fa fa-plus"></i> 选择商品
                                        </div>
                                    </div>
                                    <!--自动获取-->
                                    <div class="switch-source __auto" v-show="curItem.params.source !== 'choice'">
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品分类 </label>
                                            <div class="sm:w-2/3 ">
                                                <select v-model="curItem.params.auto.category" class="form-select">
                                                    <option value="0"> -- 全部分类 --</option>
                                                    <template v-for="first in opts.sharingCatgory">
                                                        <option :value="first.category_id"> {{ first.name }}</option>
                                                        <template v-if="first.child">
                                                            <option v-for="two in first.child" :value="two.category_id">
                                                                　{{ two.name }}
                                                            </option>
                                                        </template>
                                                    </template>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品排序 </label>
                                            <div class="sm:w-2/3 ">
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="all"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    综合
                                                </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="sales"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    销量
                                                </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="price"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    价格
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示数量 </label>
                                            <div class="sm:w-2/3 ">
                                                <input class="tpl-form-input" type="number" min="1"
                                                       v-model="curItem.params.auto.showNum">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--分割线-->
                                <hr style="" class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4"/>
                                <!--组件样式-->
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#f3f3f3')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示内容 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.sellingPoint"> 商品卖点
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.sharingPrice"> 拼团价格
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.linePrice"> 划线价格
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 砍价商品组 -->
                        <div id="tpl_editor_bargainGoods" v-if="curItem.type == 'bargainGoods'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <!--商品数据-->
                                <div class="j-switch-box" data-item-class="switch-source">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品来源 </label>
                                        <div class="sm:w-2/3 ">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="auto"
                                                       v-model="curItem.params.source"> 自动获取
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="choice"
                                                       v-model="curItem.params.source"> 手动选择
                                            </label>
                                        </div>
                                    </div>
                                    <!--手动选择-->
                                    <div class="switch-source __choice" v-show="curItem.params.source == 'choice'">
                                        <div class="form-items __goods ">
                                            <div :list="curItem.data"
                                                       :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                                <div v-for="(goods, index) in curItem.data"
                                                     class="form-item drag">
                                                    <i class="iconfont icon-shanchu item-delete" data-no-confirm="true"
                                                       @click="onEditorDeleleData(index,selectedIndex)"></i>
                                                    <div class="item-inner">
                                                        <div class="data-image">
                                                            <img :src="goods.goods_image" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="j-selectGoods form-item-add" @click.stop="onSelectGoods(curItem)">
                                            <i class="fa fa-plus"></i> 选择商品
                                        </div>
                                    </div>
                                    <!--自动获取-->
                                    <div class="switch-source __auto" v-show="curItem.params.source !== 'choice'">
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品排序 </label>
                                            <div class="sm:w-2/3 ">
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="all"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    综合
                                                </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="sales"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    销量
                                                </label>
                                                <label class="inline-flex items-center gap-2 mr-4">
                                                    <input type="radio" class="w-4 h-4 accent-blue-600" value="price"
                                                           v-model="curItem.params.auto.goodsSort">
                                                    价格
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示数量 </label>
                                            <div class="sm:w-2/3 ">
                                                <input class="tpl-form-input" type="number" min="1"
                                                       v-model="curItem.params.auto.showNum">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 分割线 -->
                                <hr style="" class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4"/>
                                <!-- 组件样式 -->
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#f3f3f3')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示内容 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.goodsName"> 商品名称
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.peoples"> 正在砍价
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.floorPrice"> 砍价底价
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.originalPrice"> 商品原价
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 秒杀商品组 -->
                        <div id="tpl_editor_sharpGoods" v-if="curItem.type == 'sharpGoods'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <!-- 商品数据 -->
                                <div class="j-switch-box" data-item-class="switch-source">
                                    <!--自动获取-->
                                    <div class="switch-source __auto" v-show="curItem.params.source !== 'choice'">
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品数量 </label>
                                            <div class="sm:w-2/3 ">
                                                <input class="tpl-form-input" type="number" min="1"
                                                       v-model="curItem.params.showNum">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 分割线 -->
                                <hr style="" class="border-t border-gray-200 my-4 border-t border-dashed border-gray-200 my-4"/>
                                <!-- 组件样式 -->
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">分列数量 </label>
                                    <div class="sm:w-2/3 ">
                                        <!--    <label class="inline-flex items-center gap-2 mr-4"> -->
                                        <!--        <input type="radio" class="w-4 h-4 accent-blue-600" value="1" -->
                                        <!--               v-model="curItem.style.column" -->
                                        <!--               :disabled="curItem.style.display == 'slide'"> 单列 -->
                                        <!--    </label> -->
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="2"
                                                   v-model="curItem.style.column"> 两列
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="3"
                                                   v-model="curItem.style.column"> 三列
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">显示内容 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox" value="1"
                                                   v-model="curItem.style.show.goodsName"> 商品名称
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.seckillPrice"> 秒杀价格
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="checkbox"
                                                   v-model="curItem.style.show.originalPrice"> 商品原价
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 辅助空白-->
                        <div id="tpl_editor_blank" v-if="curItem.type == 'blank'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">组件高度 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.height" min="1" max="200">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.height }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 辅助线-->
                        <div id="tpl_editor_guide" v-if="curItem.type == 'guide'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">线条样式 </label>
                                    <div class="sm:w-2/3 ">
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="solid"
                                                   v-model='curItem.style.lineStyle'>
                                            实线
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="dashed"
                                                   v-model='curItem.style.lineStyle'> 虚线
                                        </label>
                                        <label class="inline-flex items-center gap-2 mr-4">
                                            <input type="radio" class="w-4 h-4 accent-blue-600" value="dotted"
                                                   v-model='curItem.style.lineStyle'> 点状
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">线条颜色 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.lineColor">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'lineColor', '#000000')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">线条高度 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.lineHeight" min="1" max="20">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.lineHeight }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/3 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px(像素)
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 富文本-->
                        <div id="tpl_editor_richText" v-if="curItem.type == 'richText'">
                            <div class="editor-title"><span>{{curItem.name}}</span></div>
                            <form class="tpl-form-line-form">
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">上下边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range"
                                               v-model="curItem.style.paddingTop" min="0" max="50">
                                        <div class="display-value">
                                            <span class="value">{{ curItem.style.paddingTop }}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">左右边距 </label>
                                    <div class="sm:w-2/3 ">
                                        <input class="tpl-form-input" type="range" min="0" max="50"
                                               v-model="curItem.style.paddingLeft">
                                        <div class="display-value">
                                            <span class="value">{{curItem.style.paddingLeft}}</span>px
                                            (像素)
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                    <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">背景颜色 </label>
                                    <div class="sm:w-3/4 ">
                                        <input class="" type="color"
                                               v-model="curItem.style.background">
                                        <button type="button" class="btn-resetColor btn btn-xs"
                                                @click.stop="onEditorResetColor(curItem.style, 'background', '#ffffff')">
                                            重置
                                        </button>
                                    </div>
                                </div>
                                <div class="pt-3">
                                    <textarea id="ume-editor" name="editorValue"></textarea>
                                </div>
                            </form>
                        </div>

                        <!--编辑器: 线下门店-->
                        <div id="tpl_editor_shop" v-if="curItem.type == 'shop'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <form class="tpl-form-line-form">
                                <!--商品数据-->
                                <div class="j-switch-box" data-item-class="switch-source">
                                    <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                        <label class="sm:w-1/4 text-sm text-gray-600 shrink-0">商品来源 </label>
                                        <div class="sm:w-2/3 ">
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="auto"
                                                       v-model="curItem.params.source"> 自动获取
                                            </label>
                                            <label class="inline-flex items-center gap-2 mr-4">
                                                <input type="radio" class="w-4 h-4 accent-blue-600" value="choice"
                                                       v-model="curItem.params.source"> 手动选择
                                            </label>
                                        </div>
                                    </div>
                                    <!--手动选择-->
                                    <div class="switch-source __choice"
                                         v-show="curItem.params.source == 'choice'">
                                        <div class="form-items __goods ">
                                            <div :list="curItem.data"
                                                       :options="{ animation: 120, filter: 'input', preventOnFilter: false }">
                                                <div v-for="(shop, index) in curItem.data"
                                                     class="form-item drag">
                                                    <i class="iconfont icon-shanchu item-delete" data-no-confirm="true"
                                                       @click="onEditorDeleleData(index, selectedIndex)"></i>
                                                    <div class="item-inner">
                                                        <div class="data-image"><img :src="shop.logo_image" alt="">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="j-selectShop form-item-add" @click.stop="onSelectShop(curItem)">
                                            <span>选择门店</span>
                                        </div>
                                    </div>
                                    <!-- 自动获取 -->
                                    <div class="switch-source"
                                         v-show="curItem.params.source == 'auto'">
                                        <div class="mb-4 flex flex-wrap items-start gap-x-2">
                                            <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 展示数量 </label>
                                            <div class="sm:w-2/3 ">
                                                <input class="tpl-form-input" type="number" min="1"
                                                       v-model="curItem.params.auto.showNum">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- 编辑器: 关注公众号 -->
                        <div id="tpl_editor_officialAccount" v-if="curItem.type == 'officialAccount'">
                            <div class="editor-title"><span>{{ curItem.name }}</span></div>
                            <div class="editor-centent">
                                <p>1、使用组件前，需前往<a href="https://mp.weixin.qq.com" target="_blank">小程序后台</a>，在“设置”->“接口设置”->“公众号关注组件”中设置要展示的公众号。</p>
                                <p class="x-mb-10">注：设置的公众号需与小程序主体一致。</p>
                                <p>2、当小程序从扫二维码场景（场景值1011）打开时，才具有展示引导关注公众号组件的能力。</p>
                            </div>
                        </div>

                    </template>
                </div>
            </div>
            <!-- 提示 -->
            <div class="tips mt-6 mb-3">
                <div class="pre">
                    <p>1. 设计完成后点击"保存页面"，在小程序端页面下拉刷新即可看到效果。</p>
                    <p>2. 如需填写链接地址请参考<a href="<?= url('wxapp.page/links') ?>" target="_blank">页面链接</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 文件库弹窗 -->
{{include file="layouts/_template/file_library" /}}

<script src="assets/common/js/vue3.min.js?v=<?= $version ?>"></script>
<script src="assets/common/js/jquery-ui.min.js?v=<?= $version ?>"></script>
<script src="assets/store/js/select.data.js?v=<?= $version ?>"></script>

<script src="assets/store/js/diy.js?v=<?= $version ?>"></script>
<script>

    $(function () {

        // 渲染diy页面
        new diyPhone(<?= $defaultData ?>, <?= $jsonData ?>, <?= $opts ?>);

    });

</script>