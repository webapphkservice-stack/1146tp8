const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLoading: true,     // 加载状态
    order_id: null,      // 订单id
    order: null,         // 订单信息
    buyer: null,         // 下单人信息
    is_self: false,      // 是否为下单人本人
    paySuccess: false,   // 本次代付是否成功
    notFound: false,     // 订单不存在
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 兼容小程序码进入场景
    if (options.scene) {
      let scene = decodeURIComponent(options.scene);
      let params = {};
      scene.split('&').forEach(item => {
        let arr = item.split('=');
        params[arr[0]] = arr[1];
      });
      options = Object.assign(params, options);
    }
    _this.setData({
      order_id: options.order_id
    });
    // 获取代付订单详情
    _this.getOrderDetail();
  },

  /**
   * 获取代付订单详情
   */
  getOrderDetail() {
    let _this = this;
    if (!_this.data.order_id) {
      _this.setData({ isLoading: false, notFound: true });
      return;
    }
    App._get('order.friendpay/detail', {
      order_id: _this.data.order_id
    }, result => {
      _this.setData(Object.assign({
        isLoading: false
      }, result.data));
    }, () => {
      _this.setData({ isLoading: false, notFound: true });
    });
  },

  /**
   * 点击帮TA支付
   */
  onPayOrder() {
    let _this = this;
    // 显示loading
    wx.showLoading({
      title: '正在处理...',
    });
    App._post_form('order.friendpay/pay', {
      order_id: _this.data.order_id
    }, result => {
      // 防御: 异常响应(如微信支付失败)可能无 data/payment, 避免 TypeError
      if (!result.data || !result.data.payment) {
        App.showError((result && result.msg) || '支付参数获取失败，请重试');
        return;
      }
      // 发起微信支付
      App.wxPayment({
        payment: result.data.payment,
        success() {
          _this.setData({ paySuccess: true });
          // 刷新订单状态
          _this.getOrderDetail();
        },
        fail() {
          App.showError('订单未支付');
        },
      });
    }, null, () => {
      wx.hideLoading();
    });
  },

  /**
   * 回到首页
   */
  onGoHome() {
    wx.switchTab({
      url: '/pages/index/index'
    });
  },

  /**
   * 转发给其他好友
   */
  onShareAppMessage() {
    let _this = this;
    return {
      title: '帮TA付一下呗~',
      path: '/pages/order/friend-pay/index?' + App.getShareUrlParams({
        order_id: _this.data.order_id
      })
    };
  },

});
