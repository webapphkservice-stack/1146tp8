const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    balance: 0, // 当前可用水票数
    list: [], // 水票变动流水
    isLoading: true, // 是否正在加载中
    page: 1, // 当前页码
    scrollHeight: 0, // 列表容器高度
    no_more: false, // 没有更多数据
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 设置列表容器高度
    _this.setListHeight();
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    let _this = this;
    // 获取水票余额与流水
    _this.getWaterTicket();
  },

  /**
   * 获取水票余额与流水
   */
  getWaterTicket(isPage, page) {
    let _this = this;
    App._get('user.water_ticket/lists', {
      page: page || 1
    }, result => {
      let resList = result.data.list,
        dataList = _this.data.list;
      if (isPage == true) {
        _this.setData({
          'list.data': dataList.data.concat(resList.data),
          balance: result.data.balance,
          isLoading: false,
        });
      } else {
        _this.setData({
          list: resList,
          balance: result.data.balance,
          isLoading: false,
        });
      }
    });
  },

  /**
   * 设置列表容器高度
   */
  setListHeight() {
    let _this = this,
      systemInfo = wx.getSystemInfoSync();
    _this.setData({
      scrollHeight: systemInfo.windowHeight * 0.98
    });
  },

  /**
   * 下拉到底加载数据
   */
  onPageDown() {
    let _this = this;
    // 已经是最后一页
    if (_this.data.page >= _this.data.list.last_page) {
      _this.setData({
        no_more: true
      });
      return false;
    }
    // 加载下一页列表
    _this.getWaterTicket(true, ++_this.data.page);
  },

})
