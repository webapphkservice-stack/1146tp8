let App = getApp();
const taxi = require('../../api/taxi.js');

Page({
  data: {
    enable: 1,
    currency: 'RM',
    airports: [],
    airportIndex: -1,
    types: ['普通', '商务'],
    typeIndex: 0,
    flight_no: '',
    departure_time: '',
    takeoff_time: '',
    landing_time: '',
    hotel_name: '',
    hotel_address: '',
    contact_name: '',
    contact_phone: '',
    remark: '',
    price: '',
    submitting: false
  },

  onLoad: function () {
    this.getSetting();
  },

  getSetting: function () {
    const that = this;
    taxi.setting().then(function (res) {
      const airports = res.airports || [];
      that.setData({
        enable: res.enable === undefined ? 1 : res.enable,
        currency: res.currency || 'RM',
        airports: airports,
        airportIndex: airports.length > 0 ? 0 : -1
      });
      that.calcPrice();
    });
  },

  onAirportChange: function (e) {
    this.setData({ airportIndex: parseInt(e.detail.value, 10) });
    this.calcPrice();
  },

  onTypeChange: function (e) {
    this.setData({ typeIndex: parseInt(e.detail.value, 10) });
    this.calcPrice();
  },

  calcPrice: function () {
    const airports = this.data.airports;
    const idx = this.data.airportIndex;
    if (idx < 0 || !airports[idx]) {
      this.setData({ price: '' });
      return;
    }
    const a = airports[idx];
    const price = this.data.typeIndex === 0 ? a.price_normal : a.price_business;
    this.setData({ price: price });
  },

  inputChange: function (e) {
    const field = e.currentTarget.dataset.field;
    const val = e.detail.value;
    this.setData({ [field]: val });
  },

  /**
   * 提交预约
   */
  onSubmit: function () {
    const that = this;
    const d = that.data;
    const airport = d.airports[d.airportIndex];
    if (!airport) {
      wx.showToast({ title: '请选择抵达机场', icon: 'none' });
      return;
    }
    if (!d.flight_no) {
      wx.showToast({ title: '请填写航班号', icon: 'none' });
      return;
    }
    if (d.submitting) return;
    that.setData({ submitting: true });
    taxi.submit({
      airport_name: airport.name,
      car_type: d.typeIndex,
      flight_no: d.flight_no,
      departure_time: d.departure_time,
      takeoff_time: d.takeoff_time,
      landing_time: d.landing_time,
      hotel_name: d.hotel_name,
      hotel_address: d.hotel_address,
      contact_name: d.contact_name,
      contact_phone: d.contact_phone,
      remark: d.remark
    }).then(function () {
      that.setData({ submitting: false });
      wx.showToast({ title: '预约提交成功', icon: 'success' });
      setTimeout(function () {
        wx.navigateTo({ url: '/pages/taxi/order' });
      }, 800);
    }).catch(function () {
      that.setData({ submitting: false });
    });
  },

  /**
   * 我的出租车订单
   */
  goOrder: function () {
    wx.navigateTo({ url: '/pages/taxi/order' });
  }
});
