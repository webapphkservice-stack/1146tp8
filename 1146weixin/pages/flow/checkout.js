import Util from '../../utils/util.js';
import Verify from '../../utils/verify.js';
import DeliveryTypeEnum from '../../utils/enum/DeliveryType.js';
import PayTypeEnum from '../../utils/enum/order/PayType';
import Dialog from '../../components/dialog/dialog';
import Toast from '../../components/toast/toast';

const App = getApp()
import delivery from '../../api/delivery.js';
Page({

  /**
   * 页面的初始数据
   */
  data: {

    // 当前页面参数
    options: {},

    // // 系统设置：配送方式
    // deliverySetting: [],

    // 系统设置
    setting: {
      delivery: [], // 支持的配送方式
    },

    // 配送方式
    isShowTab: false,
    DeliveryTypeEnum,
    curDelivery: null,

    // 支付方式
    PayTypeEnum,
    curPayType: PayTypeEnum.WECHAT.value,

    address: null, // 默认收货地址
    exist_address: false, // 是否存在收货地址

    deliveryList: [], // 本地外送服务列表
    selectDeliveryId: 0, // 选择的外送服务id

    selectedShopId: 0, // 已选择的门店id（自提点 或 门店配送派送门店）
    deliveryShopList: [], // 可配送到收货地址的候选门店（送水：门店配送）
    linkman: '', // 自提联系人
    phone: '', // 自提联系电话

    // 商品信息
    goods: {},

    // 选择的优惠券
    selectCouponId: 0,

    // 是否使用积分抵扣
    isUsePoints: false,

    // 是否使用水票抵扣(送水：门店配送)
    useWaterTicket: false,

    // 买家留言
    remark: '',

    // 禁用submit按钮
    disabled: false,

    has_error: false,
    error_msg: '',

    notRefresh: false, // 不允许刷新

    // 是否虚拟商品
    isVirtualGoods: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    let _this = this;
    // 当前页面参数
    _this.setData({
      options
    });
    console.log(options);
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow() {
    let _this = this;
    // 获取当前订单信息
    !_this.data.notRefresh && _this.getOrderData();
  },

  /**
   * 获取当前订单信息
   */
  getOrderData() {
    let _this = this,
      options = _this.data.options;
    // 获取订单信息回调方法
    let callback = result => {
      let resData = result.data;
      // 请求错误
      if (result.code !== 1) {
        App.showError(result.msg);
        return false;
      }
      // 显示错误信息
      if (resData.has_error) {
        App.showError(resData.error_msg);
      }

      let data = {};
      // 判断是否为虚拟商品
      data.isVirtualGoods = resData.is_virtual || false;
      // 当前选择的配送方式
      data.curDelivery = resData.delivery;
      // 如果只有一种配送方式则不显示选项卡
      data.isShowTab = resData.setting.delivery.length > 1;
      // 上门自提联系信息
      if (_this.data.linkman === '') {
        data.linkman = resData.last_extract.linkman;
      }
      if (_this.data.phone === '') {
        data.phone = resData.last_extract.phone;
      }
      // 设置页面数据
      _this.setData(Object.assign({}, resData, data));
      // 快递配送时加载本地外送服务列表
      if (resData.delivery == DeliveryTypeEnum.EXPRESS.value && resData.address && resData.address.address_id) {
        _this.loadDeliveryList();
      }
      // 门店配送(送水)：加载候选门店列表，并校验已选门店是否仍在候选内
      if (resData.delivery == DeliveryTypeEnum.SHOP_DELIVERY.value) {
        let shopList = resData.delivery_shop_list || [];
        let selShopId = _this.data.selectedShopId || 0;
        // 已选门店不在候选内（或地址变化）则清空重选
        if (selShopId && !shopList.some(s => s.shop_id == selShopId)) {
          selShopId = 0;
        }
        _this.setData({
          deliveryShopList: shopList,
          selectedShopId: selShopId
        });
      }
      wx.hideLoading();
    };

    wx.showLoading({
      title: '加载中...',
    });

    // 请求的参数
    let params = {
      delivery: _this.data.curDelivery || 0,
      delivery_id: _this.data.selectDeliveryId || 0,
      shop_id: _this.data.selectedShopId || 0,
      coupon_id: _this.data.selectCouponId || 0,
      is_use_points: _this.data.isUsePoints ? 1 : 0,
      use_water_ticket: _this.data.useWaterTicket ? 1 : 0,
    };

    // 立即购买
    if (options.order_type === 'buyNow') {
      App._get('order/buyNow', Object.assign({}, params, {
        goods_id: options.goods_id,
        goods_num: options.goods_num,
        goods_sku_id: options.goods_sku_id,
      }), result => {
        callback(result);
      });
    }

    // 砍价活动结算
    else if (options.order_type === 'bargain') {
      App._get('bargain.order/checkout', Object.assign({}, params, {
        task_id: options.task_id,
        goods_sku_id: options.goods_sku_id,
      }), result => {
        callback(result);
      });
    }

    // 秒杀活动结算
    else if (options.order_type === 'sharp') {
      App._get('sharp.order/checkout', Object.assign({}, params, {
        active_time_id: options.active_time_id,
        sharp_goods_id: options.sharp_goods_id,
        goods_sku_id: options.goods_sku_id,
        goods_num: options.goods_num,
      }), result => {
        callback(result);
      });
    }

    // 购物车结算
    else if (options.order_type === 'cart') {
      App._get('order/cart', Object.assign({}, params, {
        cart_ids: options.cart_ids || 0,
      }), result => {
        callback(result);
      });
    }
  },

  /**
   * 切换配送方式
   */
  onSwichDelivery(e) {
    // 设置当前配送方式
    let _this = this;
    // 切换配送方式时清空已选门店/外送服务，避免残留到新配送方式
    _this.setData({
      curDelivery: e.currentTarget.dataset.current,
      selectedShopId: 0,
      selectDeliveryId: 0,
    });
    // 重新获取订单信息
    _this.getOrderData();
  },

  /**
   * 加载本地外送服务列表(快递配送时)
   */
  loadDeliveryList() {
    let _this = this;
    let addr = _this.data.address;
    if (_this.data.curDelivery != DeliveryTypeEnum.EXPRESS.value || !addr || !addr.address_id) {
      return;
    }
    delivery.lists(addr.country || 'MY').then(function(res) {
      let list = res.list || [];
      let selectId = _this.data.selectDeliveryId;
      // 保持已选，否则默认选中第一个
      if (!list.some(function(d) { return d.delivery_id == selectId; })) {
        selectId = list.length ? list[0].delivery_id : 0;
      }
      _this.setData({
        deliveryList: list,
        selectDeliveryId: selectId
      });
    }).catch(function() {});
  },

  /**
   * 选择外送服务
   */
  onSelectDelivery(e) {
    let _this = this;
    _this.setData({
      selectDeliveryId: e.currentTarget.dataset.id
    });
    _this.getOrderData();
  },

  /**
   * 快递配送：选择收货地址
   */
  onSelectAddress() {
    let _this = this;
    // 允许刷新
    _this.setData({
      notRefresh: false
    });
    // 跳转到选择自提点
    wx.navigateTo({
      url: '../address/' + (_this.data.exist_address ? 'index?from=flow' : 'create')
    });
  },

  /**
   * 门店配送(送水)：选择派送门店
   */
  onSelectDeliveryShop(e) {
    let _this = this,
      shopId = e.currentTarget.dataset.id;
    if (shopId == _this.data.selectedShopId) {
      return;
    }
    // 重新获取订单信息（后端会校验该门店是否可配送到收货地址并计算配送费/押金）
    _this.setData({
      selectedShopId: shopId,
    }, () => {
      _this.getOrderData();
    });
  },

  /**
   * 切换是否使用水票抵扣(送水：门店配送)
   */
  onToggleWaterTicket() {
    this.setData({
      useWaterTicket: !this.data.useWaterTicket,
    }, () => {
      this.getOrderData();
    });
  },

  /**
   * 上门自提：选择自提点
   */
  onSelectExtractPoint() {
    let _this = this,
      selectedId = _this.data.selectedShopId;
    // 允许刷新
    _this.setData({
      notRefresh: false
    });
    // 跳转到选择自提点
    wx.navigateTo({
      url: '../_select/extract_point/index?selected_id=' + selectedId
    });
  },

  /**
   * 跳转到商品详情页
   */
  onTargetGoods(e) {
    wx.navigateTo({
      url: `../goods/index?goods_id=${e.currentTarget.dataset.id}`,
    })
  },

  /**
   * 订单提交
   */
  onSubmitOrder() {
    let _this = this,
      options = _this.data.options;

    if (_this.data.disabled) {
      return false;
    }

    // 表单验证
    if (!_this._onVerify()) {
      return false;
    }

    // 按钮禁用, 防止二次提交
    _this.data.disabled = true;


    let url = '';

    // 表单提交的数据
    let postData = {
      delivery: _this.data.isVirtualGoods ? 0 : _this.data.curDelivery, // 虚拟商品delivery传0
      delivery_id: _this.data.isVirtualGoods ? 0 : _this.data.selectDeliveryId,
      pay_type: _this.data.curPayType,
      shop_id: _this.data.isVirtualGoods ? 0 : (_this.data.selectedShopId || 0),
      linkman: _this.data.isVirtualGoods ? '' : _this.data.linkman,
      phone: _this.data.isVirtualGoods ? '' : _this.data.phone,
      coupon_id: _this.data.selectCouponId || 0,
      is_use_points: _this.data.isUsePoints ? 1 : 0,
      use_water_ticket: _this.data.useWaterTicket ? 1 : 0,
      remark: _this.data.remark || '',
    };

    // 创建订单-立即购买
    if (options.order_type === 'buyNow') {
      url = 'order/buyNow';
      postData = Object.assign(postData, {
        goods_id: options.goods_id,
        goods_num: options.goods_num,
        goods_sku_id: options.goods_sku_id,
      });
    }

    // 创建订单-购物车结算
    if (options.order_type === 'cart') {
      url = 'order/cart';
      postData = Object.assign(postData, {
        cart_ids: options.cart_ids || 0,
      });
    }

    // 创建订单-砍价活动
    if (options.order_type === 'bargain') {
      url = 'bargain.order/checkout';
      postData = Object.assign(postData, {
        task_id: options.task_id,
        goods_sku_id: options.goods_sku_id,
      });
    }

    // 创建订单-秒杀商品
    if (options.order_type === 'sharp') {
      url = 'sharp.order/checkout';
      postData = Object.assign(postData, {
        active_time_id: options.active_time_id,
        sharp_goods_id: options.sharp_goods_id,
        goods_sku_id: options.goods_sku_id,
        goods_num: options.goods_num,
      });
    }

    // 提交到后端
    const onCommitCallback = () => {
      // 显示loading
      wx.showLoading({
        title: '正在处理...'
      });
      // 订单提交
      App._post_form(url, postData, result => {
        _this._onSubmitCallback(result);
      }, result => {}, () => {
        wx.hideLoading();
        // 解除按钮禁用
        _this.data.disabled = false;
      });
      // 不允许刷新
      _this.setData({
        notRefresh: true
      });
    };

    // 请求用户订阅消息
    _this._onRequestSubscribeMessage(onCommitCallback);
  },

  /**
   * 请求用户订阅消息
   */
  _onRequestSubscribeMessage(onCommitCallback) {
    let _this = this,
      tmplIds = _this.data.setting.order_submsg;
    if (tmplIds.length == 0) {
      onCommitCallback();
      return;
    }
    wx.requestSubscribeMessage({
      tmplIds,
      success(res) {},
      fail(res) {},
      complete(res) {
        onCommitCallback();
      },
    });
  },

  /**
   * 订单提交成功后回调
   */
  _onSubmitCallback(result) {
    let _this = this;
    // 订单创建成功后回调--微信支付
    if (result.code === -10) {
      App.showError(result.msg, () => {
        _this.redirectToOrderIndex();
      });
      return false;
    }
    // 实付 0 元订单(水票全额抵扣等)：下单即已支付，无需调起支付
    if (result.data.paid == 1) {
      App.showSuccess((result.msg && result.msg.success) || '支付成功', () => {
        _this.redirectToOrderIndex();
      });
      return false;
    }
    // 好友代付：跳转订单详情页, 由用户分享给好友代付
    if (result.data.pay_type == PayTypeEnum.FRIEND.value) {
      wx.redirectTo({
        url: `../order/detail?order_id=${result.data.order_id}`,
      });
      return false;
    }
    // 发起微信支付
    if (result.data.pay_type == PayTypeEnum.WECHAT.value) {
      App.wxPayment({
        payment: result.data.payment,
        success: res => {
          _this.redirectToOrderIndex();
        },
        fail: res => {
          App.showError(result.msg.error, () => {
            _this.redirectToOrderIndex();
          });
        },
      });
    }
    // 余额支付
    if (result.data.pay_type == PayTypeEnum.BALANCE.value) {
      App.showSuccess(result.msg.success, () => {
        _this.redirectToOrderIndex();
      });
    }
  },


  /**
   * 表单验证
   */
  _onVerify() {
    let _this = this;
    if (_this.data.has_error) {
      App.showError(_this.data.error_msg);
      return false;
    }
    // 虚拟商品无需验证配送信息
    if (_this.data.isVirtualGoods) {
      return true;
    }
    // 验证自提填写的联系方式
    if (_this.data.curDelivery == DeliveryTypeEnum.EXTRACT.value) {
      _this.setData({
        linkman: _this.data.linkman.trim(),
        phone: _this.data.phone.trim(),
      });
      if (_this.data.selectedShopId <= 0) {
        App.showError('请选择自提的门店');
        return false;
      }
      if (Verify.isEmpty(_this.data.linkman)) {
        App.showError('请填写自提联系人');
        return false;
      }
      if (Verify.isEmpty(_this.data.phone)) {
        App.showError('请填写自提联系电话');
        return false;
      }
      if (!Verify.isPhone(_this.data.phone)) {
        App.showError('请输入正确的联系电话');
        return false;
      }
    }
    // 验证门店配送(送水)已选择派送门店
    if (_this.data.curDelivery == DeliveryTypeEnum.SHOP_DELIVERY.value) {
      if (_this.data.selectedShopId <= 0) {
        App.showError('请选择派送门店');
        return false;
      }
    }
    return true;
  },

  /**
   * 买家留言
   */
  bindRemark(e) {
    let _this = this;
    _this.setData({
      remark: e.detail.value
    })
  },

  /**
   * 选择优惠券(弹出/隐藏)
   */
  onTogglePopupCoupon() {
    let _this = this;
    if (_this.data.coupon_list.length > 0) {
      _this.setData({
        showBottomPopup: !_this.data.showBottomPopup
      });
    }
  },

  /**
   * 选择优惠券
   */
  onSelectCoupon(e) {
    const app = this;
    // 当前选择的优惠券
    const index = e.currentTarget.dataset.index
    const couponItem = app.data.coupon_list[index]
    // 判断是否在适用范围
    if (!couponItem['is_apply']) {
      Toast({
        selector: '#zan-toast',
        message: couponItem.not_apply_info,
        timeout: 1800
      });
      return
    }
    // 记录选中的优惠券id
    app.setData({
      selectCouponId: couponItem.user_coupon_id
    });
    // 重新获取订单信息
    app.getOrderData();
    // 隐藏优惠券弹层
    app.onTogglePopupCoupon();
  },

  /**
   * 不使用优惠券
   */
  onNotUseCoupon() {
    let _this = this;
    _this.setData({
      selectCouponId: 0
    });
    // 重新获取订单信息
    _this.getOrderData();
    // 隐藏优惠券弹层
    _this.onTogglePopupCoupon();
  },

  /**
   * 选择支付方式
   */
  onSelectPayType(e) {
    let _this = this;
    // 设置当前支付方式
    _this.setData({
      curPayType: e.currentTarget.dataset.value
    });
  },

  /**
   * 跳转到未付款订单
   */
  redirectToOrderIndex() {
    wx.redirectTo({
      url: '../order/index',
    });
  },

  /**
   * input绑定：联系人
   */
  onInputLinkman(e) {
    let _this = this;
    _this.setData({
      linkman: e.detail.value
    });
  },

  /**
   * input绑定：联系电话
   */
  onInputPhone(e) {
    let _this = this;
    _this.setData({
      phone: e.detail.value
    });
  },

  /**
   * 选择积分抵扣
   */
  onTriggerPoints({
    detail
  }) {
    let _this = this;
    _this.setData({
      isUsePoints: detail
    });
    // 重新获取订单信息
    _this.getOrderData();
  },

  /**
   * 显示积分说明
   */
  onShowPoints(e) {
    let _this = this;
    // 显示dialog
    let setting = _this.data.setting;
    Dialog({
      title: `${setting.points_name}说明`,
      message: setting.points_describe,
      selector: '#zan-base-dialog',
      isScroll: true, // 滚动
      buttons: [{
        text: '关闭',
        color: 'red',
        type: 'cash'
      }]
    });
  },

});