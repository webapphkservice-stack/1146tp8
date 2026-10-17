<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 文章资讯模型（轻量 Db 实现，移植自 php_my api/model/Article）
 *
 * 首页装修的 article 组件与资讯中心共用本模型的字段格式化逻辑。
 * 注意文章表约定：article_status = 1 表示显示（与商品表的 goods_status = 10 不同）。
 */
class Article extends BaseModel
{
    protected $name = 'article';

    /**
     * 文章列表（分页）
     */
    public static function getList($categoryId = 0, $page = 1, $pageSize = 15)
    {
        $wxappId = self::getWxappId();
        $query = Db::name('article')
            ->where('article_status', 1)
            ->where('is_delete', 0)
            ->where('wxapp_id', $wxappId);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
        $total = $query->count();
        $rows = $query->order('article_sort', 'asc')
            ->order('article_id', 'desc')
            ->page($page, $pageSize)
            ->field('article_id,article_title,summary,category_id,image_id,image_ids,show_type,actual_views,virtual_views,create_time')
            ->select()->toArray();
        $data = array_map([self::class, 'format'], $rows);
        return [
            'data'         => $data,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total / $pageSize)),
            'total'        => $total,
            'per_page'     => $pageSize,
        ];
    }

    /**
     * 文章详情（阅读量 +1）
     */
    public static function detail($articleId)
    {
        if ($articleId <= 0) {
            return [];
        }
        $row = Db::name('article')
            ->where('article_id', $articleId)
            ->where('article_status', 1)
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->find();
        if (empty($row)) {
            return [];
        }
        // 阅读量 +1（自增更新）
        Db::name('article')->where('article_id', $articleId)->inc('actual_views')->update();
        return self::format($row, true);
    }

    /**
     * 统一字段格式化（首页 article 组件与详情页共用）
     * RN 端读取：article_id / article_title / title / summary / image / images[]
     *           / show_views / view_time / article_content / content
     */
    public static function format($a, $withContent = false)
    {
        $images = [];
        if (!empty($a['image_ids'])) {
            $ids = is_array($a['image_ids'])
                ? $a['image_ids']
                : explode(',', (string)$a['image_ids']);
            foreach ($ids as $id) {
                $url = BaseModel::getFileUrl((int)$id);
                if ($url !== '') {
                    $images[] = $url;
                }
            }
        }
        $item = [
            'article_id'    => $a['article_id'],
            'article_title' => $a['article_title'],
            'title'         => $a['article_title'], // RN 详情页同时读 title
            'summary'       => $a['summary'] ?? '',
            'image'         => BaseModel::getFileUrl($a['image_id']),
            'images'        => $images,
            'show_views'    => (int)($a['actual_views'] ?? 0) + (int)($a['virtual_views'] ?? 0),
            'view_time'     => date('Y-m-d', $a['create_time']),
            'create_time'   => $a['create_time'],
        ];
        if ($withContent) {
            $item['article_content'] = $a['article_content'] ?? '';
            $item['content']          = $a['article_content'] ?? ''; // RN 详情页同时读 content
        }
        return $item;
    }
}
