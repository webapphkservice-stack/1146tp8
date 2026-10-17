const app = getApp();

Page({

  /**
   * 页面的初始数据
   */
  data: {
    articleId: 0,
    detail: {},
    // 评论
    commentList: [],
    commentPage: 1,
    commentNoMore: false,
    // 互动状态
    isLike: false,
    likeNum: 0,
    commentNum: 0,
    // 评论输入弹层
    commentShow: false,
    commentContent: '',
    // 封面图 swiper 高度（rpx，按图片实际比例计算，由图片 load 事件精确修正）
    coverH: 420
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad(options) {
    this.setData({ articleId: parseInt(options.article_id) });
    this.getArticleDetail();
  },

  /**
   * 获取文章详情
   */
  getArticleDetail() {
    let _this = this;
    app._get('article/detail', {
      article_id: _this.data.articleId
    }, result => {
      let detail = result.data.detail;
      if (detail.article_content) {
        detail.article_content = _this.fixRichImages(detail.article_content);
      }
      // 后端可能返回 images（多图）或 image（旧单图），统一兜底
      let images = detail.images && detail.images.length
        ? detail.images
        : (detail.image ? [detail.image] : []);
      detail.images = images;
      // 占位比例：后端未返回宽高时使用，图片 load 后会精确修正
      let coverRatios = images.map(img => {
        return (img.width && img.height && img.width > 0) ? (img.height / img.width) : 0.75;
      });
      let firstRatio = coverRatios[0] || 0.75;
      _this.data.coverRatios = coverRatios;
      _this.data._coverRatio = firstRatio;
      _this.data._current = 0;
      // 先用占位高度渲染（封面全宽，宽 = 屏幕宽），图片加载完成时由 onCoverLoad 精确修正
      let coverW = wx.getSystemInfoSync().windowWidth * 2;
      let coverH = Math.round(coverW * firstRatio);
      let commentList = (result.data.commentList.data || []).map(c => {
        c.time_text = _this.formatTime(c.create_time);
        c.avatar = (c.nickname || '游').substr(0, 1);
        return c;
      });
      _this.setData({
        detail: detail,
        coverH: coverH,
        coverRatios: coverRatios,
        commentList: commentList,
        isLike: detail.is_like,
        likeNum: detail.like_num,
        commentNum: detail.comment_num,
        commentPage: 1,
        commentNoMore: result.data.commentList.last_page <= 1
      });
    });
  },

  /**
   * 封面图片加载完成：用图片真实宽高精确计算 swiper 高度。
   * 封面现已全宽贴边，渲染宽度 = windowWidth(px) → rpx(*2)。
   * 直接用窗口宽度 × 图片真实比例得到精确高度，避免二次测量时机不准导致偏高。
   * 设最大高度上限，避免超长图把页面撑得过长；高度不足时 swiper-item 内垂直居中。
   */
  onCoverLoad(e) {
    const _this = this;
    const img = e.detail;
    if (!img || !img.width || !img.height) return;
    const ratio = img.height / img.width;
    const screenWpx = wx.getSystemInfoSync().windowWidth; // 屏幕逻辑宽(px)
    let h = Math.round(screenWpx * 2 * ratio); // 全宽时的精确高度(rpx)
    // 最大高度限制：屏幕高度的一半（rpx），防止超长图过高
    const maxH = Math.round(screenWpx * 2 * 0.75);
    if (h > maxH) h = maxH;
    if (h > 0) {
      const cur = e.currentTarget.dataset.index || 0;
      _this.data._coverRatio = ratio;
      _this.data.coverRatios[cur] = ratio;
      // 仅当加载的是当前显示的图片时才更新 swiper 高度
      if (cur === (_this.data._current || 0)) {
        _this.setData({ coverH: h });
      }
    }
  },

  /**
   * 点赞 / 取消点赞
   */
  onLike() {
    let _this = this;
    app._post_form('article/like', {
      article_id: _this.data.articleId
    }, result => {
      _this.setData({
        isLike: result.data.liked,
        likeNum: result.data.count
      });
    });
  },

  /**
   * 打开评论输入
   */
  showComment() {
    this.setData({ commentShow: true });
  },

  /**
   * 关闭评论输入
   */
  hideComment() {
    this.setData({ commentShow: false, commentContent: '' });
  },

  /**
   * 评论输入
   */
  onCommentInput(e) {
    this.setData({ commentContent: e.detail.value });
  },

  /**
   * 提交评论
   */
  postComment() {
    let _this = this;
    let content = _this.data.commentContent.trim();
    if (!content) {
      app.showError('评论内容不能为空');
      return;
    }
    app._post_form('article/comment', {
      article_id: _this.data.articleId,
      content: content
    }, result => {
      let newComment = {
        comment_id: result.data.comment_id,
        nickname: '我',
        content: content,
        create_time: Math.floor(Date.now() / 1000)
      };
      newComment.time_text = _this.formatTime(newComment.create_time);
      newComment.avatar = (newComment.nickname || '游').substr(0, 1);
      let list = [newComment].concat(_this.data.commentList);
      _this.setData({
        commentList: list,
        commentNum: result.data.comment_num,
        commentShow: false,
        commentContent: ''
      });
      app.showSuccess('评论成功');
    });
  },

  /**
   * 加载更多评论
   */
  loadMoreComment() {
    let _this = this;
    if (_this.data.commentNoMore) return;
    let nextPage = _this.data.commentPage + 1;
    app._get('article/commentList', {
      article_id: _this.data.articleId,
      page: nextPage
    }, result => {
      let list = (result.data.list.data || []).map(c => {
        c.time_text = _this.formatTime(c.create_time);
        c.avatar = (c.nickname || '游').substr(0, 1);
        return c;
      });
      _this.setData({
        commentList: _this.data.commentList.concat(list),
        commentPage: nextPage,
        commentNoMore: result.data.list.last_page <= nextPage
      });
    });
  },

  /**
   * 上拉加载更多评论
   */
  onReachBottom() {
    this.loadMoreComment();
  },

  /**
   * 分享
   */
  onShareAppMessage() {
    return {
      title: this.data.detail.article_title || '种草详情',
      path: '/pages/article/detail/index?article_id=' + this.data.articleId
    };
  },

  goTop() {
    wx.pageScrollTo({ scrollTop: 0 });
  },

  noop() {},

  /**
   * 预览封面大图
   */
  previewCover(e) {
    const index = e.currentTarget.dataset.index;
    const urls = (this.data.detail.images || []).map(i => i.file_path);
    if (urls.length === 0) return;
    wx.previewImage({ current: urls[index], urls: urls });
  },

  /**
   * 封面滑动切换：swiper 高度跟随当前图片比例，保证每张图按自身比例满宽显示。
   * 若图片已加载过则用已记录的真实比例直接算高度；未加载则等其 load 事件修正。
   */
  onCoverChange(e) {
    const cur = e.detail.current;
    this.data._current = cur;
    const ratios = this.data.coverRatios || [];
    const ratio = ratios[cur] || this.data._coverRatio || 0.75;
    this.data._coverRatio = ratio;
    const screenWpx = wx.getSystemInfoSync().windowWidth;
    const maxH = Math.round(screenWpx * 2 * 0.75);
    let h = Math.round(screenWpx * 2 * ratio);
    if (h > maxH) h = maxH;
    this.setData({ coverH: h });
  },

  /**
   * 限制富文本内图片：rich-text 默认不继承外部样式，必须在 HTML 内联注入宽度控制，
   * 避免图片超出屏幕宽度。
   */
  fixRichImages(html) {
    if (!html) return html;
    // 给每个 <img> 强制注入内联样式：宽度100%、高度自适应，并去除原有宽高属性/style 中的宽高
    html = html.replace(/<img\b([^>]*?)\/?>/gi, function (m, attrs) {
      // 去掉已有 width/height 属性（含 px / % 值）
      attrs = attrs.replace(/\s(?:width|height)=["']?[0-9]+(?:px|%|rpx)?["']?/gi, '');
      // 去掉已有 style 里的 width/height（避免固定 px 宽度导致溢出屏幕）
      var styleMatch = attrs.match(/\sstyle=(["'])(.*?)\1/i);
      var extra = '';
      if (styleMatch) {
        var cleaned = styleMatch[2]
          .replace(/(^|;)\s*(?:width|height|max-width)\s*:[^;]+;?/gi, '$1')
          .replace(/!important/gi, '')
          .trim();
        attrs = attrs.replace(styleMatch[0], '');
        extra = cleaned ? cleaned + ';' : '';
      }
      // 注意：rich-text 内联 style 不支持 !important，也不支持 rpx 单位，
      // 必须只用百分比/px，否则整个 style 会被忽略导致图片放大溢出屏幕
      return '<img' + attrs + ' style="' + extra + 'width:100%;max-width:100%;height:auto;display:block;vertical-align:top;"' + '>';
    });
    return html;
  },

  /**
   * 相对时间格式化
   */
  formatTime(ts) {
    let diff = Math.floor(Date.now() / 1000) - ts;
    if (diff < 60) return '刚刚';
    if (diff < 3600) return Math.floor(diff / 60) + '分钟前';
    if (diff < 86400) return Math.floor(diff / 3600) + '小时前';
    if (diff < 86400 * 30) return Math.floor(diff / 86400) + '天前';
    let d = new Date(ts * 1000);
    return (d.getMonth() + 1) + '-' + d.getDate();
  }
});
