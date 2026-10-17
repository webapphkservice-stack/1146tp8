<?php
namespace app\api\controller;

use app\api\model\Article as ArticleModel;
use think\facade\Db;

/**
 * 文章资讯控制器（种草社区）
 *
 * RN 调用点：
 *   - ArticleScreen : article/index（分类）
 *   - ArticleScreen : article/lists（列表，分页）
 *   - ArticleDetailScreen : article/detail（详情，阅读量 +1）
 *
 * 三个接口均免登录（文章面向所有访客）。
 */
class Article extends Controller
{
    /**
     * 文章分类列表
     */
    public function index()
    {
        $categoryList = Db::name('article_category')
            ->where('wxapp_id', $this->getWxappId())
            ->order('sort', 'asc')
            ->order('category_id', 'desc')
            ->field('category_id, name')
            ->select()->toArray();
        return $this->renderSuccess(compact('categoryList'));
    }

    /**
     * 文章列表（分页）
     */
    public function lists()
    {
        $categoryId = (int)$this->request->param('category_id', 0);
        $page = (int)$this->request->param('page', 1);
        $list = ArticleModel::getList($categoryId, $page);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 文章详情（含互动数据：点赞数 / 评论数 / 是否点赞 / 评论列表，对齐 TP5）
     */
    public function detail()
    {
        $articleId = (int)$this->request->param('article_id', 0);
        $detail = ArticleModel::detail($articleId);
        if (empty($detail)) {
            return $this->renderError('文章不存在或已下架');
        }
        $wxappId = $this->getWxappId();

        $likeNum = (int)Db::name('article_like')->where('article_id', $articleId)->count();
        $commentNum = (int)Db::name('article_comment')
            ->where('article_id', $articleId)->where('status', 1)->count();

        // 当前用户是否已点赞（游客视为未点赞）
        $isLike = false;
        if ($user = $this->getUser(false)) {
            $clientId = 'u' . $user['user_id'];
            $isLike = (int)Db::name('article_like')
                ->where('article_id', $articleId)->where('client_id', $clientId)->count() > 0;
        }

        $detail['like_num'] = $likeNum;
        $detail['comment_num'] = $commentNum;
        $detail['is_like'] = $isLike;

        // 最新评论列表
        $commentList = $this->queryComments($articleId, $wxappId);
        return $this->renderSuccess(compact('detail', 'commentList'));
    }

    /**
     * 点赞 / 取消点赞（切换，需登录）
     */
    public function like()
    {
        $user = $this->getUser(true);   // 强制登录
        $articleId = (int)$this->request->param('article_id', 0);
        if ($articleId <= 0) {
            return $this->renderError('参数错误');
        }
        $wxappId = $this->getWxappId();
        $clientId = 'u' . $user['user_id'];

        $likedRow = Db::name('article_like')
            ->where('article_id', $articleId)->where('client_id', $clientId)->find();
        if ($likedRow) {
            Db::name('article_like')->where('like_id', $likedRow['like_id'])->delete();
            $liked = false;
        } else {
            Db::name('article_like')->insert([
                'article_id'  => $articleId,
                'user_id'     => $user['user_id'],
                'client_id'   => $clientId,
                'wxapp_id'    => $wxappId,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            $liked = true;
        }
        $count = (int)Db::name('article_like')->where('article_id', $articleId)->count();
        return $this->renderSuccess(['liked' => $liked, 'count' => $count]);
    }

    /**
     * 评论列表（分页）
     */
    public function commentList()
    {
        $articleId = (int)$this->request->param('article_id', 0);
        if ($articleId <= 0) {
            return $this->renderError('参数错误');
        }
        $commentList = $this->queryComments($articleId, $this->getWxappId());
        return $this->renderSuccess(compact('commentList'));
    }

    /**
     * 发表评论（需登录）
     */
    public function comment()
    {
        $user = $this->getUser(true);   // 强制登录
        $articleId = (int)$this->request->param('article_id', 0);
        $content = trim((string)$this->request->param('content', ''));
        if ($articleId <= 0 || $content === '') {
            return $this->renderError('评论内容不能为空');
        }
        $wxappId = $this->getWxappId();
        Db::name('article_comment')->insert([
            'article_id'  => $articleId,
            'user_id'     => $user['user_id'],
            'client_id'   => 'u' . $user['user_id'],
            'nickname'    => ($user['nickName'] ?? '') ?: '微信用户',
            'content'     => mb_substr($content, 0, 500),
            'status'      => 1,
            'wxapp_id'    => $wxappId,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        // 返回最新评论数，便于前端刷新
        $commentNum = (int)Db::name('article_comment')
            ->where('article_id', $articleId)->where('status', 1)->count();
        $commentList = $this->queryComments($articleId, $wxappId);
        return $this->renderSuccess(['comment_num' => $commentNum, 'commentList' => $commentList], '评论成功');
    }

    /**
     * 查询文章评论（status=1，倒序，每页 20）
     */
    private function queryComments($articleId, $wxappId)
    {
        $rows = Db::name('article_comment')
            ->where('article_id', $articleId)
            ->where('status', 1)
            ->where('wxapp_id', $wxappId)
            ->order('comment_id', 'desc')
            ->limit(20)
            ->field('comment_id, article_id, user_id, nickname, content, like_count, status, create_time')
            ->select()->toArray();
        return [
            'data'         => $rows,
            'current_page' => 1,
            'per_page'     => 20,
            'total'        => count($rows),
        ];
    }
}
