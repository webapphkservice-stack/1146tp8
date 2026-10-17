const App = getApp();

Page({
  data: {
    // 搜索框样式
    searchColor: "rgba(0,0,0,0.4)",
    searchSize: "15",
    searchName: "搜索商品",

    // 列表高度
    scrollHeight: 0,

    // 一级分类：指针
    curNav: true,
    curIndex: 0,

    // 分类列表
    list: [],

    // show
    notcont: false,

    // 状态栏高度
    statusBarHeight: 0,

    // 标题栏配置
    title: '全部分类',
    titleBackgroundColor: '#ffffff',
    titleTextColor: 'black',
    capsuleBgColor: '',

    // 图标数据
    icon: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad() {
    let _this = this;
    // 获取系统信息
    const systemInfo = wx.getSystemInfoSync();
    _this.setData({
      statusBarHeight: systemInfo.statusBarHeight
    });
    // 设置分类列表高度
    _this.setListHeight();
    // 获取分类列表
    _this.getCategoryList();
    // 获取页面配置和图标
    _this.getPageConfig();
  },

  /**
   * 获取页面配置和图标
   */
  getPageConfig() {
    let _this = this;
    App._get('page/index', {}, result => {
      const pageData = result.data;
      const page = pageData.page || {};

      // 获取背景色和文字颜色
      const bgColor = (page.style && page.style.titleBackgroundColor) ? page.style.titleBackgroundColor : '#ffffff';
      const textColor = (page.style && page.style.titleTextColor) ? page.style.titleTextColor : 'black';

      // 计算胶囊按钮背景色
      let capsuleBgColor = 'rgba(255, 255, 255, 0.9)';
      const bgColorLower = bgColor.toLowerCase();
      if (bgColorLower === '#000000' || bgColorLower === 'black' || textColor === 'white') {
        capsuleBgColor = 'rgba(0, 0, 0, 0.3)';
      }

      const iconData = pageData.icon || {};
      _this.setData({
        title: page.params && page.params.title ? page.params.title : '全部分类',
        titleBackgroundColor: bgColor,
        titleTextColor: textColor,
        capsuleBgColor: capsuleBgColor,
        icon: {
          userIcon: iconData.userIcon || '',
          homeIcon: iconData.homeIcon || ''
        }
      });
    });
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    // 更新购物车角标
    App.setCartTabBadge()
  },

  /**
   * 设置分类列表高度
   */
  setListHeight() {
    let _this = this;
    wx.getSystemInfo({
      success: function(res) {
        _this.setData({
          scrollHeight: res.windowHeight - 47,
        });
      }
    });
  },

  /**
   * 获取分类列表
   */
  getCategoryList() {
    let _this = this;
    App._get('category/index', {}, result => {
      let data = result.data;
      _this.setData({
        list: data.list,
        templet: data.templet,
        curNav: data.list.length > 0 ? data.list[0].category_id : true,
        notcont: !data.list.length
      });
    });
  },

  /**
   * 一级分类：选中分类
   */
  selectNav(e) {
    let _this = this;
    _this.setData({
      curNav: e.target.dataset.id,
      curIndex: parseInt(e.target.dataset.index),
      scrollTop: 0
    });
  },

  /**
   * 设置分享内容
   */
  onShareAppMessage() {
    const _this = this;
    return {
      title: _this.data.templet.share_title,
      path: '/pages/category/index?' + App.getShareUrlParams()
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
      title: _this.data.templet.share_title,
      path: '/pages/category/index?' + App.getShareUrlParams()
    };
  },

});