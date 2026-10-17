const App = getApp();

// 支付方式：与后台 PayType 枚举保持一致
const PAY_TYPE = {
  10: '余额支付',
  20: '微信支付',
  30: '好友代付',
  40: '支付宝',
  50: 'Stripe',
  60: '虚拟支付',
  70: '代币支付'
};
// 支付状态：10 待支付 / 20 已支付
const PAY_STATUS = {
  10: '待支付',
  20: '已支付'
};

Page({

  /**
   * 页面的初始数据
   */
  data: {
    list: [],       // VIP开通订单列表
    isLoading: true // 是否正在加载
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    this.getVipOrderList();
  },

  /**
   * 获取VIP开通订单列表
   */
  getVipOrderList: function() {
    let _this = this;
    App._get('user.vip/order', {}, function(result) {
      let list = result.data.list || [];
      list.forEach(function(item) {
        item.pay_type_name = PAY_TYPE[item.pay_type] || '未知';
        item.pay_status_text = PAY_STATUS[item.pay_status] || '未知';
        item.create_time_text = _this.formatDate(item.create_time);
        item.expire_text = item.vip_expire_time ? _this.formatDate(item.vip_expire_time) : '-';
      });
      _this.setData({
        list: list,
        isLoading: false
      });
    });
  },

  /**
   * 时间戳格式化
   */
  formatDate: function(timestamp) {
    if (!timestamp) return '-';
    let date = new Date(timestamp * 1000);
    let year = date.getFullYear();
    let month = (date.getMonth() + 1).toString().padStart(2, '0');
    let day = date.getDate().toString().padStart(2, '0');
    let hour = date.getHours().toString().padStart(2, '0');
    let minute = date.getMinutes().toString().padStart(2, '0');
    return year + '-' + month + '-' + day + ' ' + hour + ':' + minute;
  }

});
