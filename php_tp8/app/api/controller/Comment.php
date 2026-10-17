<?php
namespace app\api\controller;

use app\api\model\Comment as CommentModel;

/**
 * 商品评价控制器
 * Class Comment
 * @package app\api\controller
 */
class Comment extends Controller
{
    /**
     * 商品评价列表
     * @return array
     */
    public function lists()
    {
        $goodsId = (int)$this->request->param('goods_id');
        $scoreType = (int)$this->request->param('scoreType', -1);
        $page = (int)$this->request->param('page', 1);

        $model = new CommentModel;
        $list = $model->getGoodsCommentList($goodsId, $scoreType, $page);
        $total = $model->getTotal($goodsId);
        return $this->renderSuccess(compact('list', 'total'));
    }
}
