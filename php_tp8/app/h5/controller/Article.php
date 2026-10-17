<?php
namespace app\h5\controller;

use app\api\model\Article as ArticleModel;
use think\facade\Db;

/**
 * H5 - 文章资讯（种草社区）
 */
class Article extends Controller
{
    /**
     * 文章分类 + 列表
     */
    public function index()
    {
        $categoryId = (int)$this->request->param('category_id', 0);
        $categoryList = Db::name('article_category')
            ->where('wxapp_id', 10001)
            ->order('sort', 'asc')
            ->order('category_id', 'desc')
            ->select()
            ->toArray();
        $list = ArticleModel::getList($categoryId, 1);
        return $this->fetch('article/index', [
            'categoryList' => $categoryList,
            'list'         => $list['data'],  // getList 返回分页结构 [data=>[], total=>N, ...]
            'activeCid'    => $categoryId,
            'storeName'    => $this->storeName(),
        ]);
    }

    /**
     * 文章详情
     */
    public function detail()
    {
        $articleId = (int)$this->request->param('article_id', 0);
        $detail = ArticleModel::detail($articleId);
        if (empty($detail)) {
            return $this->fetch('article/detail', ['detail' => null, 'storeName' => $this->storeName()]);
        }
        // 阅读量 +1（字段名：actual_views）
        Db::name('article')->where('article_id', $articleId)->inc('actual_views', 1)->update();
        return $this->fetch('article/detail', [
            'detail'    => $detail,
            'storeName' => $this->storeName(),
        ]);
    }
}
