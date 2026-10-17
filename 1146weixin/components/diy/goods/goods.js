const App = getApp();

Component({

  options: {
    addGlobalClass: true,
  },

  /**
   * 组件的属性列表
   * 用于组件自定义设置
   */
  properties: {
    itemIndex: String,
    itemStyle: Object,
    params: Object,
    dataList: Object
  },

  /**
   * 组件内部数据
   */
  data: {
    leftList: [],
    rightList: []
  },

  /**
   * 监听属性变化：两列时拆分为左右瀑布流
   */
  observers: {
    'dataList, itemStyle.column': function (dataList, column) {
      let list = (dataList && dataList.data) ? dataList.data : dataList;
      if (!Array.isArray(list)) list = [];
      if (column == 2) {
        const left = [], right = [];
        let lh = 0, rh = 0;
        const COL_W = 365, CARD_FIXED = 150;
        list.forEach(item => {
          const ratio = item.__ratio || 1; // 图片宽高比，默认 1:1
          const cardH = COL_W * ratio + CARD_FIXED;
          if (lh <= rh) { left.push(item); lh += cardH; }
          else { right.push(item); rh += cardH; }
        });
        this.setData({ leftList: left, rightList: right });
      } else {
        this.setData({ leftList: [], rightList: [] });
      }
    }
  },

  /**
   * 组件的方法列表
   * 更新属性和数据的方法与更新页面数据的方法类似
   */
  methods: {

    /**
     * 跳转商品详情页
     */
    _onTargetGoods(e) {
      wx.navigateTo({
        url: '/pages/goods/index?goods_id=' + e.detail.target.dataset.id,
      });
    },
  }

})