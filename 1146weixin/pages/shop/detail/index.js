const App = getApp();

// 拼接门店地址：省/市同名（如香港：province=city="香港特别行政区"）时去重，避免重复显示
function buildDisplayAddress(detail) {
  let region = (detail && detail.region) || {};
  let province = region.province || '';
  let city = region.city || '';
  let district = region.region || '';
  let street = (detail && detail.address) || '';
  if (province && city && province === city) {
    city = '';
  }
  return [province, city, district, street].filter(function (s) { return s; }).join('');
}

Page({


  /**
   * 页面的初始数据
   */
  data: {

    // 门店详情
    detail: {},

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 获取门店详情
    _this.getShopDetail(options.shop_id);
  },

  /**
   * 获取门店详情
   */
  getShopDetail(shop_id) {
    let _this = this;
    App._get('shop/detail', {
      shop_id
    }, function(result) {
      let detail = result.data.detail ? result.data.detail : result.data;
      // 经纬度归一为数值，<map> 组件要求 Number 类型
      detail.latitude = Number(detail.latitude);
      detail.longitude = Number(detail.longitude);
      // 计算去重后的展示地址（港澳台省市同名时只显示一个）
      detail.displayAddress = buildDisplayAddress(detail);
      // 构建门店地图标记点，详情页直接展示位置
      let locationMarkers = [{
        id: 0,
        latitude: Number(detail.latitude),
        longitude: Number(detail.longitude),
        title: detail.shop_name,
        width: 28,
        height: 28,
        callout: {
          content: detail.shop_name,
          color: '#333333',
          fontSize: 12,
          borderRadius: 8,
          padding: 8,
          bgColor: '#ffffff',
          display: 'ALWAYS'
        }
      }];
      _this.setData(Object.assign({}, result.data, {
        locationMarkers: locationMarkers
      }));
    });
  },

  /**
   * 拨打电话
   */
  onMakePhoneCall() {
    let _this = this;
    wx.makePhoneCall({
      phoneNumber: _this.data.detail.phone
    });
  },

  /**
   * 查看位置
   */
  onOpenLocation() {
    let _this = this,
      detail = _this.data.detail;
    wx.openLocation({
      name: detail.shop_name,
      address: detail.displayAddress,
      longitude: Number(detail.longitude),
      latitude: Number(detail.latitude),
      scale: 15
    });
  },

  /**
   * 分享当前页面
   */
  onShareAppMessage() {
    const _this = this;
    // 构建页面参数
    const params = App.getShareUrlParams({
      'shop_id': _this.data.detail.shop_id
    });
    return {
      title: _this.data.detail.article_title,
      path: "/pages/shop/detail/index?" + params
    };
  },

  /**
   * 分享到朋友圈
   * 本接口为 Beta 版本，暂只在 Android 平台支持，详见分享到朋友圈 (Beta)
   * https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/share-timeline.html
   */
  onShareTimeline() {
    const _this = this;
    // 构建页面参数
    const params = App.getShareUrlParams({
      'shop_id': _this.data.detail.shop_id
    });
    return {
      title: _this.data.detail.article_title,
      path: "/pages/shop/detail/index?" + params
    };
  },

})