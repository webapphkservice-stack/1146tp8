<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商品模型（精简版，对齐 RN GoodsScreen / GoodsListScreen / CommentListScreen 契约）
 * - 列表：分页数组 data[] 含 goods_id / goods_name / goods_image / goods_min_price / selling_point / goods_sales
 * - 详情：detail{ goods_name, goods_image, image[], content, goods_sales, selling_point,
 *                     goods_sku{...}, spec_type, goods_multi_spec{...}, comment_data[], comment_data_count }
 */
class Goods extends BaseModel
{
    protected $name = 'goods';

    /** 每页数量 */
    protected $pageSize = 15;

    /**
     * 商品列表（分页）
     * @param array $param [category_id, search, sort, page]
     * @return array 分页结构 { total, per_page, current_page, last_page, data:[] }
     */
    public function getList(array $param)
    {
        $query = $this->where('goods_status', 10)->where('is_delete', 0);
        if (!empty($param['category_id'])) {
            $query->where('category_id', $param['category_id']);
        }
        if (!empty($param['search'])) {
            $query->where('goods_name', 'like', '%' . $param['search'] . '%');
        }
        $sort = $param['sort'] ?? '';
        if ($sort === 'sales') {
            $query->order('goods_sales', 'desc');
        } elseif ($sort === 'price') {
            $query->order('goods_price', 'asc');
        } else {
            $query->order('goods_sort', 'asc');
        }

        $page = (int)($param['page'] ?? 1);
        $size = $this->pageSize;
        $count = $query->count();
        $rows = $query->page($page, $size)->select()->toArray();

        foreach ($rows as &$item) {
            $item['goods_sales'] = (int)$item['sales_initial'] + (int)$item['sales_actual'];
            $imgId = Db::name('goods_image')->where('goods_id', $item['goods_id'])
                ->order('id', 'asc')->value('image_id');
            $item['goods_image'] = self::getFileUrl($imgId);
            $minPrice = Db::name('goods_sku')->where('goods_id', $item['goods_id'])->min('goods_price');
            $item['goods_min_price'] = $minPrice ?: '0.00';
        }

        $lastPage = $count > 0 ? (int)ceil($count / $size) : 1;
        return [
            'total' => $count,
            'per_page' => $size,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $rows,
        ];
    }

    /**
     * 商品详情
     * @param int $goodsId
     * @return array|false
     */
    public function getDetail($goodsId)
    {
        $goods = $this->where('goods_id', $goodsId)
            ->where('goods_status', 10)
            ->where('is_delete', 0)
            ->find();
        if (!$goods) {
            $this->setError('商品不存在或已下架');
            return false;
        }
        $goods = $goods->toArray();
        $goods['goods_sales'] = (int)$goods['sales_initial'] + (int)$goods['sales_actual'];

        // 主图 & 轮播图
        $imgIds = Db::name('goods_image')->where('goods_id', $goodsId)
            ->order('id', 'asc')->column('image_id');
        $goodsImage = !empty($imgIds) ? self::getFileUrl($imgIds[0]) : '';
        $goods['goods_image'] = $goodsImage;
        $goods['image'] = array_map(function ($id) {
            return ['file_path' => self::getFileUrl($id)];
        }, $imgIds);

        // 默认 SKU 对象（单/多规格都需一个初始 sku 供前端渲染）
        $sku = Db::name('goods_sku')->where('goods_id', $goodsId)
            ->order('goods_sku_id', 'asc')->find();
        if ($sku) {
            $sku['image_path'] = !empty($sku['image_id']) ? self::getFileUrl($sku['image_id']) : $goodsImage;
            unset($sku['create_time'], $sku['update_time']);
            $goods['goods_sku'] = $sku;
        } else {
            $goods['goods_sku'] = [
                'goods_price' => '0.00', 'line_price' => '0.00', 'stock_num' => 0,
                'spec_sku_id' => '0', 'image_id' => 0, 'image_path' => $goodsImage,
            ];
        }

        // 多规格
        if ($goods['spec_type'] == 20) {
            $goods['goods_multi_spec'] = $this->buildMultiSpec($goodsId, $goodsImage);
        }

        // 商品评价（最新 2 条 + 总数）
        $comment = $this->getCommentData($goodsId, 2);
        $goods['comment_data'] = $comment['list'];
        $goods['comment_data_count'] = $comment['total'];

        return $goods;
    }

