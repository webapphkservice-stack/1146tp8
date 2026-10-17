Component({

  options: {

  },

  /**
   * 组件的属性列表
   * 用于组件自定义设置
   */
  properties: {
    itemIndex: String,
    itemStyle: Object,
    params: Object
  },

  observers: {
    // 规范化后端可能返回 null 的字段，避免传给 zan-noticebar 时报类型不兼容
    'params, itemStyle': function (params, itemStyle) {
      const p = params || {};
      const s = itemStyle || {};
      this.setData({
        _icon: p.icon || '',
        _text: p.text || '',
        _textColor: s.textColor || '',
        _background: s.background || '',
        _paddingTop: s.paddingTop || 4
      });
    }
  },

  data: {
    _icon: '',
    _text: '',
    _textColor: '',
    _background: '',
    _paddingTop: 4
  }

})