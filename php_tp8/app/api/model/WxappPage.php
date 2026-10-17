<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 页面装修模型（首页 diy）
 *
 * 移植自 php_my api/model/WxappPage，采用「Db 直查」轻量实现（与第 2 批商品层一致），
 * 不引入 TP5 的 Goods / Article 模型及其关联。
 *
 * 首页装修的数据结构：wxapp_page.page_data 是 JSON，含
 *   - page  ：顶部导航（标题栏样式等）
 *   - items ：组件列表（混合键数组，键为数字序号）
 * 其中大多数组件（search/banner/navBar/notice/imageSingle/window）的数据已静态保存在
 * page_data 内，无需后端处理；只有少数「动态组件」需要后端实时取数：
 *   - goods    商品
 *   - article  文章
 *   - coupon   优惠券
 *   - special  头条快报
 *   - shop     门店
 *   - sharingGoods / bargainGoods / sharpGoods  拼团 / 砍价 / 秒杀商品
 *
 * 当前 wxapp_id=10001 的首页实际只配置了 goods 与 article 两个动态组件，
 * 营销相关的三个组件（拼团/砍价/秒杀）在营销模块迁移后再补齐（见下方 switch 的 TODO）。
 */
class WxappPage extends BaseModel
{
    protected $name = 'wxapp_page';

    /**
     * 获取页面数据（首页装修）
     *
     * @param array|false $user   当前用户（可为 false，未登录时商品价格按原价）
     * @param int         $pageId 页面 id，0 表示取首页
     * @return array{page:array, items:array, icon:array}
     */
    public static function getPageData($user, $pageId = 0)
    {
        $detail = $pageId > 0 ? self::detailById($pageId) : self::getHomePage();
        if (empty($detail)) {
            return ['page' => [], 'items' => [], 'icon' => self::getIconData()];
        }
        $pageData = is_array($detail['page_data'])
            ? $detail['page_data']
            : (json_decode((string)$detail['page_data'], true) ?: []);

        $items = $pageData['items'] ?? [];
        $page  = $pageData['page'] ?? [];
        // page 一并放进 items，供前端统一按数组处理（渲染时会剔除 type=page 的项）
        if ($page) {
            $items['page'] = $page;
        }

        foreach ($items as $key => $item) {
            switch ($item['type'] ?? '') {
                case 'window':
                    $items[$key]['data'] = array_values($item['data'] ?? []);
                    break;
                case 'goods':
                    $items[$key]['data'] = self::getGoodsList($item);
                    break;
                case 'article':
                    $items[$key]['data'] = self::getArticleList($item);
                    break;
                // TODO：营销模块（秒杀 / 拼团 / 砍价）迁移后，在此补充
                //       sharpGoods / sharingGoods / bargainGoods 三个组件的取数。
                //       当前首页装修未配置这些组件，故暂不实现，不影响首页展示。
                default:
                    break;
            }
        }

        return [
            'page'  => $page,
            'items' => $items,
            'icon'  => self::getIconData(),
        ];
    }

    /**
     * 页面详情（指定 page_id）
     */
    private static function detailById($pageId)
    {
        return Db::name('wxapp_page')
            ->where('page_id', (int)$pageId)
            ->where('is_delete', 0)
            ->find();
    }

    /**
     * 首页（page_type=10）
     */
    private static function getHomePage()
    {
        return Db::name('wxapp_page')
            ->where('page_type', 10)
            ->where('is_delete', 0)
            ->order('page_id', 'asc')
            ->find();
    }

