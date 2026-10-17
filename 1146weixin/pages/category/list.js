const App = getApp();
const pageIndex = 'category/list::';

Page({
  data: {
    scrollHeight: null,

    showView: false, // 列表显示方式

    sortType: 'all', // 排序类型
    sortPrice: false, // 价格从低到高

    option: {}, // 当前页面参数
    list: {}, // 商品列表数据

    leftList: [], // 瀑布流左列
    rightList: [], // 瀑布流右列
    leftHeight: 0, // 左列累计高度
    rightHeight: 0, // 右列累计高度
    hasList: false, // 是否有商品数据

    no_more: false, // 没有更多数据
    isLoading: true, // 是否正在加载中

    page: 1, // 当前页码
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(option) {
    let _this = this;
    // 设置商品列表高度
    _this.setListHeight();
    // 记录option
    _this.setData({
      option
    });
    // 设置列表显示方式
    _this.setShowView();
    // 获取商品列表
    _this.getGoodsList();
  },

  /**
   * 设置默认列表显示方式
   */
  setShowView() {
    let _this = this;
    _this.setData({
      showView: wx.getStorageSync(pageIndex + 'showView') || false
    });
  },

  /**
   * 获取商品列表
   * @param {bool} isPage 是否分页
   * @param {number} page 指定的页码
   */
  getGoodsList(isPage, page) {
    let _this = this;
    App._get('goods/lists', {
      page: page || 1,
      sortType: this.data.sortType,
      sortPrice: this.data.sortPrice ? 1 : 0,
      category_id: this.data.option.category_id || 0,
      search: this.data.option.search || '',
    }, result => {
      let resList = result.data.list,
        dataList = _this.data.list;
      if (isPage == true) {
        _this.setData({
          'list.data': dataList.data.concat(resList.data),
          isLoading: false,
          hasList: true
        });
        _this.appendWaterfall(resList.data);
      } else {
        _this.setData({
          list: resList,
          isLoading: false,
          hasList: resList.data.length > 0
        });
        _this.resetWaterfall(resList.data);
      }
    });
  },

  /**
   * 重置瀑布流（首屏 / 切换排序）
   */
  resetWaterfall(items) {
    this.setData({
      leftList: [],
      rightList: [],
      leftHeight: 0,
      rightHeight: 0
    });
    this.appendWaterfall(items || []);
  },

  /**
   * 双列瀑布流：按估算高度将商品分配到较矮的一列
   */
  appendWaterfall(items) {
    const COL_W = 365;      // 单列图片宽(rpx)
    const CARD_FIXED = 150; // 文字区固定高度(rpx)
    let left = this.data.leftList.slice();
    let right = this.data.rightList.slice();
    let lh = this.data.leftHeight;
    let rh = this.data.rightHeight;
    (items || []).forEach(item => {
      const ratio = item.__ratio || 1; // 图片宽高比，默认 1:1
      const cardH = COL_W * ratio + CARD_FIXED;
      if (lh <= rh) {
        left.push(item);
        lh += cardH;
      } else {
        right.push(item);
        rh += cardH;
      }
    });
    this.setData({
      leftList: left,
      rightList: right,
      leftHeight: lh,
      rightHeight: rh
    });
  },

  /**
   * 设置商品列表高度
   */
  setListHeight() {
    let _this = this;
    wx.getSystemInfo({
      success: res => {
        _this.setData({
          scrollHeight: res.windowHeight - 90,
        });
      }
    });
  },

  /**
   * 切换排序方式
   */
  switchSortType(e) {
    let _this = this,
      newSortType = e.currentTarget.dataset.type,
      newSortPrice = newSortType === 'price' ? !this.data.sortPrice : true;

    this.setData({
      list: {},
      isLoading: true,
      page: 1,
      sortType: newSortType,
      sortPrice: newSortPrice
    }, () => {
      // 获取商品列表
      _this.getGoodsList();
    });
  },

  /**
   * 切换列表显示方式
   */
  onChangeShowState() {
    let _this = this,
      showView = !_this.data.showView;
    wx.setStorageSync(pageIndex + 'showView', showView);
    _this.setData({
      showView
    });
  },

  /**
   * 下拉到底加载数据
   */
  bindDownLoad() {
    // 已经是最后一页
    if (this.data.page >= this.data.list.last_page) {
      this.setData({
        no_more: true
      });
      return false;
    }
    // 加载下一页列表
    this.getGoodsList(true, ++this.data.page);
  },

  /**
   * 商品搜索
   */
  triggerSearch() {
    let pages = getCurrentPages();
    // 判断来源页面
    if (pages.length > 1 &&
      pages[pages.length - 2].route === 'pages/search/index') {
      wx.navigateBack();
      return;
    }
    // 跳转到商品搜索
    wx.navigateTo({
      url: '../search/index',
    })
  },

  /**
   * 设置分享内容
   */
  onShareAppMessage() {
    // 构建分享参数
    return {
      title: "全部分类",
      path: "/pages/category/index?" + App.getShareUrlParams()
    };
  },

  /**
   * 分享到朋友圈
   * 本接口为 Beta 版本，暂只在 Android 平台支持，详见分享到朋友圈 (Beta)
   * https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/share-timeline.html
   */
  onShareTimeline() {
    // 构建分享参数
    return {
      title: "全部分类",
      path: "/pages/category/index?" + App.getShareUrlParams()
    };
  },

});