const taxi = require('../../api/taxi.js');

Page({
  data: {
    list: [],
    loading: true
  },

  onShow: function () {
    this.getList();
  },

  getList: function () {
    const that = this;
    that.setData({ loading: true });
    taxi.orders().then(function (list) {
      that.setData({ list: list, loading: false });
    }).catch(function () {
      that.setData({ loading: false });
    });
  },

  goCreate: function () {
    wx.navigateTo({ url: '/pages/taxi/index' });
  }
});
