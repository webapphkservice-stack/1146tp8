// components/capsuleMenu/capsuleMenu.js
const App = getApp();

Component({
  /**
   * 组件的属性列表
   */
  properties: {
    // 导航栏背景颜色（外层容器）
    backgroundColor: {
      type: String,
      value: 'transparent'
    },
    // 标题栏背景颜色（用于胶囊按钮背景）
    titleBgColor: {
      type: String,
      value: '#ffffff'
    },
    // 胶囊按钮背景颜色（备用）
    capsuleBgColor: {
      type: String,
      value: 'rgba(255, 255, 255, 0.9)'
    },
    // 页面标题
    title: {
      type: String,
      value: ''
    },
    // 标题颜色
    titleColor: {
      type: String,
      value: '#000000'
    },
    // 用户默认图标
    userIcon: {
      type: String,
      value: ''
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    // 导航栏高度
    navHeight: 0,
    // 胶囊按钮距离顶部的距离
    menuTop: 0,
    // 胶囊按钮高度
    menuHeight: 0,
    // 胶囊按钮宽度
    menuWidth: 0,
    // 胶囊按钮距离左边的距离
    menuLeft: 0,
    // 是否登录
    isLogin: false,
    // 用户信息
    userInfo: {},
    // 处理后的图标路径
    userIconUrl: ''
  },

  /**
   * 数据监听器
   */
  observers: {
    'userIcon': function(userIcon) {
      this.setData({
        userIconUrl: userIcon || ''
      });
    }
  },

  /**
   * 组件生命周期
   */
  lifetimes: {
    attached() {
      this.initMenuInfo();
      this.checkLoginStatus();
    }
  },

  /**
   * 页面生命周期
   */
  pageLifetimes: {
    show() {
      // 页面显示时重新检查登录状态
      this.checkLoginStatus();
    }
  },

  /**
   * 组件的方法列表
   */
  methods: {
    /**
     * 初始化胶囊按钮信息
     */
    initMenuInfo() {
      // 获取系统信息
      const systemInfo = wx.getSystemInfoSync();
      // 获取胶囊按钮位置信息
      const menuButtonInfo = wx.getMenuButtonBoundingClientRect();

      // 状态栏高度
      const statusBarHeight = systemInfo.statusBarHeight;
      // 导航栏高度 = 状态栏高度 + 44px（与系统默认导航栏一致）
      const navHeight = statusBarHeight + 44;

      // 计算胶囊按钮相关尺寸
      const menuHeight = menuButtonInfo.height; // 使用系统胶囊按钮的高度
      const menuTop = menuButtonInfo.top - statusBarHeight; // 胶囊按钮距离导航栏顶部的距离
      const menuWidth = menuButtonInfo.width; // 使用系统胶囊按钮的宽度
      const menuLeft = systemInfo.screenWidth - menuButtonInfo.right;

      this.setData({
        navHeight: 44, // 导航栏固定44px（不包含状态栏）
        statusBarHeight,
        menuTop,
        menuHeight,
        menuWidth,
        menuLeft
      });

      // 触发事件通知页面导航栏高度
      this.triggerEvent('navHeight', { navHeight: 44 });
    },

    /**
     * 检查登录状态
     */
    checkLoginStatus() {
      const isLogin = App.checkIsLogin();
      this.setData({
        isLogin
      });
      
      if (isLogin) {
        this.getUserInfo();
      }
    },

    /**
     * 获取用户信息
     */
    getUserInfo() {
      const _this = this;
      App._get('user.index/detail', {}, function (result) {
        if (result && result.data && result.data.userInfo) {
          const userInfo = result.data.userInfo;
          console.log('胶囊菜单获取用户信息:', userInfo);
          _this.setData({
            userInfo: userInfo,
            // 如果用户有自定义头像，使用自定义头像
            userIconUrl: userInfo.avatarUrl || _this.data.userIconUrl
          });
        }
      }, function(err) {
        console.log('获取用户信息失败:', err);
      });
    },

    /**
     * 跳转到个人中心
     */
    onGoUser() {
      wx.switchTab({
        url: '/pages/user/index'
      });
    }
  }
});
