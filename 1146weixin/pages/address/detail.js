let App = getApp();
const myRegion = require('../../utils/myRegion.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    disabled: false,
    nav_select: false, // 快捷导航
    region: '',
    state: '',
    city: '',
    stateList: [
      '吉隆坡', '雪兰莪',
      '柔佛', '吉打', '吉兰丹', '马六甲', '森美兰', '彭亨', '槟城', '霹雳', '玻璃市', '登嘉楼',
      '沙巴', '砂拉越',
      '布城', '纳闽'
    ],
    stateIndex: 0,
    cityList: [],         // 当前州属的城市列表（空则手动输入）
    cityIndex: 0,
    country: 'CN',
    detail: {},

    error: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    // 获取当前地址信息
    this.getAddressDetail(options.address_id);
  },

  /**
   * 获取当前地址信息
   */
  getAddressDetail: function(address_id) {
    let _this = this;
    App._get('address/detail', {
      address_id
    }, function(result) {
      let detail = result.data.detail;
      let country = detail.country || 'CN';
      // region 数组：CN 为 [省,市,区]；MY 为 [州,城市,'']
      let region = result.data.region || [];
      let state = detail.state || (country === 'MY' ? (region[0] || '') : '');
      let stateIndex = _this.data.stateList.indexOf(state);
      let city = detail.city || (country === 'MY' ? (region[1] || '') : '');
      // 若该州属有城市列表，回显时定位已选城市的下标
      let cityList = myRegion.getCityList(state);
      let cityIndex = cityList.indexOf(city);
      _this.setData({
        detail: detail,
        country: country,
        state: state,
        stateIndex: stateIndex > -1 ? stateIndex : 0,
        city: city,
        cityList: cityList,
        cityIndex: cityIndex > -1 ? cityIndex : 0,
        region: country === 'CN' ? region : ''
      });
    });
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
    values.address_id = _this.data.detail.address_id;
    App._post_form('address/edit', values, function(result) {
      App.showSuccess(result.msg, function() {
        // 页面栈只有一层时 navigateBack 会静默失败，兜底跳地址列表
        if (getCurrentPages().length > 1) {
          wx.navigateBack();
        } else {
          wx.redirectTo({ url: '/pages/address/index' });
        }
      });
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
          'detail.name': res.userName,
          'detail.phone': res.telNumber,
          'detail.detail': res.detailInfo,
          region: [res.provinceName, res.cityName, res.countyName],
          country: 'CN'
        });
      }
    })
  },

})
