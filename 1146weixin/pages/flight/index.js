let App = getApp();
const flight = require('../../api/flight.js');

Page({

  /**
   * 页面的初始数据
   */
  data: {
    origin: '',
    destination: '',
    date: '',
    cabin: 1,
    list: [],
    source: '',
    defaultTitle: '',
    defaultFlightNo: '',
    loading: false
  },

  /**
   * 生命周期：进入页面即加载后台默认航班并展示
   */
  onLoad: function() {
    this.loadDefault();
  },

  /**
   * 加载后台默认航班，未手动查询时直接展示
   */
  loadDefault: function() {
    const _this = this;
    flight.getDefaults().then(function(res) {
      const f = res.flight;
      if (!f) {
        return;
      }
      let flights = f.flights;
      if (typeof flights === 'string') {
        try { flights = JSON.parse(flights); } catch (e) { flights = []; }
      }
      _this.setData({
        origin: f.origin,
        destination: f.destination,
        date: f.departure_date,
        cabin: f.cabin || 1,
        list: (flights || []).map(function(it) {
          return Object.assign({}, it, {
            stops: (it.stops == null ? 0 : it.stops),
            currency_symbol: (it.currency === 'MYR' ? 'RM ' : '¥ ')
          });
        }),
        source: 'default',
        defaultTitle: f.title || '',
        defaultFlightNo: f.flight_no || '',
        loading: false
      });
    }).catch(function() {});
  },

  /**
   * 输入
   */
  bindInput: function(e) {
    const field = e.currentTarget.dataset.field;
    this.setData({
      [field]: e.detail.value
    });
  },

  /**
   * 选择出发日期（picker）
   */
  bindDateChange: function(e) {
    this.setData({
      date: e.detail.value
    });
  },

  /**
   * 选择舱位
   */
  switchCabin: function(e) {
    this.setData({
      cabin: e.currentTarget.dataset.cabin
    });
  },

  /**
   * 航班查询
   */
  search: function() {
    const _this = this;
    const data = this.data;
    if (!data.origin || !data.destination || !data.date) {
      App.showError('请填写出发地、目的地与出发日期');
      return;
    }
    this.setData({
      loading: true
    });
    flight.search({
      origin: data.origin,
      destination: data.destination,
      date: data.date,
      cabin: data.cabin,
      adult: 1,
      currency: 'CNY'
    }).then(function(res) {
      let flights = res.flights;
      if (typeof flights === 'string') {
        try { flights = JSON.parse(flights); } catch (e) { flights = []; }
      }
      _this.setData({
        list: (flights || []).map(function(it) {
          return Object.assign({}, it, {
            stops: (it.stops == null ? 0 : it.stops),
            currency_symbol: (it.currency === 'MYR' ? 'RM ' : '¥ ')
          });
        }),
        source: res.source || '',
        loading: false
      });
      if (!res.flights || !res.flights.length) {
        App.showError('未查询到航班，请稍后重试');
      }
    }).catch(function() {
      _this.setData({
        loading: false
      });
    });
  },

})
