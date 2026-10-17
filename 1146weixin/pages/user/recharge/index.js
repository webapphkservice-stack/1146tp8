const App = getApp();

// 枚举类：充值类型
import RechargeTypeEnum from '../../../utils/enum/recharge/order/RechargeType.js';

Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {}, // 用户信息
    setting: {}, // 充值设置

    // recharge_type: '', // 充值类型
    selectedPlanId: 0, // 当前选中的套餐id
    inputValue: '', // 自定义金额

    disabled: false, //按钮禁用
    rechargeMode: 'balance', // balance=余额充值(普通微信支付) coin=代币充值(虚拟支付)
    defaultMode: 'balance', // 从"我的"页代币入口带入，默认选中的充值方式
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    if (options && options.mode === 'coin') {
      this.setData({ defaultMode: 'coin' });
    }
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    let _this = this;
    // 获取充值中心数据
    _this.getRechargeIndex();
  },

  /**
   * 获取充值中心数据
   */
  getRechargeIndex() {
    let _this = this;
    App._get('recharge/index', {}, function(result) {
      _this.setData(result.data);
      // 若从"我的"页代币入口进入，强制选中代币充值
      if (_this.data.defaultMode === 'coin') {
        _this.setData({ rechargeMode: 'coin' });
      }
    });
  },

  /**
   * 选择充值套餐
   */
  onSelectPlan(e) {
    let _this = this;
    _this.setData({
      selectedPlanId: e.currentTarget.dataset.id,
      inputValue: ''
    });
  },

  /**
   * 绑定金额输入框
   */
  bindMoneyInput(e) {
    let _this = this;
    _this.setData({
      inputValue: e.detail.value,
      selectedPlanId: 0
    })
  },

  /**
   * 切换充值方式
   */
  selectMode(e) {
    this.setData({ rechargeMode: e.currentTarget.dataset.mode });
  },

  /**
   * 立即充值
   */
  onSubmit(e) {
    let _this = this;
    let platform = (wx.getSystemInfoSync().platform === 'ios') ? 'ios' : 'android';

    // 按钮禁用
    _this.setData({
      disabled: true
    });

    // 代币充值：走微信虚拟支付 currency 模式
    if (_this.data.rechargeMode === 'coin') {
      App._post_form('user.coin/buy', {
        plan_id: _this.data.selectedPlanId,
        platform: platform
      }, (result) => {
        App.wxVirtualPayment({
          payment: result.data.payment,
          success() {
            App.showSuccess('充值成功', () => {
              wx.navigateBack();
            });
          },
          fail(res) {
            App.showError('支付已取消');
          }
        });
      }, false, () => {
        _this.setData({ disabled: false });
      });
      return;
    }

    // 余额充值：普通微信支付
    App._post_form('recharge/submit', {
      planId: _this.data.selectedPlanId,
      customMoney: _this.data.inputValue
    }, (result) => {

      // 发起微信支付
      App.wxPayment({
        payment: result.data.payment,
        success() {
          App.showSuccess(result.msg.success, () => {
            wx.navigateBack();
          });
        },
        fail(res) {
          App.showError(result.msg.error);
        },
        complete(res) {

        }
      });

    }, false, () => {
      // 解除禁用
      _this.setData({
        disabled: false
      });
    });
  },



})