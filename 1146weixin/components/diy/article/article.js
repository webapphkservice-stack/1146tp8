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
    // itemStyle: Object,
    params: Object,
    dataList: Object
  },

  /**
   * 数据监听
   */
  observers: {
    'dataList': function (dataList) {
      this.layoutWaterfall(dataList || []);
    }
  },

  /**
   * 组件的方法列表
   * 更新属性和数据的方法与更新页面数据的方法类似
   */
  methods: {

    /**
     * 双列瀑布流分配（按封面图片真实高度错落）
     */
    layoutWaterfall(list) {
      const COL_W = 330;     // 单列宽(rpx)，用于估算卡片高度
      const CARD_FIXED = 170; // 文字等固定高度(rpx)
      const _this = this;
      let left = [];
      let right = [];
      let lh = 0;
      let rh = 0;
      list.forEach(item => {
        const img = item.image || {};
        let ratio = 0.75;
        if (img.width && img.height && img.width > 0) {
          ratio = img.height / img.width;
        } else if (img.file_path) {
          // 实时获取一次，失败则用默认比例
          wx.getImageInfo({
            src: img.file_path,
            success: r => { item.__ratio = r.height / r.width; },
            fail: () => {}
          });
        }
        const cardH = COL_W * ratio + CARD_FIXED;
        if (lh <= rh) {
          left.push(item);
          lh += cardH;
        } else {
          right.push(item);
          rh += cardH;
        }
      });
      _this.setData({ leftList: left, rightList: right });
    },

    /**
     * 跳转文章详情页
     */
    onTargetDetail(e) {
      wx.navigateTo({
        url: '/pages/article/detail/index?article_id=' + e.currentTarget.dataset.id
      });
    },

  }

})