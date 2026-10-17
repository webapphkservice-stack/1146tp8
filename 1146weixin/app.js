/**
 * tabBar页面路径列表 (用于链接跳转时判断)
 * tabBarLinks为常量, 无需修改
 */
const tabBarLinks = [
  'pages/index/index',
  'pages/category/index',
  'pages/flow/index',
  'pages/user/index'
];

// 站点配置文件
import siteinfo from './siteinfo.js';

// 工具类
import util from './utils/util.js';

App({

  /**
   * 全局变量
   */
  globalData: {
    user_id: null,
    // 微信确认收货组件(weappOrderConfirm)待确认订单号，组件回调成功后用于同步状态
    pendingReceiptOrderId: null,
  },

  // api地址
  api_root: siteinfo.siteroot + 'index.php?s=/api/',

  /**
   * 生命周期函数--监听小程序初始化
   */
  onLaunch(e) {
    let _this = this;
    // 小程序主动更新
    _this.updateManager();
    // 小程序启动场景
    _this.onStartupScene(e.query);
  },

  /**
   * 小程序启动场景
   */
  onStartupScene(query) {
    // 获取场景值
    let scene = this.getSceneData(query);
    // 记录推荐人id
    let refereeId = query.referee_id ? query.referee_id : scene.uid;
    refereeId > 0 && (this.saveRefereeId(refereeId));
  },

  /**
   * 获取商城ID
   */
  getWxappId() {
    return siteinfo.uniacid || 10001;
  },

  /**
   * 记录推荐人id
   */
  saveRefereeId(refereeId) {
    let App = this;
    refereeId = parseInt(refereeId);
    if (refereeId <= 0 || refereeId == App.getUserId()) {
      return false;
    }
    if (wx.getStorageSync('referee_id')) {
      return false;
    }
    wx.setStorageSync('referee_id', refereeId);
    return true;
  },

  /**
   * 获取场景值(scene)
   */
  getSceneData(query) {
    return query.scene ? util.scene_decode(query.scene) : {};
  },

  /**
   * 当小程序启动，或从后台进入前台显示，会触发 onShow
   */
  onShow(options) {
    let App = this;
    // 微信「确认收货组件」(weappOrderConfirm) 回传结果
    // 组件通过 navigateBackMiniProgram 返回，结果置于 options.referrerInfo，必须由 App.onShow 处理
    if (options && options.referrerInfo && options.referrerInfo.appId === 'wx1183b055aeec94d1') {
      const extra = options.referrerInfo.extraData || {};
      const order_id = App.globalData.pendingReceiptOrderId;
      App.globalData.pendingReceiptOrderId = null;
      if (extra.status === 'success' && order_id) {
        const pages = getCurrentPages();
        const cur = pages[pages.length - 1];
        // 仍在订单详情页则复用页面同步逻辑并刷新；否则仅后台同步状态
        if (cur && cur.route === 'pages/order/detail' && cur._syncReceipt) {
          cur._syncReceipt(order_id);
          wx.showToast({ title: '已确认收货', icon: 'success' });
        } else {
          App._post_form('user.order/receipt', { order_id }, () => {}, null, null, false);
        }
      }
    }
    try {
      const livePlayer = requirePlugin('live-player-plugin');
      if (options.scene == 1007 || options.scene == 1008 || options.scene == 1044) {
        livePlayer.getShareParams()
          .then(res => {
            // 直播页面的自定义参数
            let customParams = res.custom_params;
            console.log('get custom params', customParams);
            // 记录推荐人ID
            if (customParams.hasOwnProperty('referee_id')) {
              App.saveRefereeId(customParams['referee_id']);
            }
          }).catch(err => {
            console.log('get share params', err)
          });
      }
    } catch (error) {

    }
  },

  /**
   * 执行用户登录
   */
  doLogin(delta) {
    // 保存当前页面
    let pages = getCurrentPages();
    if (pages.length) {
      let currentPage = pages[pages.length - 1];
      if ("pages/login/login" != currentPage.route) {
        wx.setStorageSync("currentPage", {
          route: currentPage.route,
          options: currentPage.options || {}
        });
      }
    }
    // 跳转授权页面
    wx.navigateTo({
      url: "/pages/login/login?delta=" + (delta || 1)
    });
  },

  /**
   * 当前用户id
   */
  getUserId() {
    return wx.getStorageSync('user_id');
  },

  /**
   * 显示成功提示框
   */
  showSuccess(msg, callback) {
    wx.showToast({
      title: msg,
      icon: 'success',
      mask: true,
      duration: 1500
    });
    // 回调不依赖 toast 的 success 事件（可能被其他 loading 调用打断导致不触发），
    // 独立定时执行，确保跳转等后续动作一定发生
    callback && setTimeout(() => {
      callback();
    }, 1500);
  },

  /**
   * 显示失败提示框
   */
  showError(msg, callback) {
    wx.showModal({
      title: '友情提示',
      content: msg,
      showCancel: false,
      success(res) {
        // callback && (setTimeout(() => {
        //   callback();
        // }, 1500));
        callback && callback();
      }
    });
  },

  /**
   * get请求
   */
  _get(url, data, success, fail, complete, check_login) {
    wx.showNavigationBarLoading();
    let _this = this;
    // 构造请求参数
    data = data || {};
    data.wxapp_id = _this.getWxappId();

    // if (typeof check_login === 'undefined')
    //   check_login = true;

    // 构造get请求
    let request = () => {
      data.token = wx.getStorageSync('token');
      wx.request({
        url: _this.api_root + url,
        header: {
          'content-type': 'application/json'
        },
        data: data,
        success(res) {
          if (res.statusCode !== 200 || typeof res.data !== 'object') {
            console.log(res);
            _this.showError('网络请求出错');
            return false;
          }
          if (res.data.code === -1) {
            // 登录态失效, 重新登录
            wx.hideNavigationBarLoading();
            _this.doLogin(2);
          } else if (res.data.code === 0) {
            _this.showError(res.data.msg, () => {
              fail && fail(res);
            });
            return false;
          } else {
            success && success(res.data);
          }
        },
        fail(res) {
          _this.showError(res.errMsg, () => {
            fail && fail(res);
          });
        },
        complete(res) {
          wx.hideNavigationBarLoading();
          complete && complete(res);
        },
      });
    };
    // 未登录且接口需要登录: 跳过无效的服务器请求, 直接跳转登录页
    // 避免“先发请求等服务器返回 -1 再跳转登录页”导致的授权按钮迟迟不出现
    if (!_this.checkIsLogin() && _this.isGatedApi(url)) {
      wx.hideNavigationBarLoading();
      _this.doLogin();
      return;
    }
    // 判断是否需要验证登录
    check_login ? _this.doLogin(request) : request();
  },

  /**
   * post提交
   */
  _post_form(url, data, success, fail, complete, isShowNavBarLoading) {
    let _this = this;

    // 默认显示导航条加载动画：未传参视为 true；调用方显式传 false 才不显示
    isShowNavBarLoading = isShowNavBarLoading !== false;
    data.wxapp_id = _this.getWxappId();
    data.token = wx.getStorageSync('token');

    // 在当前页面显示导航条加载动画
    if (isShowNavBarLoading == true) {
      wx.showNavigationBarLoading();
    }
    // 未登录且接口需要登录: 跳过无效的服务器请求, 直接跳转登录页
    if (!_this.checkIsLogin() && _this.isGatedApi(url)) {
      wx.hideNavigationBarLoading();
      _this.doLogin();
      return;
    }
    wx.request({
      url: _this.api_root + url,
      header: {
        'content-type': 'application/x-www-form-urlencoded',
      },
      method: 'POST',
      data: data,
      success(res) {
        if (res.statusCode !== 200 || typeof res.data !== 'object') {
          _this.showError('网络请求出错');
          return false;
        }
        if (res.data.code === -1) {
          // 登录态失效, 重新登录
          wx.hideNavigationBarLoading();
          _this.doLogin(1);
          return false;
        } else if (res.data.code !== 1) {
          // 非成功状态码(含 0 及业务错误码如 -10 微信支付失败)均视为失败
          _this.showError(res.data.msg, () => {
            fail && fail(res);
          });
          return false;
        }
        success && success(res.data);
      },
      fail(res) {
        // console.log(res);
        _this.showError(res.errMsg, () => {
          fail && fail(res);
        });
      },
      complete(res) {
        wx.hideNavigationBarLoading();
        // wx.hideLoading();
        complete && complete(res);
      }
    });
  },

  /**
   * 判断接口是否需要登录(基于接口模块前缀)
   * 仅对需要登录的接口做本地token预判, 避免误拦截游客可见的公共接口(首页/商品等)
   */
  isGatedApi(url) {
    url = (url || '').replace(/^\//, '');
    // 免登录接口(登录/注册判断等), 不拦截, 否则会导致登录死循环
    const noLogin = [
      'user/login', 'user/isRegister',
      'user/sendCode',           // 手机号短信验证码(未登录也可获取)
      'user/whatsappSendCode',   // WhatsApp 验证码(未登录也可获取)
      'user/whatsappLogin'       // WhatsApp 登录/注册(本就用于登录)
    ];
    if (noLogin.some(prefix => url.indexOf(prefix) === 0)) {
      return false;
    }
    const gated = [
      'user/', 'order/', 'cart/', 'address/', 'coupon/', 'points/',
      'recharge/', 'balance/', 'shop/', 'comment/', 'signin/', 'bargain/',
      'sharing/', 'sharp/', 'live/'
    ];
    return gated.some(prefix => url.indexOf(prefix) === 0);
  },

  /**
   * 验证是否存在user_info
   */
  validateUserInfo() {
    let user_info = wx.getStorageSync('user_info');
    return !!wx.getStorageSync('user_info');
  },

  /**
   * 小程序主动更新
   */
  updateManager() {
    if (!wx.canIUse('getUpdateManager')) {
      return false;
    }
    const updateManager = wx.getUpdateManager();
    updateManager.onCheckForUpdate(res => {
      // 请求完新版本信息的回调
      // console.log(res.hasUpdate)
    });
    updateManager.onUpdateReady(() => {
      wx.showModal({
        title: '更新提示',
        content: '新版本已经准备好，即将重启应用',
        showCancel: false,
        success(res) {
          if (res.confirm) {
            // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
            updateManager.applyUpdate()
          }
        }
      });
    });
    updateManager.onUpdateFailed(() => {
      // 新的版本下载失败
      wx.showModal({
        title: '更新提示',
        content: '新版本下载失败',
        showCancel: false
      })
    });
  },

  /**
   * 获取tabBar页面路径列表
   */
  getTabBarLinks() {
    return tabBarLinks;
  },

  /**
   * 跳转到指定页面
   * 支持tabBar页面
   */
  navigationTo(url) {
    if (!url || url.length == 0) {
      return false;
    }
    url = url.trim();
    let tabBarLinks = this.getTabBarLinks();
    // tabBar页面
    if (tabBarLinks.indexOf(url) > -1) {
      wx.switchTab({
        url: '/' + url
      });
    } else {
      // 普通页面
      wx.navigateTo({
        url: '/' + url
      });
    }
  },

  /**
   * 记录formId
   * (因微信模板消息已下线，所以formId取消不再收集)
   */
  saveFormId(formId) {
    return true;
    // let _this = this;
    // console.log('saveFormId');
    // if (formId === 'the formId is a mock one') {
    //   return false;
    // }
    // _this._post_form('wxapp.formId/save', {
    //   formId: formId
    // }, null, null, null, false);
  },

  /**
   * 生成转发的url参数
   */
  getShareUrlParams(params) {
    let _this = this;
    return util.urlEncode(Object.assign({
      referee_id: _this.getUserId()
    }, params));
  },

  /**
   * 发起微信支付
   */
  wxPayment(option) {
    let options = Object.assign({
      payment: {},
      success: () => {},
      fail: () => {},
      complete: () => {},
    }, option);
    wx.requestPayment({
      timeStamp: options.payment.timeStamp,
      nonceStr: options.payment.nonceStr,
      package: options.payment.package || ('prepay_id=' + options.payment.prepay_id),
      // v2 链路为 MD5，v3 链路为 RSA：以后端返回为准，缺省回退 MD5（老后端 v2 契约）
      signType: options.payment.signType || 'MD5',
      paySign: options.payment.paySign,
      success(res) {
        options.success(res);
      },
      fail(res) {
        options.fail(res);
      },
      complete(res) {
        options.complete(res);
      }
    });
  },

  /**
   * 发起微信虚拟支付（会员VIP直购 / 代币充值）
   * payment 由后端生成，需原样透传 signData / paySig / signature 等字段
   */
  wxVirtualPayment(option) {
    let options = Object.assign({
      payment: {},
      success: () => {},
      fail: () => {},
      complete: () => {},
    }, option);
    let p = options.payment || {};
    wx.requestVirtualPayment({
      mode: p.mode,
      productId: p.productId,
      goodsPrice: p.goodsPrice,
      goodsName: p.goodsName,
      buyQuantity: p.buyQuantity,
      outTradeNo: p.outTradeNo,
      currencyType: p.currencyType,
      platform: p.platform,
      env: p.env,
      attach: p.attach,
      nonceStr: p.nonceStr,
      timeStamp: p.timeStamp,
      signature: p.signature,
      paySig: p.paySig,
      signData: p.signData,
      success(res) {
        options.success(res);
      },
      fail(res) {
        options.fail(res);
      },
      complete(res) {
        options.complete(res);
      }
    });
  },

  /**
   * 验证登录
   */
  checkIsLogin() {
    return wx.getStorageSync('token') != '' && wx.getStorageSync('user_id') != '';
  },

  /**
   * 授权登录
   */
  getUserInfo(userInfo, callback) {
    let App = this;
    wx.showLoading({
      title: "正在登录",
      mask: true
    });
    // 执行微信登录
    wx.login({
      success(res) {
        // 发送用户信息
        App._post_form('user/login', {
          code: res.code,
          user_info: JSON.stringify(userInfo),
          referee_id: wx.getStorageSync('referee_id')
        }, result => {
          // 记录token user_id
          wx.setStorageSync('token', result.data.token);
          wx.setStorageSync('user_id', result.data.user_id);
          // 执行回调函数
          callback && callback();
        }, false, () => {
          wx.hideLoading();
        });
      }
    });
  },

  /**
   * 记录购物车商品总数量
   * @param {*} value 
   */
  setCartTotalNum(value) {
    wx.setStorageSync('cartTotalNum', Number(value))
  },

  /**
   * 设置购物车tabbar的角标
   */
  setCartTabBadge() {
    const number = wx.getStorageSync('cartTotalNum')
    if (number > 0) {
      wx.setTabBarBadge({
        index: 2,
        text: `${number}`
      })
    } else {
      wx.removeTabBarBadge({
        index: 2
      })
    }
    return
  }

});
