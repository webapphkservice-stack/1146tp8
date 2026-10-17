const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},     // 用户信息
    plans: [],        // 会员套餐
    vip: {},          // 会员状态
    setting: {},      // 会员设置
    isVip: false,     // 是否已开通
    vipExpireDate: '', // 到期日期
    payType: 60,      // 支付方式：10余额 20微信 60虚拟支付 70代币
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.getVipInfo();
  },

  /**
   * 获取VIP会员信息
   */
  getVipInfo: function(callback) {
    let _this = this;
    App._get('user.vip/index', {}, function(result) {
      let userInfo = result.data.userInfo || {};
      let expire = userInfo.vip_expire_time || 0;
      let isVip = !!(userInfo.is_vip && expire > Date.now() / 1000);
      _this.setData({
        userInfo: userInfo,
        plans: result.data.plans,
        vip: result.data.vip,
        setting: result.data.setting,
        isVip: isVip,
        vipExpireDate: isVip ? _this.formatDate(expire) : ''
      });
      // 回调告知调用方当前是否已开通(供支付后轮询确认使用)
      callback && callback(isVip);
    });
  },

  /**
   * 选择支付方式
   */
  selectPayType: function(e) {
    this.setData({ payType: Number(e.currentTarget.dataset.type) });
  },

  /**
   * 跳转我的VIP订单
   */
  goOrder: function() {
    wx.navigateTo({ url: './order/index' });
  },

  /**
   * 开通/续费VIP
   */
  buy: function(e) {
    let _this = this,
      planId = e.currentTarget.dataset.planId,
      payType = _this.data.payType,
      platform = (wx.getSystemInfoSync().platform === 'ios') ? 'ios' : 'android';

    App._post_form('user.vip/buy', {
      plan_id: planId,
      pay_type: payType,
      platform: platform
    }, function(result) {
      let payment = (result.data && result.data.payment) || {};
      if (payType == 60) {
        // 虚拟支付（道具直购）：调起 wx.requestVirtualPayment
        App.wxVirtualPayment({
          payment: payment,
          success: function() {
            // 会员由后端支付回调异步开通，可能晚于前端支付成功回调
            // 因此先提示"正在确认"，轮询确认开通后再提示成功，避免显示未开通
            _this.confirmVipOpen();
          },
          fail: function() {
            App.showError('支付已取消');
          }
        });
      } else if (payType == 20) {
        // 普通微信支付
        App.wxPayment({
          payment: payment,
          success: function() {
            // 同上：后端回调异步开通，轮询确认后再提示成功
            _this.confirmVipOpen();
          },
          fail: function() {
            App.showError('支付已取消');
          }
        });
      } else {
        // 余额 / 代币：后端已同步开通
        App.showSuccess('VIP开通成功');
        _this.getVipInfo();
      }
    });
  },

  /**
   * 支付成功后轮询确认会员开通
   * 后端由支付回调异步开通，可能晚于前端支付成功回调；
   * 确认开通后再提示成功，超时则提示"确认中"而不误报失败
   * @param {number} maxRetry 最大重试次数(默认15次)
   * @param {number} interval 每次间隔毫秒(默认2000)
   */
  confirmVipOpen: function(maxRetry, interval) {
    let _this = this;
    maxRetry = maxRetry || 15;
    interval = interval || 2000;
    wx.showLoading({ title: '支付成功，正在确认开通', mask: true });
    let retry = 0;
    let check = function() {
      _this.getVipInfo(function(isVip) {
        if (isVip) {
          _this.clearVipConfirmTimer();
          wx.hideLoading();
          App.showSuccess('VIP开通成功');
          return;
        }
        retry++;
        if (retry >= maxRetry) {
          _this.clearVipConfirmTimer();
          wx.hideLoading();
          // 支付已成功，仅说明后端尚未回调，不提示失败
          App.showError('支付已成功，开通确认中，请稍后下拉刷新');
          return;
        }
        _this.vipConfirmTimer = setTimeout(check, interval);
      });
    };
    check();
  },

  /**
   * 清理会员开通确认的轮询定时器
   */
  clearVipConfirmTimer: function() {
    if (this.vipConfirmTimer) {
      clearTimeout(this.vipConfirmTimer);
      this.vipConfirmTimer = null;
    }
  },

  /**
   * 生命周期函数--监听页面卸载(清理轮询，避免后台持续请求)
   */
  onUnload: function() {
    this.clearVipConfirmTimer();
  },

  /**
   * 时间戳格式化
   */
  formatDate: function(timestamp) {
    let date = new Date(timestamp * 1000);
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let day = date.getDate().toString().padStart(2, '0');
    return year + '-' + month + '-' + day;
  }

});
