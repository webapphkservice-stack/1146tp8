<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;
use think\Collection;
use app\store\service\Goods as GoodsService;

/**
 * 商品模型（服务端渲染版，移植自 TP5 store/model/Goods.php）
 * 用 Db 直查自包含实现，不依赖缺失的 common 模型层。
 */
class Goods extends BaseModel
{
    protected $name = 'goods';
    protected $pk = 'goods_id';
    protected $updateTime = false;

    /**
     * 获取商品列表（含主图/分类/状态文本）
     */
    public function getList($param)
    {
        $params = array_merge([
            'status'      => -1,     // 商品状态(-1 全部)
            'category_id' => 0,
            'search'      => '',
        ], $param);
        $filter = [];
        if ($params['category_id'] > 0) {
            $filter[] = ['category_id', 'in', Category::getSubCategoryId($params['category_id'])];
        }
        if ($params['status'] > -1) {
            $filter[] = ['goods_status', '=', $params['status']];
        }
        if (!empty($params['search'])) {
            $filter[] = ['goods_name', 'like', '%' . trim($params['search']) . '%'];
        }
        $list = Db::name('goods')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->where($filter)
            ->order(['goods_sort' => 'asc', 'goods_id' => 'desc'])
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $goodsIds = array_column($items, 'goods_id');
        $images = [];
        if ($goodsIds) {
            $rows = Db::name('goods_image')->alias('gi')
                ->where('gi.goods_id', 'in', $goodsIds)
                ->where('gi.wxapp_id', self::getWxappId())
                ->field('gi.goods_id, gi.image_id')
                ->order('gi.id', 'asc')->select()->toArray();
            foreach ($rows as $r) {
                $images[$r['goods_id']][] = ['file_path' => self::getFileUrl($r['image_id'])];
            }
        }
        $catIds = array_unique(array_column($items, 'category_id'));
        $cats = [];
        if ($catIds) {
            $crows = Db::name('category')->where('category_id', 'in', $catIds)
                ->field('category_id, name')->select()->toArray();
            foreach ($crows as $c) {
                $cats[$c['category_id']] = ['name' => $c['name']];
            }
        }
        foreach ($items as &$item) {
            $item['image'] = $images[$item['goods_id']] ?? [];
            $item['category'] = $cats[$item['category_id']] ?? ['name' => ''];
            $item['goods_status'] = [
                'value' => (int)$item['goods_status'],
                'text'  => $item['goods_status'] == 10 ? '上架' : '下架',
            ];
            // goods 表没有 goods_sales 字段,用 initial + actual 代替
            $item['goods_sales'] = (int)($item['sales_initial'] ?? 0) + (int)($item['sales_actual'] ?? 0);
        }
        $this->setPaginatorItems($list, $items);
        return $list;
    }

    /**
     * 按商品 id 列表获取商品（编辑优惠券回显已选商品）
     * @param array $ids
     * @return \think\Collection
     */
    public function getListByIds($ids)
    {
        if (empty($ids)) {
            return Collection::make([]);
        }
        $rows = Db::name('goods')
            ->where('goods_id', 'in', $ids)
            ->where('wxapp_id', self::getWxappId())
            ->field('goods_id, goods_name')
            ->select()->toArray();
        foreach ($rows as &$g) {
            $imageId = Db::name('goods_image')->where('goods_id', $g['goods_id'])
                ->order('id', 'asc')->value('image_id');
            $g['goods_image'] = $imageId ? BaseModel::getFileUrl($imageId) : '';
        }
        unset($g);
        return Collection::make($rows);
    }

    /**
     * 替换分页对象的条目（think\Paginator 为抽象类，无法直接 new）
     */
    private function setPaginatorItems($paginator, $items)
    {
        $paginator->setCollection(Collection::make($items));
    }

    /**
     * 商品详情（含图片/SKU）
     */
    public static function detail($goodsId)
    {
        $row = Db::name('goods')->where('goods_id', $goodsId)->find();
        if (!$row) {
            return [];
        }
        $imageRows = Db::name('goods_image')->alias('gi')
            ->where('gi.goods_id', $goodsId)
            ->field('gi.image_id')
            ->order('gi.id', 'asc')->select()->toArray();
        $row['image'] = array_map(function ($im) {
            return ['image_id' => $im['image_id'], 'file_path' => self::getFileUrl($im['image_id'])];
        }, $imageRows);
        $row['goods_status'] = [
            'text'  => $row['goods_status'] == 10 ? '上架' : '下架',
            'value' => (int)$row['goods_status'],
        ];
        $row['sku'] = Db::name('goods_sku')->where('goods_id', $goodsId)
            ->order('goods_sku_id', 'asc')->select()->toArray();
        if (empty($row['sku'])) {
            $row['sku'] = [['goods_no' => '', 'goods_price' => '', 'line_price' => '', 'stock_num' => '', 'goods_weight' => '']];
        }
        return $row;
    }

