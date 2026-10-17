<?php

namespace app\store\model\sharp;

use app\common\model\BaseModel;
use app\store\model\Goods as GoodsModel;
use app\store\service\Goods as GoodsService;

/**
 * 整点秒杀-商品模型(后台，自包含)
 * Class Goods
 * @package app\store\model\sharp
 */
class Goods extends BaseModel
{
    protected $name = 'sharp_goods';

    /**
     * 关联普通商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('app\store\model\Goods', 'goods_id', 'goods_id');
    }

    /**
     * 关联秒杀sku
     * @return \think\model\relation\HasMany
     */
    public function sku()
    {
        return $this->hasMany('app\store\model\sharp\GoodsSku', 'sharp_goods_id', 'sharp_goods_id');
    }

    /**
     * 获取列表数据
     * @param string $search
     * @return \think\Paginator
     */
    public function getList($search = '')
    {
        // join goods + goods_image 子表拿商品名和首张图
        $list = $this->withoutGlobalScope(['wxappId'])
            ->alias('sg')
            ->join('goods g', 'g.goods_id = sg.goods_id')
            ->join('goods_image gi', 'gi.goods_id = sg.goods_id AND gi.id = (SELECT MIN(id) FROM mybaixin_goods_image WHERE goods_id = sg.goods_id)', 'LEFT')
            ->where('sg.wxapp_id', self::getWxappId())
            ->where('sg.is_delete', '=', 0)
            ->order(['sg.sort' => 'asc', 'sg.create_time' => 'desc'])
            ->field('sg.*, g.goods_name, gi.image_id AS goods_image_id')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    /**
     * 根据商品id集获取商品列表
     * @param array $goodsIds
     * @param array $param
     * @return mixed
     */
    public function getListByIds($goodsIds, $param = [])
    {
        return (new GoodsModel)->getListByIds($goodsIds, $param);
    }

    /**
     * 详情
     * @param $sharpGoodsId
     * @param array $with
     * @return null|\think\Model
     */
    public static function detail($sharpGoodsId, $with = [])
    {
        return static::with($with)->find($sharpGoodsId);
    }

    /**
     * 添加商品
     * @param $goods
     * @param $data
     * @return bool
     */
    public function add($goods, $data)
    {
        $this->save(array_merge($data, [
            'goods_id' => $goods['goods_id'],
            'seckill_stock' => $this->getSeckillStock($goods, $data),
            'wxapp_id' => self::getWxappId(),
        ]));
        $this->addGoodsSpec($goods, $data);
        return true;
    }

    /**
     * 编辑商品
     * @param $goods
     * @param $data
     * @return bool
     */
    public function edit($goods, $data)
    {
        $this->save(array_merge($data, [
            'seckill_stock' => $this->getSeckillStock($goods, $data),
        ]));
        $this->addGoodsSpec($goods, $data, true);
        return true;
    }

    /**
     * 获取总库存数量
     * @param $goods
     * @param $data
     * @return int
     */
    private function getSeckillStock($goods, $data)
    {
        if ($goods['spec_type'] == '10') {
            return $data['sku']['seckill_stock'];
        }
        $seckillStock = 0;
        foreach ($data['spec_many']['spec_list'] as $item) {
            $seckillStock += $item['form']['seckill_stock'];
        }
        return $seckillStock;
    }

    /**
     * 验证商品ID能否被添加
     * @param $goodsId
     * @return bool
     */
    public function validateGoodsId($goodsId)
    {
        if ($goodsId <= 0) {
            $this->error = '很抱歉，您还没有选择商品';
            return false;
        }
        if ($this->isExistGoodsId($goodsId)) {
            $this->error = '很抱歉，该商品已存在，无需重复添加';
            return false;
        }
        return true;
    }

    /**
     * 添加商品规格
     * @param $goods
     * @param $data
     * @param bool $isUpdate
     * @return array|\think\Model
     */
    private function addGoodsSpec($goods, $data, $isUpdate = false)
    {
        $model = new GoodsSku;
        $isUpdate && $model->removeAll($this['sharp_goods_id']);
        if ($goods['spec_type'] == '10') {
            return $this->sku()->save(array_merge($data['sku'], [
                'wxapp_id' => self::getWxappId(),
            ]));
        }
        return $model->addSkuList($this['sharp_goods_id'], $data['spec_many']['spec_list']);
    }

    /**
     * 商品ID是否存在
     * @param $goodsId
     * @return bool
     */
    public static function isExistGoodsId($goodsId)
    {
        return !!(new static)->where('goods_id', '=', $goodsId)
            ->where('is_delete', '=', 0)
            ->value('sharp_goods_id');
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        (new ActiveGoods)->onDeleteSharpGoods($this['sharp_goods_id']);
        return $this->save(['is_delete' => 1]) !== false;
    }

    /**
     * 组装秒杀商品规格数据（供编辑页）
     * @param $goods
     * @param null $sharpSku
     * @return array
     */
    public function getSpecData($goods, $sharpSku = null)
    {
        $specData = GoodsService::getSpecData($goods);
        if (!empty($sharpSku)) {
            $skuMap = [];
            foreach ($sharpSku as $item) {
                $skuMap[$item['spec_sku_id']] = $item;
            }
            foreach ($specData['spec_list'] as &$spec) {
                $sid = $spec['spec_sku_id'] ?? '';
                if (isset($skuMap[$sid])) {
                    $spec['form']['seckill_price'] = $skuMap[$sid]['seckill_price'] ?? '';
                    $spec['form']['seckill_stock'] = $skuMap[$sid]['seckill_stock'] ?? '';
                    $spec['form']['sharp_goods_sku_id'] = $skuMap[$sid]['sharp_goods_sku_id'] ?? '';
                }
            }
        }
        return $specData;
    }

}
