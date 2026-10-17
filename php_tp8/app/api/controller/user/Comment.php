<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\Comment as CommentModel;
use app\api\model\Order as OrderModel;
use app\api\model\OrderGoods as OrderGoodsModel;

/**
 * 订单评价（移植自 php_my api/controller/user/Comment）
 * RN 调用点：commentApi.{list,submit,delete,append} -> user.comment/{order,order,delete,append}
 */
class Comment extends Controller
{
    /**
     * 评价页（待评价商品列表 / 提交评价）
     */
    public function order()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id');
        $order = (new OrderModel)->getUserOrderDetail($orderId, $user['user_id']);
        if ($order === false) {
            return $this->renderError((new OrderModel)->getError() ?: '订单不存在');
        }
        $model = new CommentModel;
        if ($model->checkOrderAllowComment($order) === false) {
            return $this->renderError($model->getError());
        }
        $goodsList = OrderGoodsModel::getNotCommentGoodsList($orderId);
        if ($this->request->isPost()) {
            $post = $this->postData();
            $res = $model->addForOrder($order, $goodsList, $post['formData'] ?? '');
            if ($res === false) {
                return $this->renderError($model->getError());
            }
            return $this->renderSuccess('评价成功');
        }
        return $this->renderSuccess(compact('order', 'goodsList'));
    }

    /**
     * 我的评价列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $model = new CommentModel;
        $scoreType = (int)$this->request->param('scoreType', -1);
        $page = (int)$this->request->param('page', 1);
        $sort = $this->request->param('sort', 'desc');
        $list = $model->getUserCommentList($user['user_id'], $scoreType, $page, $sort);
        $total = $model->getUserCommentTotal($user['user_id']);
        return $this->renderSuccess(compact('list', 'total'));
    }

    /**
     * 删除我的评价（软删除）
     */
    public function delete()
    {
        $user = $this->getUser(true);
        $commentId = (int)$this->request->param('comment_id');
        (new CommentModel)->softDelete($commentId, $user['user_id']);
        return $this->renderSuccess('删除成功');
    }

    /**
     * 追评
     */
    public function append()
    {
        $user = $this->getUser(true);
        $commentId = (int)$this->request->param('comment_id');
        $content = $this->request->param('content', '');
        $model = new CommentModel;
        if ($model->appendComment($commentId, $user['user_id'], $content) === false) {
            return $this->renderError($model->getError());
        }
        return $this->renderSuccess('追评成功');
    }
}
