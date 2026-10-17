let App = getApp();

/**
 * 获取出租车设置（机场列表 + 价格）
 */
function setting() {
  return new Promise(function (resolve, reject) {
    App._get('taxi/setting', {}, function (res) {
      resolve(res.data);
    }, function () {
      reject();
    });
  });
}

/**
 * 提交出租车预约订单
 */
function submit(data) {
  return new Promise(function (resolve, reject) {
    App._post_form('taxi/submit', data, function (res) {
      resolve(res.data);
    }, function (res) {
      reject(res);
    });
  });
}

/**
 * 我的出租车订单
 */
function orders() {
  return new Promise(function (resolve, reject) {
    App._get('taxi/orders', {}, function (res) {
      resolve(res.data.list || []);
    }, function () {
      reject();
    });
  });
}

module.exports = {
  setting: setting,
  submit: submit,
  orders: orders
};
