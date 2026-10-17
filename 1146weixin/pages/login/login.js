const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: {},
    nickName: '',
    avatarUrl: '', // 选中的头像(临时路径)
    isRegister: 0, // 是否已注册: 1=已注册(无需填头像/昵称) 0=未注册或未知
    logo: '',      // 登录页 LOGO（后台图标管理设置）
    // 登录方式: wechat / mobile / whatsapp
    loginType: 'wechat',
    // 手机号码登录
    mobile: '',
    mobileCode: '',
    mobileCountdown: 0,
    mobileCodeText: '获取验证码',
    // WhatsApp 登录
    waPhone: '',
    waCode: '',
    waCountdown: 0,
    waCodeText: '获取验证码'
  },

  /**
   * 拼接页面参数
   */
  buildUrl(route, options = {}) {
    const query = Object.keys(options)
      .filter(key => options[key] !== undefined && options[key] !== null && options[key] !== '')
      .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(options[key])}`)
      .join('&');
    return `/${route}${query ? '?' + query : ''}`;
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 主动触发微信官方隐私授权弹窗（chooseAvatar / 昵称需在隐私协议同意后调用）
    if (typeof wx.requirePrivacyAuthorize === 'function') {
      wx.requirePrivacyAuthorize({
        success: () => {},
        fail: () => {},
        complete: () => {}
      });
    }
    _this.setData({
      options
    });
    // 获取登录页 LOGO（后台 图标管理 中设置）
    App._get('page/index', {}, result => {
      const iconData = (result.data && result.data.icon) || {};
      _this.setData({ logo: iconData.logo || '' });
    }, false, () => {}, false);
    // 预判断微信用户是否已注册: 已注册则无需填写头像/昵称
    wx.login({
      success(res) {
        App._post_form('user/isRegister', {
          code: res.code
        }, result => {
          _this.setData({
            isRegister: result.data.is_register
          });
        }, false, () => {}, false);
      }
    });
  },

  /**
   * 选择头像 (open-type="chooseAvatar"，从微信获取头像)
   */
  onChooseAvatar(e) {
    const _this = this;
    const { avatarUrl } = e.detail;
    if (!avatarUrl) {
      App.showError('获取头像失败，请重试');
      return;
    }
    _this.setData({ avatarUrl });
  },

  /**
   * 输入昵称 (手动输入或失焦时触发, 使用 e.detail.value)
   */
  onNickName(e) {
    // 空字符串时不覆盖: 微信昵称快捷填充走 nicknamereview 时, 输入框会额外触发一次
    // bindinput 且 value 为空, 若直接清空会丢失已填好的昵称
    if (e.detail && e.detail.value !== undefined && e.detail.value !== '') {
      this.setData({
        nickName: e.detail.value.trim()
      });
    }
  },

  /**
   * 微信昵称快捷填充 (点击底部"使用微信昵称"时触发, 兼容 e.detail.nickname / e.detail.value)
   */
  onNicknameReview(e) {
    const nickName = (e.detail && (e.detail.nickname || e.detail.value) || '').trim();
    if (nickName) {
      this.setData({ nickName });
    }
  },

  /**
   * 切换登录方式
   */
  switchLoginType(e) {
    this.setData({ loginType: e.currentTarget.dataset.type });
  },

  /**
   * 手机号码输入框
   */
  onMobileInput(e) {
    this.setData({ mobile: (e.detail.value || '').trim() });
  },

  /**
   * 手机验证码输入框
   */
  onMobileCodeInput(e) {
    this.setData({ mobileCode: (e.detail.value || '').trim() });
  },

  /**
   * WhatsApp 手机号码输入框
   */
  onWaPhoneInput(e) {
    this.setData({ waPhone: (e.detail.value || '').trim() });
  },

  /**
   * WhatsApp 验证码输入框
   */
  onWaCodeInput(e) {
    this.setData({ waCode: (e.detail.value || '').trim() });
  },

  /**
   * 发送短信验证码（手机号码登录）
   */
  getMobileCode() {
    const _this = this;
    const mobile = _this.data.mobile;
    if (!/^1\d{10}$/.test(mobile)) {
      App.showError('请输入正确的手机号码');
      return;
    }
    App._post_form('user/sendCode', { mobile: mobile }, result => {
      // 开发环境未配置短信网关时，后端直接返回验证码
      if (result.data && result.data.code) {
        App.showSuccess('验证码：' + result.data.code);
      } else {
        App.showSuccess('验证码已发送');
      }
      _this.startCountdown('mobile');
    }, false, () => {}, false);
  },

  /**
   * 发送 WhatsApp 验证码
   */
  getWaCode() {
    const _this = this;
    const phone = (_this.data.waPhone || '').replace(/\D/g, '');
    if (!/^\d{8,16}$/.test(phone)) {
      App.showError('请输入正确的国际手机号码（含国家码，8-16 位）');
      return;
    }
    App._post_form('user/whatsappSendCode', {
      phone: phone,
      event: 'mobilelogin',
      channelType: 'whatsapp'
    }, (result) => {
      console.log('[whatsappSendCode] success:', result);
      // 先启动倒计时, 避免 showSuccess/toast 等后续逻辑异常阻断
      _this.startCountdown('whatsapp');
      App.showSuccess('验证码已通过 WhatsApp 发送');
    }, (err) => {
      console.log('[whatsappSendCode] fail:', err);
    }, () => {}, false);
  },

  /**
   * 验证码倒计时
   * @param {string} key 'mobile' | 'whatsapp'
   */
  startCountdown(key) {
    const _this = this;
    const cdKey = key + 'Countdown';
    const textKey = key + 'CodeText';
    const timerKey = '_' + key + 'Timer';
    let count = 60;
    _this.setData({ [cdKey]: count, [textKey]: count + 's' });
    _this[timerKey] && clearInterval(_this[timerKey]);
    _this[timerKey] = setInterval(() => {
      count--;
      if (count <= 0) {
        clearInterval(_this[timerKey]);
        _this[timerKey] = null;
        _this.setData({ [cdKey]: 0, [textKey]: '获取验证码' });
      } else {
        _this.setData({ [cdKey]: count, [textKey]: count + 's' });
      }
    }, 1000);
  },

  /**
   * 手机号码 + 短信验证码 登录 / 注册
   */
  onMobileSubmit() {
    const _this = this;
    const mobile = _this.data.mobile;
    const code = _this.data.mobileCode;
    if (!/^1\d{10}$/.test(mobile)) {
      App.showError('请输入正确的手机号码');
      return;
    }
    if (!/^\d{4,6}$/.test(code)) {
      App.showError('请输入验证码');
      return;
    }
    wx.showLoading({ title: '登录中', mask: true });
    App._post_form('user/login', {
      mobile: mobile,
      code: code,
      referee_id: wx.getStorageSync('referee_id')
    }, result => {
      wx.setStorageSync('token', result.data.token);
      wx.setStorageSync('user_id', result.data.user_id);
      _this.onNavigateBack(1);
    }, false, () => { wx.hideLoading(); }, false);
  },

  /**
   * WhatsApp + 验证码 登录 / 注册
   */
  onWhatsappSubmit() {
    const _this = this;
    const phone = (_this.data.waPhone || '').replace(/\D/g, '');
    const code = _this.data.waCode;
    if (!/^\d{8,16}$/.test(phone)) {
      App.showError('请输入正确的国际手机号码（含国家码，8-16 位）');
      return;
    }
    if (!/^\d{4,6}$/.test(code)) {
      App.showError('请输入验证码');
      return;
    }
    wx.showLoading({ title: '登录中', mask: true });
    App._post_form('user/whatsappLogin', {
      phone: phone,
      code: code,
      referee_id: wx.getStorageSync('referee_id')
    }, result => {
      wx.setStorageSync('token', result.data.token);
      wx.setStorageSync('user_id', result.data.user_id);
      _this.onNavigateBack(1);
    }, false, () => { wx.hideLoading(); }, false);
  },

  /**
   * 授权登录 (新版头像+昵称方案)
   */
  onSubmit() {
    let _this = this;
    let nickName = (_this.data.nickName || '').trim();
    // 已注册用户: 无需填写昵称/头像, 直接授权登录 (后端保留原昵称头像)
    if (_this.data.isRegister == 1) {
      App.getUserInfo({ nickName: '', avatarUrl: '' }, function() {
        _this.onNavigateBack(1);
      });
      return;
    }
    // 未注册用户: 引导填写昵称
    if (!nickName) {
      App.showError('请先设置昵称');
      return;
    }
    // 微信登录并注册/登录 (user_info 仅含昵称, 头像留空, 登录后再上传)
    App.getUserInfo({ nickName: nickName, avatarUrl: '' }, function() {
      // 登录成功(token已写入), 若用户选择了头像则上传
      if (_this.data.avatarUrl && _this.data.avatarUrl.indexOf('tmp') > -1) {
        _this.uploadAvatar(_this.data.avatarUrl);
      }
      // 跳转回原页面
      _this.onNavigateBack(1);
    });
  },

  /**
   * 上传头像到服务器
   */
  uploadAvatar(filePath) {
    let _this = this;
    wx.showLoading({ title: '上传中...' });
    wx.uploadFile({
      url: App.api_root + 'upload/image',
      filePath: filePath,
      name: 'iFile',
      formData: {
        wxapp_id: App.getWxappId(),
        token: wx.getStorageSync('token')
      },
      success(res) {
        wx.hideLoading();
        try {
          let result = typeof res.data === 'object' ? res.data : JSON.parse(res.data);
          if (result.code === 1) {
            let url = result.data.file_path || result.data.url;
            if (url) {
              _this.updateAvatarUrl(url);
            }
          } else {
            App.showError(result.msg || '头像上传失败');
          }
        } catch (e) {
          App.showError('头像上传失败，请重试');
        }
      },
      fail() {
        wx.hideLoading();
        App.showError('网络错误，请重试');
      }
    });
  },

  /**
   * 更新头像地址到服务器
   */
  updateAvatarUrl(avatarUrl) {
    App._post_form('User/update_avatarurl', {
      avatarUrl: avatarUrl
    }, function() {
      // 静默成功
    }, function() {
      // 失败不影响登录
    }, function() {
      // complete
    }, false);
  },

  /**
   * 暂不登录：放弃登录，直接进入首页（避免跳回需登录的触发页被再次弹回）
   */
  onNotLogin() {
    const home = App.getTabBarLinks()[0] || 'pages/index/index';
    wx.removeStorageSync('currentPage');
    wx.reLaunch({ url: '/' + home });
  },

  /**
   * 页面卸载时清理倒计时
   */
  onUnload() {
    this._mobileTimer && clearInterval(this._mobileTimer);
    this._waTimer && clearInterval(this._waTimer);
  },

  /**
   * 授权成功 跳转回原页面
   */
  onNavigateBack(delta) {
    const currentPage = wx.getStorageSync('currentPage');
    wx.removeStorageSync('currentPage');

    if (currentPage && currentPage.route) {
      const targetUrl = this.buildUrl(currentPage.route, currentPage.options);
      const tabBarLinks = App.getTabBarLinks();
      if (tabBarLinks.indexOf(currentPage.route) > -1) {
        wx.reLaunch({
          url: `/${currentPage.route}`
        });
        return;
      }
      wx.redirectTo({
        url: targetUrl
      });
      return;
    }

    wx.navigateBack({
      delta: Number(delta || 1)
    });
  },

})