    /**
     * 商品组件：取商品列表
     *
     * @param array $item 组件配置
     * @return array
     */
    public static function getGoodsList($item)
    {
        $params = $item['params'] ?? [];
        $auto   = $params['auto'] ?? [];
        $source = $params['source'] ?? 'auto';
        $categoryId = (int)($auto['category'] ?? 0);
        $showNum    = (int)($auto['showNum'] ?? 10);
        $sortType   = $auto['goodsSort'] ?? 'all';

        $query = Db::name('goods')
            ->where('goods_status', 10)
            ->where('is_delete', 0);

        if ($source === 'choice') {
            // 手动选品
            $goodsIds = array_filter(array_map('intval', array_column($item['data'] ?? [], 'goods_id')));
            if (empty($goodsIds)) {
                return [];
            }
            $query->whereIn('goods_id', $goodsIds);
        } elseif ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        // 排序：销量 / 价格 / 综合（默认按排序号 + 最新）
        if ($sortType === 'sales') {
            $query->order('sales_actual', 'desc');
        } elseif ($sortType === 'price') {
            $query->order('goods_id', 'asc'); // 价格存于 sku，此处退化为 id 序
        } else {
            $query->order('goods_sort', 'asc')->order('goods_id', 'desc');
        }

        $list = $query->limit(max(1, $showNum))->select()->toArray();

        $data = [];
        foreach ($list as $goods) {
            $imageId = Db::name('goods_image')->where('goods_id', $goods['goods_id'])
                ->order('id', 'asc')->value('image_id');
            $sku = Db::name('goods_sku')->where('goods_id', $goods['goods_id'])
                ->order('goods_sku_id', 'asc')->find();
            $image = BaseModel::getFileUrl($imageId);
            $data[] = [
                'goods_id'     => $goods['goods_id'],
                'goods_name'   => $goods['goods_name'],
                'selling_point'=> $goods['selling_point'] ?? '',
                'image'        => $image,
                'goods_image'  => $image,
                'goods_price'  => $sku['goods_price'] ?? '0.00',
                'line_price'   => $sku['line_price'] ?? '0.00',
                'goods_sales'  => (int)($goods['sales_actual'] ?? 0),
            ];
        }
        return $data;
    }

    /**
     * 文章组件：取文章列表
     *
     * RN 侧 Article 组件读取字段：article_id / article_title / title /
     * image / images[] / show_views / summary
     */
    public static function getArticleList($item)
    {
        $auto = $item['params']['auto'] ?? [];
        $categoryId = (int)($auto['category'] ?? 0);
        $showNum    = (int)($auto['showNum'] ?? 10);

        // 注意：文章表的状态约定与商品表不同——
        //   yoshop_goods.goods_status = 10 表示上架；
        //   yoshop_article.article_status = 1 表示显示（对齐 php_my Article::getList）。
        // 曾误用 10 过滤文章，导致首页文章组件始终为空。
        $query = Db::name('article')
            ->where('article_status', 1)
            ->where('is_delete', 0);
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }
        $list = $query->order('article_sort', 'asc')
            ->order('article_id', 'desc')
            ->limit(max(1, $showNum))
            ->select()->toArray();

        $data = [];
        foreach ($list as $article) {
            $images = [];
            if (!empty($article['image_ids'])) {
                $ids = is_array($article['image_ids'])
                    ? $article['image_ids']
                    : explode(',', (string)$article['image_ids']);
                foreach ($ids as $id) {
                    $url = BaseModel::getFileUrl((int)$id);
                    if ($url !== '') {
                        $images[] = ['file_path' => $url];
                    }
                }
            }
            $data[] = [
                'article_id'    => $article['article_id'],
                'article_title' => $article['article_title'],
                // RN 的 Article 组件同时兼容 article_title 与 title，两个都给，避免改前端
                'title'         => $article['article_title'],
                'summary'       => $article['summary'] ?? '',
                'image'         => BaseModel::getFileUrl($article['image_id']),
                'images'        => $images,
                'show_views'    => (int)($article['actual_views'] ?? 0) + (int)($article['virtual_views'] ?? 0),
                'create_time'   => $article['create_time'],
            ];
        }
        return $data;
    }

    /**
     * 小程序图标配置（登录页 logo 等）
     *
     * 返回值统一补成完整 URL：RN 侧 coverUrl() 对字符串直接透传，不做域名拼接，
     * 因此后端必须返回可直接加载的地址。
     */
    public static function getIconData()
    {
        $row = Db::name('wxapp_icon')->where('wxapp_id', self::getWxappId())->find();
        if (empty($row)) {
            return ['user_icon' => '', 'home_icon' => '', 'logo' => ''];
        }
        $format = function ($value) {
            if (empty($value)) {
                return '';
            }
            return preg_match('/^https?:\/\//i', $value) ? $value : base_url() . ltrim($value, '/');
        };
        return [
            'user_icon' => $format($row['user_icon'] ?? ''),
            'home_icon' => $format($row['home_icon'] ?? ''),
            'logo'      => $format($row['logo'] ?? ''),
        ];
    }
}
