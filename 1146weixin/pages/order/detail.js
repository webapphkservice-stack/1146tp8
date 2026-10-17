const App = getApp();

// 枚举类：发货方式
import DeliveryTypeEnum from '../../utils/enum/DeliveryType.js';

// 枚举类：支付方式
import PayTypeEnum from '../../utils/enum/order/PayType'


Page({

  /**
   * 页面的初始数据
   */
  data: {

    // 配送方式
    DeliveryTypeEnum,

    // 支付方式
    PayTypeEnum,

    order_id: null,
    order: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 支持微信订单中心跳转：PATH 携带 order_no（即 out_trade_no）
    _this.data.order_id = options.order_id || null;
    if (options.order_no) {
      // 通过订单号查询，返回结果中包含 order_id，供后续取消/支付等操作使用
      _this.getOrderDetailByNo(options.order_no);
    } else {
      // 获取订单详情
      _this.getOrderDetail(options.order_id);
    }
  },

  /**
   * 通过订单号（out_trade_no）获取订单详情——微信订单中心跳转
   */
  getOrderDetailByNo(order_no) {
    let _this = this;
    App._get('user.order/detail', {
      order_no
    }, result => {
      // 记录 order_id 供页面内取消/支付等操作使用
      _this.data.order_id = result.data.order.order_id;
      _this.setData(result.data);
    });
  },

  /**
   * 获取订单详情
   */
  getOrderDetail(order_id) {
    let _this = this;
    App._get('user.order/detail', {
      order_id
    }, result => {
      _this.setData(result.data);
    });
  },

  /**
   * 跳转到商品详情
   */
  onTargetGoods(e) {
    let goods_id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../goods/index?goods_id=' + goods_id
    });
  },

  /**
   * 取消订单
   */
  cancelOrder(e) {
    let _this = this;
    let order_id = _this.data.order_id;
    wx.showModal({
      title: "提示",
      content: "确认取消订单？",
      success(o) {
        if (o.confirm) {
          App._post_form('user.order/cancel', {
            order_id
          }, result => {
            wx.navigateBack();
          });
        }
      }
    });
  },

  /**
   * 确认收货
   * - 微信支付订单: 优先调起微信官方「确认收货」组件(实物电商发货管理),
   *   用户确认后平台会推送收货结果, 这里同步本地状态
   * - 非微信支付订单: 走原有弹窗确认
   */
  receipt(e) {
    const _this = this;
    const order_id = _this.data.order_id;
    const order = _this.data.detail || {};
    if (order.transaction_id) {
      // 记录待确认订单，组件确认结果会通过 App.onShow 的 referrerInfo 回传
      getApp().globalData.pendingReceiptOrderId = order_id;
      wx.openBusinessView({
        businessType: 'weappOrderConfirm',
        extraData: {
          transaction_id: order.transaction_id
        },
        // openBusinessView 的 success/fail 仅表示组件是否成功拉起，不代表用户已确认；
        // 真正的确认结果由微信通过 navigateBackMiniProgram 回传至 App.onShow，切勿在此处同步
        fail(err) {
          // 组件不可用或被取消时, 降级为原有确认弹窗
          const msg = (err && err.errMsg) || '';
          if (msg.indexOf('cancel') === -1 && msg.indexOf('fail') !== -1) {
            _this._receiptByModal(order_id);
          }
        }
      });
    } else {
      _this._receiptByModal(order_id);
    }
  },

  /**
   * 弹窗确认收货(原有逻辑)
   */
  _receiptByModal(order_id) {
    const _this = this;
    wx.showModal({
      title: "提示",
      content: "确认收到商品？",
      success(o) {
        if (o.confirm) {
          _this._syncReceipt(order_id);
        }
      }
    });
  },

  /**
   * 提交确认收货到后端并刷新页面
   */
  _syncReceipt(order_id) {
    const _this = this;
    App._post_form('user.order/receipt', {
      order_id
    }, result => {
      _this.getOrderDetail(order_id);
    });
  },

  /**
   * 申请售后
   */
  onApplyRefund(e) {
    wx.navigateTo({
      url: './refund/apply/apply?order_goods_id=' + e.currentTarget.dataset.id,
    })
  },

  /**
   * 跳转到门店详情
   */
  onTargetShop(e) {
    wx.navigateTo({
      url: '../shop/detail/index?shop_id=' + e.currentTarget.dataset.id,
    })
  },

  /**
   * 点击付款按钮
   */
  onPayOrder(e) {
    let _this = this;
    // 显示支付方式弹窗
    _this.onTogglePayPopup();
  },

  /**
   * 选择支付方式
   */
  onSelectPayType(e) {
    let _this = this;
    // 隐藏支付方式弹窗
    _this.onTogglePayPopup();
    if (!_this.data.showPayPopup) {
      // 发起付款请求
      _this.payment(e.currentTarget.dataset.value);
    }
  },

  /**
   * 显示/隐藏支付方式弹窗
   */
  onTogglePayPopup() {
    this.setData({
      showPayPopup: !this.data.showPayPopup
    });
  },

  /**
   * 发起付款请求
   */
  payment(payType) {
    let _this = this,
      orderId = _this.data.order_id;
    // 显示loading
    wx.showLoading({
      title: '正在处理...',
    });
    App._post_form('user.order/pay', {
      order_id: orderId,
      payType
    }, result => {
      if (result.code === -10) {
        App.showError(result.msg);
        return false;
      }

      // 发起微信支付
      if (result.data.pay_type == PayTypeEnum.WECHAT.value) {
        App.wxPayment({
          payment: result.data.payment,
          success() {
            _this.getOrderDetail(orderId);
          },
          fail() {
            App.showError(result.msg.error);
          },
        });
      }

      // 余额支付
      if (result.data.pay_type == PayTypeEnum.BALANCE.value) {
        App.showSuccess(result.msg.success, () => {
          _this.getOrderDetail(orderId);
        });
      }

    }, null, () => {
      wx.hideLoading();
    });
  },

  /**
   * 复制虚拟商品发货内容
   */
  onCopyContent(e) {
    let content = e.currentTarget.dataset.content;
    wx.setClipboardData({
      data: content,
      success() {
        App.showSuccess('已复制到剪贴板');
      }
    });
  },

  /**
   * 转发分享（好友代付）
   */
  onShareAppMessage() {
    let _this = this,
      order = _this.data.order;
    // 待付款订单：分享好友代付页面
    if (order.order_id && order.pay_status.value == 10) {
      return {
        title: '帮我付一下呗，谢谢啦~',
        path: '/pages/order/friend-pay/index?' + App.getShareUrlParams({
          order_id: order.order_id
        })
      };
    }
    // 默认分享首页
    return {
      title: '',
      path: '/pages/index/index?' + App.getShareUrlParams()
    };
  },

});