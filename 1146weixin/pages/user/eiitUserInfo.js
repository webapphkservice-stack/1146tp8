const App = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isLogin: false,
    userInfo: {}, // 用户信息
    nickName: '',
    avatarUrl: '',
    mobile: '', // 注册手机号码
    userIcon: '', // 后台设置的默认头像（图标管理）
    showModal: false, // 是否显示昵称弹窗
    tempNickName: '', // 弹窗中的临时昵称
    isBindWechat: false, // 是否已绑定微信（用于微信支付）
    showMobileModal: false, // 是否显示绑定手机号弹窗
    tempMobile: '', // 弹窗中的手机号
    tempCode: '', // 弹窗中的验证码
    mobileCountdown: 0, // 验证码倒计时
    mobileCodeText: '获取验证码', // 验证码按钮文字
    whatsapp: '', // 绑定的 WhatsApp 号码
    showWhatsappModal: false, // 是否显示绑定 WhatsApp 弹窗
    tempWhatsapp: '', // 弹窗中的 WhatsApp 号码
    tempWhatsappCode: '', // 弹窗中的验证码
    whatsappCountdown: 0, // 验证码倒计时
    whatsappCodeText: '获取验证码', // 验证码按钮文字
    registerMethod: '' // 注册方式: wechat / mobile / whatsapp / 空
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 获取后台设置的默认头像（图标管理）
    App._get('page/index', {}, function (result) {
      const iconData = (result.data && result.data.icon) || {};
      _this.setData({ userIcon: iconData.userIcon || '' });
      // 当前尚无头像时，先用默认头像兜底
      if (!_this.data.avatarUrl) {
        _this.setData({ avatarUrl: iconData.userIcon || '' });
      }
    });
    // 主动触发微信官方隐私授权弹窗（chooseAvatar / 昵称需在隐私协议同意后调用）
    if (typeof wx.requirePrivacyAuthorize === 'function') {
      wx.requirePrivacyAuthorize({
        success: () => {
          _this.getUserDetail();
        },
        fail: () => {
          _this.getUserDetail();
        },
        complete: () => {}
      });
    } else {
      this.getUserDetail();
    }
  },

  /**
   * 获取当前用户信息
   */
  getUserDetail() {
    let _this = this;
    App._get('user.index/detail', {}, function (result) {
      _this.setData(result.data);
      // 处理昵称，去除换行符
      let nickName = result.data.userInfo.nickName || '';
      nickName = nickName.replace(/[\n\r]/g, '').replace(/\s+/g, ' ').trim();
      const _info = result.data.userInfo || {};
      // 推导注册方式: 微信(已绑微信) > WhatsApp > 手机
      let registerMethod = '';
      if (_info.is_bind_wechat) {
        registerMethod = 'wechat';
      } else if (_info.whatsapp) {
        registerMethod = 'whatsapp';
      } else if (_info.mobile) {
        registerMethod = 'mobile';
      }
      _this.setData({
        avatarUrl: result.data.userInfo.avatarUrl || _this.data.userIcon || '',
        nickName: nickName || '微信用户',
        mobile: result.data.userInfo.mobile || '',
        whatsapp: result.data.userInfo.whatsapp || '',
        isBindWechat: !!(result.data.userInfo && result.data.userInfo.is_bind_wechat),
        registerMethod: registerMethod
      });
    });
  },

  /**
   * 显示昵称修改弹窗
   */
  showNicknameModal() {
    this.setData({
      showModal: true,
      tempNickName: this.data.nickName === '微信用户' ? '' : this.data.nickName
    });
  },

  /**
   * 隐藏昵称修改弹窗
   */
  hideNicknameModal() {
    this.setData({
      showModal: false,
      tempNickName: ''
    });
  },

  /**
   * 阻止事件冒泡
   */
  stopPropagation() {
    // 什么都不做，只是阻止冒泡
  },

  /**
   * 弹窗输入框输入事件
   */
  onModalInput(e) {
    // 去除换行符和多余空格
    let value = e.detail.value || '';
    value = value.replace(/[\n\r]/g, '').replace(/\s+/g, ' ').trim();
    this.setData({
      tempNickName: value
    });
  },

  /**
   * 确认修改昵称
   */
  confirmNickname() {
    let _this = this;
    let nickname = _this.data.tempNickName.trim();

    // 验证昵称长度
    if (nickname.length < 2) {
      App.showError('昵称长度不能少于2个字符');
      return;
    }

    if (nickname.length > 20) {
      App.showError('昵称长度不能超过20个字符');
      return;
    }

    // 提交到后端
    App._post_form('User/update_nickname', {
      nickname: nickname
    }, function (result) {
      console.log('修改昵称成功:', result);
      _this.setData({
        nickName: nickname,
        showModal: false,
        tempNickName: ''
      });
      App.showSuccess('修改成功');
    }, function (err) {
      console.log('修改昵称失败:', err);
      App.showError('修改失败，请重试');
    }, function () {
      // complete 回调
    }, false);
    },

    /**
     * 绑定微信：补充 open_id，用于订单微信支付
     */
    onBindWechat() {
      let _this = this;
      if (_this.data.isBindWechat) {
        return false;
      }
      if (!App.checkIsLogin()) {
        App.doLogin();
        return false;
      }
      wx.login({
        success(res) {
          if (!res.code) {
            App.showError('微信登录失败，请重试');
            return;
          }
          wx.showLoading({ title: '绑定中...', mask: true });
          App._post_form('user/bindWechat', { code: res.code }, function () {
            _this.setData({ isBindWechat: true });
            App.showSuccess('微信绑定成功');
          }, null, function () {
            wx.hideLoading();
          }, false);
        },
        fail() {
          App.showError('微信登录失败，请重试');
        }
      });
    },

    /**
     * 显示绑定手机号弹窗
     */
    showBindMobile() {
      let _this = this;
      if (!App.checkIsLogin()) {
        App.doLogin();
        return false;
      }
      _this.setData({
        showMobileModal: true,
        tempMobile: '',
        tempCode: ''
      });
    },

    /**
     * 隐藏绑定手机号弹窗
     */
    hideBindMobile() {
      this.setData({
        showMobileModal: false,
        tempMobile: '',
        tempCode: ''
      });
    },

    /**
     * 弹窗手机号输入
     */
    onBindMobileInput(e) {
      this.setData({ tempMobile: (e.detail.value || '').trim() });
    },

    /**
     * 弹窗验证码输入
     */
    onBindCodeInput(e) {
      this.setData({ tempCode: (e.detail.value || '').trim() });
    },

    /**
     * 发送绑定手机号验证码
     */
    getBindMobileCode() {
      const _this = this;
      const mobile = _this.data.tempMobile;
      if (!/^1\d{10}$/.test(mobile)) {
        App.showError('请输入正确的手机号码');
        return;
      }
      wx.showLoading({ title: '发送中', mask: true });
      App._post_form('user/sendCode', { mobile: mobile }, function (result) {
        wx.hideLoading();
        if (result.data && result.data.code) {
          App.showSuccess('验证码：' + result.data.code);
        } else {
          App.showSuccess('验证码已发送');
        }
        _this.startBindCountdown();
      }, null, function () { wx.hideLoading(); }, false);
    },

    /**
     * 验证码倒计时
     */
    startBindCountdown() {
      const _this = this;
      let count = 60;
      _this.setData({ mobileCountdown: count, mobileCodeText: count + 's' });
      _this._mobileTimer && clearInterval(_this._mobileTimer);
      _this._mobileTimer = setInterval(function () {
        count--;
        if (count <= 0) {
          clearInterval(_this._mobileTimer);
          _this._mobileTimer = null;
          _this.setData({ mobileCountdown: 0, mobileCodeText: '获取验证码' });
        } else {
          _this.setData({ mobileCountdown: count, mobileCodeText: count + 's' });
        }
      }, 1000);
    },

    /**
     * 确认绑定手机号
     */
    confirmBindMobile() {
      const _this = this;
      const mobile = _this.data.tempMobile;
      const code = _this.data.tempCode;
      if (!/^1\d{10}$/.test(mobile)) {
        App.showError('请输入正确的手机号码');
        return;
      }
      if (!/^\d{4,6}$/.test(code)) {
        App.showError('请输入验证码');
        return;
      }
      wx.showLoading({ title: '绑定中...', mask: true });
      App._post_form('user/bindMobile', { mobile: mobile, code: code }, function (result) {
        _this.setData({
          mobile: mobile,
          showMobileModal: false,
          tempMobile: '',
          tempCode: ''
        });
        // 该手机号在另一平台已有账号: 后端已完成账号合并, 需重新拉取数据
        if (result.data && result.data.is_merged == 1) {
          wx.showModal({
            title: '账号关联成功',
            content: '已将您的微信与支付宝账号合并，订单、余额、积分等数据已合并至当前账号。',
            showCancel: false,
            confirmText: '知道了',
            success() {
              _this.getUserDetail && _this.getUserDetail();
            }
          });
          return;
        }
        App.showSuccess('手机号绑定成功');
      }, null, function () { wx.hideLoading(); }, false);
    },

    /**
     * 显示绑定 WhatsApp 弹窗
     */
    showBindWhatsapp() {
      let _this = this;
      if (!App.checkIsLogin()) {
        App.doLogin();
        return false;
      }
      _this.setData({
        showWhatsappModal: true,
        tempWhatsapp: '',
        tempWhatsappCode: ''
      });
    },

    /**
     * 隐藏绑定 WhatsApp 弹窗
     */
    hideBindWhatsapp() {
      this.setData({
        showWhatsappModal: false,
        tempWhatsapp: '',
        tempWhatsappCode: ''
      });
    },

    /**
     * 弹窗 WhatsApp 号码输入
     */
    onBindWhatsappInput(e) {
      this.setData({ tempWhatsapp: (e.detail.value || '').trim() });
    },

    /**
     * 弹窗验证码输入
     */
    onBindWhatsappCodeInput(e) {
      this.setData({ tempWhatsappCode: (e.detail.value || '').trim() });
    },

    /**
     * 发送绑定 WhatsApp 验证码
     */
    getBindWhatsappCode() {
      const _this = this;
      const phone = (this.data.tempWhatsapp || '').replace(/[^\d]/g, '');
      if (!/^\d{8,16}$/.test(phone)) {
        App.showError('请输入正确的 WhatsApp 号码（8-16 位国际号码）');
        return;
      }
      wx.showLoading({ title: '发送中', mask: true });
      App._post_form('user/whatsappSendCode', { phone: phone, event: 'mobilelogin', channelType: 'whatsapp' }, function (result) {
        console.log('[whatsappSendCode] success result:', result);
        wx.hideLoading();
        // 先启动倒计时, 避免后续逻辑异常阻断
        _this.startWhatsappCountdown();
        if (result.data && result.data.code) {
          App.showSuccess('验证码：' + result.data.code);
        } else {
          App.showSuccess('验证码已发送');
        }
      }, function (err) {
        console.log('[whatsappSendCode] fail:', err);
        wx.hideLoading();
      }, null, false);
    },

    /**
     * 验证码倒计时
     */
    startWhatsappCountdown() {
      const _this = this;
      let count = 60;
      _this.setData({ whatsappCountdown: count, whatsappCodeText: count + 's' });
      _this._whatsappTimer && clearInterval(_this._whatsappTimer);
      _this._whatsappTimer = setInterval(function () {
        count--;
        if (count <= 0) {
          clearInterval(_this._whatsappTimer);
          _this._whatsappTimer = null;
          _this.setData({ whatsappCountdown: 0, whatsappCodeText: '获取验证码' });
        } else {
          _this.setData({ whatsappCountdown: count, whatsappCodeText: count + 's' });
        }
      }, 1000);
    },

    /**
     * 确认绑定 WhatsApp
     */
    confirmBindWhatsapp() {
      const _this = this;
      const phone = (this.data.tempWhatsapp || '').replace(/[^\d]/g, '');
      const code = (this.data.tempWhatsappCode || '').trim();
      if (!/^\d{8,16}$/.test(phone)) {
        App.showError('请输入正确的 WhatsApp 号码');
        return;
      }
      if (!/^\d{4,6}$/.test(code)) {
        App.showError('请输入验证码');
        return;
      }
      wx.showLoading({ title: '绑定中...', mask: true });
      App._post_form('user/bindWhatsapp', { phone: phone, code: code }, function () {
        _this.setData({
          whatsapp: phone,
          showWhatsappModal: false,
          tempWhatsapp: '',
          tempWhatsappCode: ''
        });
        App.showSuccess('WhatsApp 绑定成功');
      }, null, function () { wx.hideLoading(); }, false);
    },

    onChooseAvatar() {
    let _this = this;
    // 使用 chooseMedia（相册/拍照）选择头像，避免依赖微信隐私授权接口 chooseAvatar
    // 在隐私保护指引未通过审核时 chooseAvatar 会被禁用，导致无法修改头像
    const pick = () => {
      if (typeof wx.chooseMedia === 'function') {
        wx.chooseMedia({
          count: 1,
          mediaType: ['image'],
          sourceType: ['album', 'camera'],
          success(res) {
            const file = res.tempFiles && res.tempFiles[0];
            _this.handleAvatar(file ? file.tempFilePath : '');
          },
          fail() {
            App.showError('选择图片失败，请重试');
          }
        });
      } else {
        wx.chooseImage({
          count: 1,
          success(res) {
            _this.handleAvatar(res.tempFilePaths[0] || '');
          },
          fail() {
            App.showError('选择图片失败，请重试');
          }
        });
      }
    };
    pick();
  },

  /**
   * 处理选择的头像文件并上传
   */
  handleAvatar(avatarUrl) {
    let _this = this;
    console.log('选择的头像:', avatarUrl);

    if (!avatarUrl) {
      App.showError('获取头像失败，请重试');
      return;
    }

    // 验证文件路径是否有效
    wx.getFileInfo({
      filePath: avatarUrl,
      success: function(res) {
        console.log('文件信息:', res);
        _this.setData({
          avatarUrl: avatarUrl
        });
        // 上传到服务器
        _this.uploadAvatar(avatarUrl);
      },
      fail: function(err) {
        console.error('获取文件信息失败:', err);
        // 如果是开发者工具环境问题，尝试直接使用
        if (avatarUrl.indexOf('tmp') > -1 || avatarUrl.indexOf('http') > -1) {
          _this.setData({
            avatarUrl: avatarUrl
          });
          _this.uploadAvatar(avatarUrl);
        } else {
          App.showError('头像文件读取失败，请重试');
        }
      }
    });
  },

  /**
   * 上传头像
   */
  uploadAvatar: function (filePath) {
    let _this = this;
    
    wx.showLoading({ title: '上传中...' });
    
    // 文件上传
    wx.uploadFile({
      url: App.api_root + 'upload/image',
      filePath: filePath,
      name: 'iFile',
      formData: {
        wxapp_id: App.getWxappId(),
        token: wx.getStorageSync('token')
      },
      success: function (res) {
        wx.hideLoading();
        console.log('上传响应:', res);
        
        try {
          let result = typeof res.data === "object" ? res.data : JSON.parse(res.data);
          console.log('解析结果:', result);
          
          if (result.code === 1) {
            let filePath = result.data.file_path || result.data.url;
            console.log('文件路径:', filePath);
            
            // 更新到后端
            _this.updateAvatarUrl(filePath);
          } else {
            App.showError(result.msg || '上传失败');
          }
        } catch (e) {
          console.error('解析响应失败:', e);
          App.showError('上传失败，请重试');
        }
      },
      fail: function (err) {
        wx.hideLoading();
        console.error('上传失败:', err);
        App.showError('网络错误，请重试');
      }
    });
  },

  /**
   * 更新头像URL到服务器
   */
  updateAvatarUrl: function (avatarUrl) {
    let _this = this;
    
    App._post_form('User/update_avatarurl', {
      avatarUrl: avatarUrl
    }, function (result) {
      console.log('更新头像结果:', result);
      // 直接刷新页面头像，避免回到列表才更新
      if (result.data && result.data.avatarUrl) {
        _this.setData({ avatarUrl: result.data.avatarUrl });
      }
      App.showSuccess('头像更新成功');
    }, function (err) {
      console.log('更新头像失败:', err);
      App.showError('头像更新失败');
    }, function () {
      // complete 回调
    }, false);
  },

})
