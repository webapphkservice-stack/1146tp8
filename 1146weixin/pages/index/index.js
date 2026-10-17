const App = getApp();

Page({

  data: {
    // 页面参数
    options: {},
    // 页面元素
    items: {},
    scrollTop: 0,
    // 页面配置
    page: {
      style: {
        titleBackgroundColor: '',
        titleTextColor: ''
      }
    },
    // 胶囊按钮背景色
    capsuleBgColor: '',
    // 状态栏高度
    statusBarHeight: 0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    // 获取系统信息
    const systemInfo = wx.getSystemInfoSync();
    // 当前页面参数
    this.setData({
      options,
      statusBarHeight: systemInfo.statusBarHeight
    });
    // 加载页面数据
    this.getPageData();
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    // 延迟更新购物车角标，避免阻塞页面渲染
    setTimeout(() => {
      App.setCartTabBadge()
    }, 100)
    // 未登录时，主动触发微信官方隐私授权弹窗（平台默认弹出，居中、文本取自后台隐私政策）
    if (typeof wx.requirePrivacyAuthorize === 'function' && !App.checkIsLogin()) {
      wx.requirePrivacyAuthorize({
        success: () => {
          console.log('隐私协议已同意')
        },
        fail: () => {
          console.log('隐私协议已拒绝')
        },
        complete: () => {}
      })
    }
  },

  /**
   * 加载页面数据
   */
  getPageData(callback) {
    let _this = this;
    App._get('page/index', {
      page_id: _this.data.options.page_id || 0
    }, result => {
      // 处理页面数据，确保样式正确
      const pageData = result.data;
      const page = pageData.page || {};

      // 获取背景色和文字颜色
      // 优先使用后端返回的颜色，如果没有则使用默认值
      const bgColor = (page.style && page.style.titleBackgroundColor) ? page.style.titleBackgroundColor : '#ffffff';
      const textColor = (page.style && page.style.titleTextColor) ? page.style.titleTextColor : 'black';

      console.log('后端返回的 page:', page);
      console.log('后端返回的 page.style:', page.style);
      console.log('使用的 bgColor:', bgColor);

      // 计算胶囊按钮背景色
      let capsuleBgColor = 'rgba(255, 255, 255, 0.9)';
      const bgColorLower = bgColor.toLowerCase();
      if (bgColorLower === '#000000' || bgColorLower === 'black' || textColor === 'white') {
        capsuleBgColor = 'rgba(0, 0, 0, 0.3)';
      }

      // 确保 page.style 存在并设置正确的值
      if (!page.style) {
        page.style = {};
      }
      page.style.titleBackgroundColor = bgColor;
      page.style.titleTextColor = textColor;

      // 构建要设置的数据对象，确保 page 对象不会被覆盖
      const iconData = pageData.icon || {};
      const dataToSet = {
        items: pageData.items,
        page: page,
        capsuleBgColor: capsuleBgColor,
        icon: {
          userIcon: iconData.userIcon || '',
          homeIcon: iconData.homeIcon || ''
        }
      };

      // 设置页面数据（包含处理后的样式）
      _this.setData(dataToSet);

      // 设置导航栏标题和颜色
      wx.setNavigationBarTitle({
        title: page.params?.title || ''
      });
      wx.setNavigationBarColor({
        frontColor: textColor === 'white' ? '#ffffff' : '#000000',
        backgroundColor: bgColor
      });

      // 回调函数
      typeof callback === 'function' && callback();
    });
  },

  /**
   * 下拉刷新
   */
  onPullDownRefresh() {
    // 获取首页数据
    this.getPageData(function () {
      wx.stopPullDownRefresh();
    });
  },

  /**
   * 分享当前页面
   */
  onShareAppMessage() {
    const _this = this;
    return {
      title: _this.data.page.params.share_title,
      path: "/pages/index/index?" + App.getShareUrlParams()
    };
  },

  /**
   * 分享到朋友圈
   * 本接口为 Beta 版本，暂只在 Android 平台支持，详见分享到朋友圈 (Beta)
   * https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/share-timeline.html
   */
  onShareTimeline() {
    const _this = this;
    return {
      title: _this.data.page.params.share_title,
      path: "/pages/index/index?" + App.getShareUrlParams()
    };
  }

});