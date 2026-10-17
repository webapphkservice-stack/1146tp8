<?php
namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\Comment as CommentModel;

/**
 * 商品评价管理控制器（服务端渲染版，移植自 TP5 store/controller/goods/Comment.php）
 */
class Comment extends Controller
{
    /**
     * 评价列表
     */
    public function index()
    {
        $model = new CommentModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 评价详情
     * @param int $comment_id
     */
    public function detail($comment_id)
    {
        $model = CommentModel::detail($comment_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('detail', compact('model'));
        }
        $data = $this->postData('comment');
        $data['comment_id'] = $comment_id;
        if ((new CommentModel)->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('goods.comment/index'));
        }
        return $this->renderError((new CommentModel)->getError() ?: '更新失败');
    }

    /**
     * 删除评价
     * @param int $comment_id
     */
    public function delete($comment_id)
    {
        $model = new CommentModel;
        if (!$model->setDelete($comment_id)) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