    /**
     * 添加商品
     */
    public function add(array $data)
    {
        if (empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = $data['content'] ?? '';
        $data['wxapp_id'] = self::getWxappId();
        Db::startTrans();
        try {
            $goodsId = Db::name('goods')->insertGetId([
                'goods_name'        => $data['goods_name'],
                'category_id'       => (int)$data['category_id'],
                'selling_point'     => $data['selling_point'] ?? '',
                'spec_type'         => (int)$data['spec_type'],
                'delivery_id'       => (int)($data['delivery_id'] ?? 0),
                'deduct_stock_type' => (int)($data['deduct_stock_type'] ?? 10),
                'content'           => $data['content'],
                'sales_initial'     => 0,
                'sales_actual'      => 0,
                'goods_sort'        => (int)($data['goods_sort'] ?? 100),
                'goods_status'      => (int)($data['goods_status'] ?? 10),
                'is_delete'         => 0,
                'wxapp_id'          => $data['wxapp_id'],
                'create_time'       => time(),
                'update_time'       => time(),
            ]);
            $this->addGoodsImages($goodsId, $data['images']);
            $this->addGoodsSpec($goodsId, $data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    private function addGoodsImages($goodsId, $images)
    {
        $data = array_map(function ($imageId) use ($goodsId) {
            return [
                'goods_id'    => $goodsId,
                'image_id'    => $imageId,
                'wxapp_id'    => self::getWxappId(),
                'create_time' => time(),
            ];
        }, $images);
        if ($data) {
            Db::name('goods_image')->insertAll($data);
        }
    }

    private function addGoodsSpec($goodsId, $data)
    {
        if ((int)$data['spec_type'] === 10) {
            // 单规格
            $sku = $data['sku'];
            Db::name('goods_sku')->insert([
                'goods_id'     => $goodsId,
                'spec_sku_id'  => '',
                'image_id'     => 0,
                'goods_no'     => $sku['goods_no'] ?? '',
                'goods_price'  => (float)($sku['goods_price'] ?? 0),
                'line_price'   => (float)($sku['line_price'] ?? 0),
                'stock_num'    => (int)($sku['stock_num'] ?? 0),
                'goods_weight' => (float)($sku['goods_weight'] ?? 0),
                'wxapp_id'     => self::getWxappId(),
                'create_time'  => time(),
                'update_time'  => time(),
            ]);
        } else {
            // 多规格
            $model = new GoodsSku;
            $model->addGoodsSpecRel($goodsId, $data['spec_many']['spec_attr']);
            $model->addSkuList($goodsId, $data['spec_many']['spec_list']);
        }
    }

    /**
     * 编辑商品
     */
    public function edit($data)
    {
        if (empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $goodsId = (int)$data['goods_id'];
        $data['content'] = $data['content'] ?? '';
        Db::startTrans();
        try {
            Db::name('goods')->where('goods_id', $goodsId)->update([
                'goods_name'        => $data['goods_name'],
                'category_id'       => (int)$data['category_id'],
                'selling_point'     => $data['selling_point'] ?? '',
                'spec_type'         => (int)$data['spec_type'],
                'delivery_id'       => (int)($data['delivery_id'] ?? 0),
                'deduct_stock_type' => (int)($data['deduct_stock_type'] ?? 10),
                'content'           => $data['content'],
                'goods_sort'        => (int)($data['goods_sort'] ?? 100),
                'goods_status'      => (int)($data['goods_status'] ?? 10),
                'update_time'       => time(),
            ]);
            Db::name('goods_image')->where('goods_id', $goodsId)->delete();
            $this->addGoodsImages($goodsId, $data['images']);
            (new GoodsSku)->removeAll($goodsId);
            $this->addGoodsSpec($goodsId, $data);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 修改商品状态
     */
    public function setStatus($goodsId, $state)
    {
        return Db::name('goods')->where('goods_id', $goodsId)
            ->update(['goods_status' => $state ? 10 : 20, 'update_time' => time()]) !== false;
    }

    /**
     * 软删除
     */
    public function setDelete($goodsId)
    {
        if (!GoodsService::checkIsAllowDelete($goodsId)) {
            $this->error = '当前商品正在参与其他活动，不允许删除';
            return false;
        }
        return Db::name('goods')->where('goods_id', $goodsId)
            ->update(['is_delete' => 1, 'update_time' => time()]) !== false;
    }
}
