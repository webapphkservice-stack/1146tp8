let App = getApp();
const delivery = require('../../api/delivery.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    deliveryId: 0,
    detail: null,
    total: '0.00',
    addressList: [],
    addressId: 0,
    selectedAddress: null, // 选中的收货地址（单个卡片展示，而非全部列表）
    remark: '',
    rangeExpanded: false,
    loading: true,
    submitting: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    const deliveryId = parseInt(options.delivery_id, 10) || 0;
    this.setData({ deliveryId: deliveryId });
    this.loadDetail(deliveryId);
  },

  /**
   * 每次显示都重新加载地址，确保从地址页返回后刷新选中项
   */
  onShow: function() {
    this.loadAddress();
  },

  /**
   * 加载外送服务详情
   */
  loadDetail: function(deliveryId) {
    const _this = this;
    App._get('delivery/detail', { delivery_id: deliveryId }, function(res) {
      // 注意：App._get 回调的参数是完整响应对象，真实数据在 res.data 里
      const detail = res.data.detail || {};
      // 配送范围：字符串按 、分隔为数组，供下单页展示
      detail.range_list = detail.delivery_range ? detail.delivery_range.split('、') : [];
      // 金额：商品费 + 配送费（与后端一致），保留两位小数
      const total = (parseFloat(detail.price || 0) + parseFloat(detail.delivery_fee || 0)).toFixed(2);
      _this.setData({
        detail: detail,
        total: total,
        loading: false
      });
    });
  },

  // 加载收货地址列表：优先使用用户在地址页选中的地址（持久化存储），其次默认地址
  loadAddress: function() {
    const _this = this;
    App._get('address/lists', {}, function(res) {
      const list = res.data.list || [];
      // 注意：地址列表接口返回的是 default_id，每个 item 没有 is_default 字段
      let chosen = null;
      const pickedId = wx.getStorageSync('deliveryAddressId') || 0;
      if (pickedId) {
        list.forEach(function(a) {
          if (a.address_id == pickedId) chosen = a;
        });
      }
      if (!chosen) {
        const defaultId = res.data.default_id || 0;
        list.forEach(function(a) {
          if (a.address_id == defaultId) chosen = a;
        });
      }
      _this.setData({
        addressList: list,
        addressId: chosen ? chosen.address_id : 0,
        selectedAddress: chosen ? _this.formatAddress(chosen) : null
      });
    });
  },

  // 把地址对象格式化为下单页展示用（拼接完整地址文本）
  formatAddress: function(a) {
    let region = '';
    if (a.region && a.region.province) {
      region = [a.region.province, a.region.city, a.region.region].filter(Boolean).join(' ');
    } else {
      region = [a.state, a.city].filter(Boolean).join(' ');
    }
    return {
      address_id: a.address_id,
      name: a.name,
      phone: a.phone,
      fullAddress: (region + ' ' + (a.detail || '')).trim()
    };
  },

  // 展开 / 收起配送范围
  toggleRange: function () {
    this.setData({ rangeExpanded: !this.data.rangeExpanded });
  },

  // 去选择/添加收货地址（带 from=delivery 以便返回）
  goAddress: function() {
    wx.navigateTo({ url: '/pages/address/index?from=delivery' });
  },

  onRemarkInput: function(e) {
    this.setData({ remark: e.detail.value });
  },

  // 地址列表中的单选（保留兼容，主要走地址页选回）
  onSelectAddress: function(e) {
    const id = e.currentTarget.dataset.id;
    this.setData({ addressId: id });
  },

  /**
   * 提交下单
   */
  onSubmit: function() {
    const _this = this;
    if (_this.data.submitting) return;
    if (!_this.data.addressId) {
      App.showError('请先选择收货地址');
      return;
    }
    _this.setData({ submitting: true });
    wx.showLoading({ title: '提交中...', mask: true });
    delivery.create({
      delivery_id: _this.data.deliveryId,
      address_id: _this.data.addressId,
      remark: _this.data.remark
    }).then(function() {
      wx.hideLoading();
      _this.setData({ submitting: false });
      wx.showToast({ title: '下单成功', icon: 'success' });
      setTimeout(function() {
        wx.redirectTo({ url: '/pages/delivery/index' });
      }, 1200);
    }).catch(function() {
      wx.hideLoading();
      _this.setData({ submitting: false });
    });
  }

});
