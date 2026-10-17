let App = getApp();

Page({
  data: {
    list: [],
    default_id: null,
  },

  onLoad: function(options) {
    // 当前页面参数
    this.data.options = options;
    this.setData({ from: (options && options.from) || '' });
  },

  onShow: function() {
    // 获取收货地址列表
    this.getAddressList();
  },

  /**
   * 获取收货地址列表
   */
  getAddressList: function() {
    let _this = this;
    App._get('address/lists', {}, function(result) {
      _this.setData(result.data);
    });
  },

  /**
   * 从外送/结算页进入时，点击地址即选中使用并返回
   */
  selectAddress: function(e) {
    const from = this.data.from;
    if (from === 'delivery' || from === 'flow') {
      const id = e.currentTarget.dataset.id;
      if (id) wx.setStorageSync('deliveryAddressId', id);
      wx.navigateBack();
    }
  },

  /**
   * 添加新地址
   */
  createAddress: function() {
    wx.navigateTo({
      url: './create?from=' + this.data.from
    });
  },

  /**
   * 编辑地址
   */
  editAddress: function(e) {
    wx.navigateTo({
      url: "./detail?address_id=" + e.currentTarget.dataset.id
    });
  },

  /**
   * 移除收货地址
   */
  removeAddress: function(e) {
    let _this = this,
      address_id = e.currentTarget.dataset.id;
    wx.showModal({
      title: "提示",
      content: "您确定要移除当前收货地址吗?",
      success: function(o) {
        o.confirm && App._post_form('address/delete', {
          address_id
        }, function(result) {
          _this.getAddressList();
        });
      }
    });
  },

  /**
   * 设置为默认地址
   */
  setDefault: function(e) {
    let _this = this,
      address_id = e.detail.value;
    _this.setData({
      default_id: parseInt(address_id)
    });
    App._post_form('address/setDefault', {
      address_id
    }, function(result) {
      const from = _this.data.from;
      // 从外送/结算页进入时，设默认即视为选中，直接返回来源页
      if (from === 'flow' || from === 'delivery') {
        wx.setStorageSync('deliveryAddressId', address_id);
        wx.navigateBack();
      }
    });
    return false;
  },

});