const App = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    // 任务列表
    list: [],
    // 空占位
    notcont: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    // 当前页面参数
    this.data.options = options;
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    // 获取每日任务列表
    this.getTaskList();
  },

  /**
   * 获取每日任务列表
   */
  getTaskList: function() {
    let _this = this;
    App._get('user.task/lists', {}, function(result) {
      _this.setData({
        list: result.data.list,
        notcont: !result.data.list.length
      });
    });
  },

  /**
   * 领取任务奖励
   */
  receive: function(e) {
    let _this = this,
      taskId = e.currentTarget.dataset.taskId;

    App._post_form('user.task/receive', {
      task_id: taskId
    }, function(result) {
      App.showSuccess(result.msg);
      // 刷新列表
      _this.getTaskList();
    });
  }

});
