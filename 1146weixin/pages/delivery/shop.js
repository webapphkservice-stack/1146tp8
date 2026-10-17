let App = getApp();
const delivery = require('../../api/delivery.js');

Page({

  data: {
    shopId: null,
    shop: {},
    list: [],
    loading: true
  },

  onLoad: function(options) {
    this.setData({ shopId: options.shop_id });
    this.loadGoods();
  },

  /**
   * 加载单个店铺及其外送商品（店铺为主）
   */
  loadGoods: function() {
    const _this = this;
    this.setData({ loading: true });
    delivery.goods(this.data.shopId).then(function(res) {
      const list = (res.list || []).map(function(g) {
        g.range_list = g.delivery_range ? g.delivery_range.split('、') : [];
        return g;
      });
      _this.setData({
        shop: res.shop || {},
        list: list,
        loading: false
      });
    }).catch(function() {
      _this.setData({ loading: false });
    });
  },

  /**
   * 展开 / 收起配送范围
   */
  toggleRange: function (e) {
    const index = e.currentTarget.dataset.index;
    const key = 'list[' + index + ']._expanded';
    this.setData({
      [key]: !this.data.list[index]._expanded
    });
  },

  /**
   * 立即下单：跳转外送下单页
   */
  onOrder: function(e) {
    const deliveryId = e.currentTarget.dataset.id;
    const shop = this.data.shop;
    if (shop.accept_order === 0) {
      wx.showToast({ title: '店铺暂不接单', icon: 'none' });
      return;
    }
    if (shop.delivery_enabled === 0) {
      wx.showToast({ title: '该店铺仅支持到店自提，暂不支持外送下单', icon: 'none' });
      return;
    }
    wx.navigateTo({
      url: '/pages/delivery/checkout?delivery_id=' + deliveryId
    });
  },

  /**
   * 点击 WhatsApp 号码：复制到剪贴板
   */
  onWhatsapp: function(e) {
    const wa = e.currentTarget.dataset.wa;
    if (!wa) return;
    wx.setClipboardData({
      data: wa,
      success: function() {
        wx.showToast({ title: 'WhatsApp 号码已复制：' + wa, icon: 'none' });
      }
    });
  }
});
