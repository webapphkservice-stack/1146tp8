const app = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    // 分类列表
    categoryList: [],
    // 选中的分类id
    selectCategoryId: 0,
    // 文章列表（原始分页对象）
    articleList: [],
    // 双排瀑布流（左右两列）
    leftList: [],
    rightList: [],
    // 两列累计高度（rpx），用于按图片真实高度分配
    _leftH: 0,
    _rightH: 0,
    isLoading: true,
    page: 1,
    no_more: false,
    refresh: false
  },

  /**
   * 卡片封面列宽（rpx）：(750 - 容器左右padding16*2 - 列间距16) / 2
   */
  COL_W: 351,
  /**
   * 卡片文字区域估算高度（rpx）：标题+摘要+底部
   */
  CARD_FIXED: 160,

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    this.setData({ selectCategoryId: options.category_id ? parseInt(options.category_id) : 0 });
    this.getCategoryList(() => {
      _this.getArticleList();
    });
  },

  /**
   * 获取文章分类列表
   */
  getCategoryList(callback) {
    let _this = this;
    app._get('article/index', {}, result => {
      _this.setData({ categoryList: result.data.categoryList });
      callback && callback();
    });
  },

  /**
   * 获取文章列表
   */
  getArticleList(isPage, page) {
    let _this = this;
    app._get('article/lists', {
      page: page || 1,
      category_id: _this.data.selectCategoryId
    }, result => {
      let resList = result.data.list;
      let dataList = resList.data || [];
      // 按图片真实高度分配到较矮的一列（瀑布流竖/横图错落）
      if (!isPage) {
        _this.data._leftH = 0;
        _this.data._rightH = 0;
      }
      _this.layoutWaterfall(dataList, !!isPage).then(() => {
        _this.setData({
          articleList: resList,
          isLoading: false,
          no_more: resList.last_page <= (page || 1)
        });
      });
    }, null, () => {
      wx.stopPullDownRefresh();
    });
  },

  /**
   * 计算封面图地址：多图 images 首图优先，单图 image 兜底
   */
  _coverOf(item) {
    let imgs = item.images || [];
    if (imgs.length > 0 && imgs[0].file_path) return imgs[0].file_path;
    let img = item.image || {};
    if (img.file_path) return img.file_path;
    return '';
  },

  /**
   * 获取封面图宽高比（优先后台返回，否则实时获取）
   */
  _getImgRatio(item) {
    return new Promise(resolve => {
      let imgs = item.images || [];
      let img = imgs.length > 0 ? imgs[0] : (item.image || {});
      if (img.width && img.height && img.width > 0) {
        resolve(img.height / img.width);
      } else if (img.file_path) {
        wx.getImageInfo({
          src: img.file_path,
          success: r => resolve(r.height / r.width),
          fail: () => resolve(0.75)
        });
      } else {
        resolve(0.75);
      }
    });
  },

  /**
   * 按图片高度做瀑布流分配
   */
  layoutWaterfall(list, isPage) {
    let _this = this;
    // 预计算封面地址，保证 wxml 统一用 item.cover
    list.forEach(item => { item.cover = _this._coverOf(item); });
    return Promise.all(list.map(item => _this._getImgRatio(item))).then(ratios => {
      let left = isPage ? _this.data.leftList.slice() : [];
      let right = isPage ? _this.data.rightList.slice() : [];
      let lh = isPage ? _this.data._leftH : 0;
      let rh = isPage ? _this.data._rightH : 0;
      ratios.forEach((ratio, i) => {
        let cardH = _this.COL_W * ratio + _this.CARD_FIXED;
        if (lh <= rh) {
          left.push(list[i]);
          lh += cardH;
        } else {
          right.push(list[i]);
          rh += cardH;
        }
      });
      _this.data._leftH = lh;
      _this.data._rightH = rh;
      _this.setData({ leftList: left, rightList: right });
    });
  },

  /**
   * 切换分类
   */
  onSwitchTab(e) {
    let _this = this;
    let categoryId = e.currentTarget.dataset.id;
    if (categoryId == _this.data.selectCategoryId) return;
    this.setData({
      selectCategoryId: categoryId,
      leftList: [],
      rightList: [],
      page: 1
    });
    this.getArticleList();
  },

  /**
   * 下拉刷新
   */
  onPullDownRefresh() {
    this.setData({ leftList: [], rightList: [], page: 1 });
    this.getArticleList();
  },

  /**
   * 上拉加载更多
   */
  onReachBottom() {
    let _this = this;
    if (_this.data.no_more) return false;
    _this.getArticleList(true, ++_this.data.page);
  },

  /**
   * 分享
   */
  onShareAppMessage() {
    return {
      title: '种草精选，发现好物',
      path: '/pages/article/index'
    };
  },

  goTop() {
    wx.pageScrollTo({ scrollTop: 0 });
  }
});
