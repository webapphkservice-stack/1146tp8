let App = getApp();

/**
 * 马来西亚本地外送
 */
function lists(country = 'MY') {
  return new Promise((resolve, reject) => {
    App._get('delivery/lists', {
      country: country
    }, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 判断当前用户是否可使用马来西亚本地外送
 */
function checkCountry() {
  return new Promise((resolve, reject) => {
    App._get('delivery/checkCountry', {}, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 我的外送订单列表
 * @param {number} status 不传或 -1 表示全部；0待接单 1配送中 2已完成 3已取消
 */
function orderList(status = -1) {
  return new Promise((resolve, reject) => {
    App._get('delivery_order/lists', {
      status: status
    }, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 确认收货（用户端）
 * @param {number} orderId 外送订单ID
 */
function confirm(orderId) {
  return new Promise((resolve, reject) => {
    App._post_form('delivery_order/confirm', {
      order_id: orderId
    }, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 外送服务详情
 */
function detail(deliveryId) {
  return new Promise((resolve, reject) => {
    App._get('delivery/detail', {
      delivery_id: deliveryId
    }, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 创建外送订单
 * @param {object} data { delivery_id, address_id, remark }
 */
function create(data) {
  return new Promise((resolve, reject) => {
    App._post_form('delivery_order/create', data, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 店铺列表（公开，展示店铺管理配置的店铺信息）
 * 店铺为主：每个店铺内嵌其下启用的外送商品（外送商品是店铺的功能）
 */
function shop() {
  return new Promise((resolve, reject) => {
    App._get('delivery/shop', {}, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 单个店铺及其外送商品列表
 * @param {number} shopId
 */
function goods(shopId) {
  return new Promise((resolve, reject) => {
    App._get('delivery/goods', { shop_id: shopId }, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

module.exports = {
  lists: lists,
  checkCountry: checkCountry,
  orderList: orderList,
  confirm: confirm,
  detail: detail,
  create: create,
  shop: shop,
  goods: goods
};
