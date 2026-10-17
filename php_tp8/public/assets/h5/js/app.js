(function () {
    // 仅装修页（注入 H5_PAGE_DATA）才交给 Vue 接管；分类/购物车/我的等静态页直接跳过
    if (!window.H5_PAGE_DATA) return;
    var raw = window.H5_PAGE_DATA || {};
    var items = raw.items || [];
    var page  = raw.page  || {};
    // items 可能是 {0:..., 1:...} 这种对象，转数组
    if (!Array.isArray(items)) {
        items = Object.keys(items).sort(function (a, b) { return a - b; }).map(function (k) { return items[k]; });
    }

    var app = Vue.createApp({
        data: function () {
            return {
                items: items,
                page: page,
                bannerIdx: 0,
                _bannerTimer: null,
                searchKeyword: ""
            };
        },
        mounted: function () {
            var self = this;
            // 首屏 banner 自动轮播（仅当只有 1 个 banner 组件时自动轮播第一个）
            var bannerIdx = -1;
            for (var i = 0; i < self.items.length; i++) {
                if (self.items[i].type === 'banner') { bannerIdx = i; break; }
            }
            if (bannerIdx >= 0 && self.items[bannerIdx].data && self.items[bannerIdx].data.length > 1) {
                self._bannerTimer = setInterval(function () {
                    self.bannerIdx = (self.bannerIdx + 1) % self.items[bannerIdx].data.length;
                }, 3000);
            }
        },
        beforeUnmount: function () {
            if (this._bannerTimer) clearInterval(this._bannerTimer);
        },
        methods: {
            /**
             * 统一拿组件要渲染的列表（后端动态展开的 item.data 或 defaultData 兜底）
             */
            pickList: function (item) {
                if (!item) return [];
                var source = (item.params && item.params.source) || 'auto';
                var data = (source === 'choice' ? item.data : null) || item.data || item.defaultData || [];
                return Array.isArray(data) ? data : [];
            },
            /**
             * 链接跳转（简单处理：相对路径直接 href，外部链接打开新窗）
             */
            onLink: function (url) {
                if (!url) return;
                if (/^https?:\/\//i.test(url)) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            },
            doSearch: function () {
                var kw = (this.searchKeyword || "").trim();
                if (!kw) {
                    // 没输入关键词 → 跳到全部商品列表
                    window.location.href = "/h5/goods/list.html";
                    return;
                }
                window.location.href = "/h5/goods/list.html?keyword=" + encodeURIComponent(kw);
            }
        }
    });

    app.mount('#h5-app');
})();

/* ============================================================
 * H5 底部导航登录门禁
 * —— 点击"我的" tab 时检查 localStorage['h5_token']
 *    未登录 → 跳转 /h5/passport/login?redirect=<当前页>
 *    已登录 → 正常跳转 /h5/user/index.html
 *
 * 同时在所有 H5 页面拦截直接访问 /h5/user/index.html 的场景
 * （后端也会 302 重定向，这里做前端兜底，体验更流畅）
 * ============================================================ */
(function () {
    var USER_PATH = '/h5/user/index';
    var LOGIN_URL = '/h5/passport/login';

    /**
     * 统一判断登录状态
     */
    function isLogged() {
        try {
            var t = localStorage.getItem('h5_token');
            return !!t;
        } catch (e) {
            return false;
        }
    }

    /**
     * 跳转到登录页（带 redirect 便于登录后回跳）
     */
    function goLogin() {
        var back = encodeURIComponent(window.location.pathname + window.location.search);
        window.location.href = LOGIN_URL + '?redirect=' + back;
    }

    // 1. 底部导航拦截（事件委托，兼容动态渲染的 tabbar）
    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('[data-nav="user"]');
        if (!link) return;
        e.preventDefault();
        if (isLogged()) {
            window.location.href = USER_PATH + '.html';
        } else {
            goLogin();
        }
    });

    // 2. 登录成功后：如果 URL 有 ?redirect 参数则回跳
    //    （passport/login.php 的 doLogin 已经把 redirect 写进 JSON，
    //     前端 fetch 成功后 location.href = data.redirect 即可；
    //     这里额外处理 SSR 渲染的登录页 GET 参数）
    var params = new URLSearchParams(window.location.search);
    if (params.has('login_ok')) {
        var red = params.get('redirect');
        if (red && isLogged()) {
            window.location.replace(red);
        }
    }
})();