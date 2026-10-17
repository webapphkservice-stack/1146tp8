let App = getApp();
const myRegion = require('../../utils/myRegion.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    disabled: false,
    nav_select: false, // 快捷导航

    name: '',
    region: '',           // 中国大陆三级地区数组
    state: '',            // 马来西亚州
    city: '',             // 马来西亚城市
    stateList: [
      '吉隆坡', '雪兰莪',
      '柔佛', '吉打', '吉兰丹', '马六甲', '森美兰', '彭亨', '槟城', '霹雳', '玻璃市', '登嘉楼',
      '沙巴', '砂拉越',
      '布城', '纳闽'
    ],
    stateIndex: 0,
    cityList: [],         // 当前州属的城市列表（空则手动输入）
    cityIndex: 0,
    country: 'CN',        // 国家：CN 中国大陆 / MY 马来西亚
    phone: '',
    detail: '',
    showBindTip: false,   // 是否显示"手机号用于关联微信/支付宝账号"提示

    error: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.from = (options && options.from) || '';
    this.checkBindTip();
  },

  /**
   * 切换国家
   */
  switchCountry: function(e) {
    this.setData({
      country: e.currentTarget.dataset.country
    });
  },

  /**
   * 选择州属（马来西亚 13 州 + 3 联邦直辖区）
   * 切换州属时联动城市列表并清空已选城市
   */
  bindStateChange: function(e) {
    let state = this.data.stateList[e.detail.value];
    this.setData({
      state: state,
      stateIndex: e.detail.value,
      cityList: myRegion.getCityList(state),
      cityIndex: 0,
      city: ''
    });
  },

  /**
   * 选择城市（有城市列表的州属）
   */
  bindCityChange: function(e) {
    this.setData({
      city: this.data.cityList[e.detail.value],
      cityIndex: e.detail.value
    });
  },

  /**
   * 输入城市（无城市列表的州属）
   */
  bindCityInput: function(e) {
    this.setData({
      city: e.detail.value
    });
  },

  /**
   * 未绑定手机号的第三方登录用户，提示手机号将用于关联微信/支付宝账号
   */
  checkBindTip: function() {
    let _this = this;
    if (!App.checkIsLogin()) return;
    App._get('user.index/detail', {}, function(result) {
      let userInfo = (result.data && result.data.userInfo) || {};
      _this.setData({ showBindTip: userInfo.is_bind_mobile != 1 });
    });
  },

  /**
   * 表单提交
   */
  saveData: function(e) {
    let _this = this,
      values = e.detail.value
    values.country = this.data.country;
    values.region = this.data.country === 'MY'
      ? [this.data.state, this.data.city]
      : this.data.region;

    // 表单验证
    if (!_this.validation(values)) {
      App.showError(_this.data.error);
      return false;
    }

    // 按钮禁用
    _this.setData({
      disabled: true
    });

    // 提交到后端
    App._post_form('address/add', values, function(result) {
      const goBack = function() {
        // 从外送/结算页过来：保存后直接返回来源下单页（跳过地址列表），
        // 由下单页 onShow 自动刷新并选中地址。
        // 注意：不能用 delta = 页面栈长度-1，否则会一路跳到栈底的外送列表页，
        // 把下单页(checkout)从栈中弹掉，导致"无法跳回下单页"。
        if (_this.from === 'delivery' || _this.from === 'flow') {
          if (result.data && result.data.address_id) {
            wx.setStorageSync('deliveryAddressId', result.data.address_id);
          }
          const pages = getCurrentPages();
          let targetIdx = -1;
          for (let i = 0; i < pages.length; i++) {
            const route = pages[i].route || '';
            if (_this.from === 'delivery' && route.indexOf('delivery/checkout') !== -1) {
              targetIdx = i;
            } else if (_this.from === 'flow' && route.indexOf('flow/checkout') !== -1) {
              targetIdx = i;
            }
          }
          if (targetIdx >= 0 && targetIdx < pages.length - 1) {
            wx.navigateBack({ delta: (pages.length - 1) - targetIdx });
            return;
          }
        }
        // 页面栈只有一层时 navigateBack 会静默失败，兜底跳地址列表
        if (getCurrentPages().length > 1) {
          wx.navigateBack();
        } else {
          wx.redirectTo({ url: '/pages/address/index' });
        }
      };
      // 手机号关联账号的结果提示
      const bind = (result.data && result.data.bind_mobile) || {};
      // bound/need_verify/unverified 均提示：地址手机号仅提示关联，须到「我的」页短信验证才写入账号
      if ((bind.status === 'bound' || bind.status === 'need_verify' || bind.status === 'unverified') && bind.msg) {
        wx.showModal({
          title: '账号关联提示',
          content: bind.msg,
          showCancel: false,
          confirmText: '知道了',
          success: goBack
        });
        return;
      }
      App.showSuccess(result.msg, goBack);
    }, false, function() {
      // 解除禁用
      _this.setData({
        disabled: false
      });
    });
  },

  /**
   * 表单验证
   */
  validation: function(values) {
    if (values.name === '') {
      this.data.error = '收件人不能为空';
      return false;
    }
    if (values.phone.length < 1) {
      this.data.error = '手机号不能为空';
      return false;
    }
    // 手机号校验：支持中国大陆、马来西亚、香港等国际号码
    // 去掉空格、短横线等非数字字符后，要求 6-16 位纯数字
    let phoneVal = (values.phone || '').trim();
    let intlDigits = phoneVal.replace(/\D/g, '');
    if (intlDigits.length < 6 || intlDigits.length > 16) {
      this.data.error = '手机号不符合要求';
      return false;
    }
    if (values.country === 'MY') {
      if (!this.data.state || !this.data.city) {
        this.data.error = '请填写州/省与城市';
        return false;
      }
    } else {
      if (!this.data.region || !this.data.region.length) {
        this.data.error = '省市区不能空';
        return false;
      }
    }
    if (values.detail === '') {
      this.data.error = '详细地址不能为空';
      return false;
    }
    return true;
  },

  /**
   * 修改地区(中国大陆)
   */
  bindRegionChange: function(e) {
    this.setData({
      region: e.detail.value
    })
  },

  /**
   * 获取微信地址(仅中国大陆)
   */
  chooseAddress: function() {
    let _this = this;
    wx.chooseAddress({
      success: function(res) {
        _this.setData({
          name: res.userName,
          phone: res.telNumber,
          region: [res.provinceName, res.cityName, res.countyName],
          detail: res.detailInfo,
          country: 'CN'
        });
      }
    })
  },

})
