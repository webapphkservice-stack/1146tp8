let App = getApp();
const delivery = require('../../api/delivery.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    isMalaysia: false,
    list: [],
    loading: false,
    notice: '',
    settingNotice: '',
    // 我的外送订单
    orderList: [],
    orderLoading: false,
    orderStatus: -1,
    orderStatusTabs: [
      { key: -1, name: '全部' },
      { key: 0, name: '待接单' },
      { key: 1, name: '配送中' },
      { key: 2, name: '已完成' },
      { key: 3, name: '已取消' }
    ]
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.init();
  },

  /**
   * 初始化：校验登录与国家
   */
  init: function() {
    const isLogin = App.checkIsLogin();
    this.setData({
      isLogin: isLogin
    });
    if (!isLogin) {
      this.setData({
        list: [],
        isMalaysia: false
      });
      return;
    }
    const _this = this;
    _this.loadList();
    delivery.checkCountry().then(function(res) {
      _this.setData({
        isMalaysia: res.is_malaysia
      });
      if (res.is_malaysia) {
        _this.loadList();
        _this.loadOrderList();
      } else {
        _this.setData({
          notice: '当前收货地址国家不是马来西亚，暂不可使用本地外送。请添加马来西亚收货地址后重试。',
          list: []
        });
      }
    }).catch(function() {
      _this.setData({
        isMalaysia: false,
        notice: '获取地址信息失败，请稍后重试。',
        list: []
      });
    });
  },

  /**
   * 加载店铺列表（店铺为主，外送商品内嵌在各店铺下）
   */
  loadList: function() {
    const _this = this;
    this.setData({
      loading: true
    });
    delivery.shop().then(function(res) {
      // 每个店铺取前 4 件外送商品作为首页预览
      const list = (res.list || []).map(function(shop) {
        shop.goods_preview = (shop.goods || []).slice(0, 4);
        return shop;
      });
      _this.setData({
        list: list,
        settingNotice: res.notice || '',
        loading: false
      });
    }).catch(function() {
      _this.setData({
        loading: false
      });
    });
  },

  /**
   * 加载我的外送订单
   */
  loadOrderList: function() {
    if (!this.data.isLogin) {
      this.setData({ orderList: [] });
      return;
    }
    const _this = this;
    this.setData({ orderLoading: true });
    delivery.orderList(this.data.orderStatus).then(function(res) {
      _this.setData({
        orderList: res.list || [],
        orderLoading: false
      });
    }).catch(function() {
      _this.setData({
        orderList: [],
        orderLoading: false
      });
    });
  },

  /**
   * 切换订单状态 tab
   */
  onOrderTab: function(e) {
    const status = parseInt(e.currentTarget.dataset.status, 10);
    this.setData({ orderStatus: status }, this.loadOrderList);
  },

  /**
   * 去设置收货地址
   */
  goAddress: function() {
    wx.navigateTo({
      url: '/pages/address/index'
    });
  },

  /**
   * 去登录
   */
  goLogin: function() {
    wx.navigateTo({
      url: '/pages/login/login'
    });
  },

  /**
   * 拨打电话 / 选择外送
   */
  onCall: function(e) {
    const phone = e.currentTarget.dataset.phone;
    if (phone) {
      wx.makePhoneCall({
        phoneNumber: phone
      });
    }
  },

  /**
   * 立即下单：跳转到外送下单页
   */
  onOrder: function(e) {
    const ds = e.currentTarget.dataset;
    if (ds.accept === 0) {
      wx.showToast({ title: '店铺暂不接单', icon: 'none' });
      return;
    }
    if (ds.delivery === 0) {
      wx.showToast({ title: '该店铺仅支持到店自提，暂不支持外送下单', icon: 'none' });
      return;
    }
    wx.navigateTo({
      url: '/pages/delivery/checkout?delivery_id=' + ds.id
    });
  },

  /**
   * 进入店铺详情（店铺为主）
   */
  onShopTap: function(e) {
    const shopId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/delivery/shop?shop_id=' + shopId
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
  },

  /**
   * 确认收货
   */
  onConfirmReceipt: function(e) {
    const orderId = e.currentTarget.dataset.id;
    const _this = this;
    wx.showModal({
      title: '提示',
      content: '确认已收到该外送订单？',
      success: function(sm) {
        if (!sm.confirm) {
          return;
        }
        wx.showLoading({ title: '处理中...', mask: true });
        delivery.confirm(orderId).then(function() {
          wx.hideLoading();
          wx.showToast({ title: '已确认收货', icon: 'success' });
          _this.loadOrderList();
        }).catch(function() {
          wx.hideLoading();
          wx.showToast({ title: '操作失败，请重试', icon: 'none' });
        });
      }
    });
  },

  /**
   * 进入出租车预约页
   */
  goTaxi: function() {
    wx.navigateTo({
      url: '/pages/taxi/index'
    });
  },

})