    /**
     * 构造多规格数据（spec_attr / spec_list），对齐 RN initManySpec / updateSpec
     */
    private function buildMultiSpec($goodsId, $goodsImage)
    {
        $rels = Db::name('goods_spec_rel')->where('goods_id', $goodsId)->select()->toArray();
        if (empty($rels)) {
            return [];
        }
        $specIds = array_unique(array_column($rels, 'spec_id'));
        $specRows = Db::name('spec')->whereIn('spec_id', $specIds)->select()->toArray();
        $specName = array_column($specRows, 'spec_name', 'spec_id');

        $specValueIds = array_unique(array_column($rels, 'spec_value_id'));
        $specValueRows = Db::name('spec_value')->whereIn('spec_value_id', $specValueIds)->select()->toArray();
        $specValueMap = [];
        foreach ($specValueRows as $sv) {
            $specValueMap[$sv['spec_value_id']] = $sv['spec_value'];
        }

        $specAttr = [];
        foreach ($specIds as $sid) {
            $items = [];
            foreach ($rels as $rel) {
                if ($rel['spec_id'] == $sid) {
                    $items[] = [
                        'item_id' => $rel['spec_value_id'],
                        'spec_value' => $specValueMap[$rel['spec_value_id']] ?? '',
                        'checked' => false,
                    ];
                }
            }
            $specAttr[] = [
                'group_name' => $specName[$sid] ?? '',
                'spec_items' => $items,
            ];
        }

        $skus = Db::name('goods_sku')->where('goods_id', $goodsId)->select()->toArray();
        $specList = [];
        foreach ($skus as $sku) {
            $specList[] = [
                'spec_sku_id' => (string)$sku['spec_sku_id'],
                'form' => [
                    'goods_price' => $sku['goods_price'],
                    'line_price' => $sku['line_price'],
                    'stock_num' => $sku['stock_num'],
                    'image_id' => $sku['image_id'],
                    'image_path' => !empty($sku['image_id']) ? self::getFileUrl($sku['image_id']) : $goodsImage,
                ],
            ];
        }
        return ['spec_attr' => $specAttr, 'spec_list' => $specList];
    }

    /**
     * 商品评价数据（含用户 / 图片 / 订单规格）
     * @return array { list:[], total:int }
     */
    private function getCommentData($goodsId, $limit = 2)
    {
        $rows = Db::name('comment')
            ->where('goods_id', $goodsId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->order('create_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
        foreach ($rows as &$c) {
            $user = Db::name('user')->where('user_id', $c['user_id'])
                ->field('nickName,avatarUrl')->find();
            $c['user'] = $user ?: ['nickName' => '', 'avatarUrl' => ''];
            $imgIds = Db::name('comment_image')->where('comment_id', $c['comment_id'])->column('image_id');
            $c['image'] = array_map(function ($id) {
                return ['file_path' => self::getFileUrl($id)];
            }, $imgIds);
            $og = Db::name('order_goods')->where('order_goods_id', $c['order_goods_id'])
                ->field('goods_attr')->find();
            $c['order_goods'] = $og ?: ['goods_attr' => ''];
            $ts = is_numeric($c['create_time']) ? (int)$c['create_time'] : strtotime($c['create_time']);
            $c['create_time'] = $ts ? date('Y-m-d H:i', $ts) : '';
        }
        $total = Db::name('comment')
            ->where('goods_id', $goodsId)->where('status', 1)->where('is_delete', 0)
            ->count();
        return ['list' => $rows, 'total' => $total];
    }
}
