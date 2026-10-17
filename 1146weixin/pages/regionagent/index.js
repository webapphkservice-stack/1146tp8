const App = getApp();

Page({

  data: {
    isLogin: false,
    agentInfo: null,
    fee: null,
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    this.setData({ isLogin: App.checkIsLogin() });
    this.getAgentInfo();
  },

  /**
   * 获取区域代理信息
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
      _this.setData({ agentInfo: data, fee: data.fee || null });
    }, function () {});
  },

  /**
   * 申请区域代理(由后台统一审核开通)
   */
  onApply() {
    App.showError('区域代理申请请联系客服或在后台提交');
  }

})
