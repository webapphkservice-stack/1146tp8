const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    userInfo: {}, // 用户信息
    orderCount: {}, // 订单数量
    signin: {}, // 签到数据
    today: '', // 今天日期
    setting: {}, // 系统设置
    userIcon: '', // 后台设置的默认头像（图标管理）
    waterTicketNum: 0, // 我的水票数量(送水)
    balanceText: '0.00', // 账户余额(统一两位小数)
    coinText: '0.00', // 我的代币(统一两位小数)
    agentInfo: null, // 区域代理信息(非代理商为 null 或 is_agency=0)
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    let _this = this;
    _this.setData({
      isLogin: App.checkIsLogin(),
      today: this.getTodayDate()
    });
    // 获取后台设置的默认头像（图标管理）
    App._get('page/index', {}, function (result) {
      const iconData = (result.data && result.data.icon) || {};
      _this.setData({ userIcon: iconData.userIcon || '' });
    });
    // 获取当前用户信息
    _this.getUserDetail();
    // 获取区域代理信息(卡片展示)
    _this.getAgentInfo();
    // 获取水票余额(送水)
    _this.getWaterTicketNum();
    // 获取签到数据
    _this.getSigninData();
    // 更新购物车角标
    App.setCartTabBadge();
  },

  /**
   * 获取今天日期
   */
  getTodayDate() {
    let date = new Date();
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
  },

  /**
   * 获取签到数据
   */
  getSigninData() {
    let _this = this;
    App._get('signin/index', {}, function (result) {
      _this.setData({
        signin: result.data
      });
    });
  },

  /**
   * 执行签到
   */
  onSignin() {
    let _this = this;
    if (_this.data.signin.is_signin_today) {
      App.showError('您今天已经签到过了');
      return;
    }
    App._post_form('signin/submit', {}, function (result) {
      App.showSuccess(result.msg);
      // 刷新签到数据
      _this.getSigninData();
      // 刷新用户信息（积分）
      _this.getUserDetail();
    });
  },

  /**
   * 获取当前用户信息
   */
  getUserDetail() {
    let _this = this;
    App._get('user.index/detail', {}, function (result) {
      console.log('API返回数据:', result.data);
      console.log('setting数据:', result.data.setting);
      let userInfo = result.data.userInfo || {};
      let expire = userInfo.vip_expire_time || 0;
      let isVip = !!(userInfo.is_vip && expire > Date.now() / 1000);
      result.data.isVip = isVip;
      result.data.vipExpireDate = isVip ? _this.formatDate(expire) : '';
      // 余额/代币统一格式化为两位小数，避免显示缺少 .00
      result.data.balanceText = (Number(userInfo.balance) || 0).toFixed(2);
      result.data.coinText = (Number(userInfo.coin) || 0).toFixed(2);
      _this.setData(result.data);
      // 第三方登录且未绑定手机号: 引导绑定以关联微信/支付宝账号
      _this.checkBindMobile(userInfo);
    });
  },

  /**
   * 检查是否需要引导绑定手机号
   * 绑定后可将微信与支付宝账号关联为同一账号(数据互通)
   */
  checkBindMobile(userInfo) {
    if (!userInfo || userInfo.need_bind_mobile != 1) {
      return;
    }
    // 免打扰: 用户选择“暂不绑定”后 24 小时内不再提示
    const lastTime = wx.getStorageSync('bindMobileTipTime') || 0;
    if (lastTime && Date.now() - lastTime < 24 * 3600 * 1000) {
      return;
    }
    wx.showModal({
      title: '绑定手机号',
      content: '绑定手机号后，可关联您的微信与支付宝账号，订单、余额、积分等数据互通。',
      confirmText: '立即绑定',
      cancelText: '暂不绑定',
      success(res) {
        if (res.confirm) {
          wx.navigateTo({ url: '/pages/user/eiitUserInfo' });
        } else {
          wx.setStorageSync('bindMobileTipTime', Date.now());
        }
      }
    });
  },

  /**
   * 格式化日期
   */
  formatDate(timestamp) {
    let date = new Date(timestamp * 1000);
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let day = date.getDate().toString().padStart(2, '0');
    return year + '-' + month + '-' + day;
  },

  /**
   * 订单导航跳转
   */
  onTargetOrder(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    let urls = {
      all: '/pages/order/index?type=all',
      payment: '/pages/order/index?type=payment',
      received: '/pages/order/index?type=received',
      refund: '/pages/order/refund/index',
    };
    // 转跳指定的页面
    wx.navigateTo({
      url: urls[e.currentTarget.dataset.type]
    })
  },

  /**
   * 跳转VIP会员页
   */
  onTargetVip(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: './vip/index'
    })
  },

  /**
   * 菜单列表导航跳转
   */
  onTargetMenus(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: '/' + e.currentTarget.dataset.url
    })
  },

  /**
   * 跳转我的钱包页面
   */
  onTargetWallet(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: './wallet/index'
    })
  },

  /**
   * 跳转积分明细页
   */
  onTargetPoints(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: '../points/log/index'
    });
  },

  /**
   * 获取我的水票余额(送水)
   */
  getWaterTicketNum() {
    let _this = this;
    if (!App.checkIsLogin()) {
      return false;
    }
    App._get('user.water_ticket/lists', {
      page: 1
    }, function (result) {
      _this.setData({
        waterTicketNum: (result.data && result.data.balance) || 0
      });
    }, function () {});
  },

  /**
   * 跳转我的水票页（送水：水票余额与明细）
   */
  onTargetWaterTicket(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: './water_ticket/index'
    });
  },

  /**
   * 跳转代币充值页（默认选中代币充值）
   */
  onTargetCoin(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: './recharge/index?mode=coin'
    });
  },

  /**
   * 跳转到登录页
   */
  onLogin() {
    // wx.navigateTo({
    //   url: '../login/login',
    // });
    App.doLogin();
  },

  /**
   * 跳转到个人信息编辑页面
   */
  onEditUserInfo() {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: '/pages/user/eiitUserInfo'
    });
  },

  /**
   * 编辑用户信息（别名）
   */
  toUserInfo() {
    this.onEditUserInfo();
  },

  /**
   * 验证是否已登录
   */
  onCheckLogin() {
    let _this = this;
    if (!_this.data.isLogin) {
      App.showError('很抱歉，您还没有登录');
      return false;
    }
    return true;
  },

  /**
   * 获取区域代理信息(我的页卡片)
   */
  getAgentInfo() {
    let _this = this;
    if (!App.checkIsLogin()) {
      return false;
    }
    App._get('regionagent/getAgentInfo', {}, function (result) {
      let data = result.data || {};
      let agent = data.agent;
      if (agent) {
        // 金额统一格式化为两位小数
        agent.current_moneyText = (Number(agent.current_money) || 0).toFixed(2);
        agent.withdrawnText = (Number(agent.withdrawn) || 0).toFixed(2);
        agent.withdrawableText = (Number(agent.withdrawable) || 0).toFixed(2);
      }
      _this.setData({ agentInfo: data });
    }, function () {});
  },

  /**
   * 跳转区域代理中心
   */
  onTargetRegionAgent(e) {
    let _this = this;
    if (!_this.onCheckLogin()) {
      return false;
    }
    wx.navigateTo({
      url: '/pages/regionagent/index'
    });
  },

})