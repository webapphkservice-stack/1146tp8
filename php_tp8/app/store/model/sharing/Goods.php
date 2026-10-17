<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团商品模型(后台，自包含)
 * Class Goods
 * @package app\store\model\sharing
 */
class Goods extends BaseModel
{
    protected $name = 'sharing_goods';

    /**
     * 关联商品图片
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('app\store\model\sharing\GoodsImage', 'goods_id', 'goods_id')
            ->order(['id' => 'asc']);
    }

    /**
     * 关联sku
     * @return \think\model\relation\HasMany
     */
    public function sku()
    {
        return $this->hasMany('app\store\model\sharing\GoodsSku', 'goods_id', 'goods_id');
    }

    /**
     * 关联规格关系
     * @return \think\model\relation\HasMany
     */
    public function specRel()
    {
        return $this->hasMany('app\store\model\sharing\GoodsSpecRel', 'goods_id', 'goods_id');
    }

    /**
     * 关联拼团分类
     * @return \think\model\relation\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('app\store\model\sharing\Category', 'category_id', 'category_id');
    }

    /**
     * 详情
     * @param $goodsId
     * @return null|\think\Model
     */
    public static function detail($goodsId)
    {
        return static::with(['sku', 'specRel.spec', 'specRel.specValue', 'image'])->find($goodsId);
    }

    /**
     * 获取列表数据
     * @param array $filter
     * @return \think\Paginator
     */
    public function getList($filter = [])
    {
        $model = $this;
        if (isset($filter['status']) && $filter['status'] > -1) {
            $model->where('goods_status', '=', $filter['status']);
        }
        if (isset($filter['category_id']) && $filter['category_id'] > 0) {
            $model->where('category_id', '=', $filter['category_id']);
        }
        if (!empty($filter['search'])) {
            $model->where('goods_name', 'like', "%{$filter['search']}%");
        }
        return $model->with(['image', 'category'])
            ->where('is_delete', '=', 0)
            ->order(['goods_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 添加商品
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = $data['content'] ?? '';
        $images = $data['images'];
        $sku = $data['sku'] ?? [];
        $specMany = $data['spec_many'] ?? [];
        $data['wxapp_id'] = self::getWxappId();
        unset($data['sku'], $data['spec_many'], $data['images']);
        $this->startTrans();
        try {
            $this->save($data);
            $this->addGoodsSpec($sku, $specMany);
            $this->addGoodsImages($images);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 更新商品
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (!isset($data['images']) || empty($data['images'])) {
            $this->error = '请上传商品图片';
            return false;
        }
        $data['content'] = $data['content'] ?? '';
        $images = $data['images'];
        $sku = $data['sku'] ?? [];
        $specMany = $data['spec_many'] ?? [];
        $data['wxapp_id'] = self::getWxappId();
        unset($data['sku'], $data['spec_many'], $data['images']);
        $this->startTrans();
        try {
            $this->save($data);
            $this->addGoodsSpec($sku, $specMany, true);
            $this->addGoodsImages($images);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加商品图片
     * @param $images
     * @return array|\think\Collection
     */
    private function addGoodsImages($images)
    {
        $this->image()->delete();
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::getWxappId(),
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 添加商品规格
     * @param $sku
     * @param $specMany
     * @param bool $isUpdate
     */
    private function addGoodsSpec($sku, $specMany, $isUpdate = false)
    {
        $model = new GoodsSku;
        $isUpdate && $model->removeAll($this['goods_id']);
        if (empty($sku) && empty($specMany)) {
            return;
        }
        if (empty($specMany)) {
            // 单规格
            $sku['wxapp_id'] = self::getWxappId();
            $this->sku()->save($sku);
        } else {
            // 多规格
            $model->addGoodsSpecRel($this['goods_id'], $specMany['spec_attr']);
            $model->addSkuList($this['goods_id'], $specMany['spec_list']);
        }
    }

    /**
     * 修改状态
     * @param $state
     * @return bool
     */
    public function setStatus($state)
    {
        return $this->save(['goods_status' => $state ? 10 : 20]) !== false;
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

    /**
     * 获取商品总数
     * @param array $where
     * @return int
     */
    public function getGoodsTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }

    /**
     * 组装多规格数据(供复制/编辑页)
     * @param null $specRel
     * @param null $sku
     * @return array
     */
    public function getManySpecData($specRel = null, $sku = null)
    {
        $goodsId = $this['goods_id'];
        $specRelData = Db::name('sharing_goods_spec_rel')->alias('gsr')
            ->join('spec s', 's.spec_id = gsr.spec_id')
            ->join('spec_value sv', 'sv.spec_value_id = gsr.spec_value_id')
            ->where('gsr.goods_id', '=', $goodsId)
            ->field('gsr.spec_id, gsr.spec_value_id, s.spec_name, sv.spec_value')
            ->order(['gsr.id' => 'asc'])
            ->select()->toArray();
        $skuData = Db::name('sharing_goods_sku')->where('goods_id', '=', $goodsId)
            ->order(['goods_sku_id' => 'asc'])
            ->select()->toArray();
        return (new \app\store\model\GoodsSku)->getManySpecData($specRelData, $skuData);
    }

}
