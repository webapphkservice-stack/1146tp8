let App = getApp();

/**
 * 航班查询
 */
function search(param) {
  return new Promise((resolve, reject) => {
    App._post_form('flight/search', param, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

/**
 * 获取后台默认航班（用于查询页直接展示）
 */
function getDefaults() {
  return new Promise((resolve, reject) => {
    App._get('flight/defaults', {}, function(res) {
      resolve(res.data);
    }, function() {
      reject();
    });
  });
}

module.exports = {
  search: search,
  getDefaults: getDefaults
};
