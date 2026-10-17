<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团商品规格sku模型(后台，自包含)
 * Class GoodsSku
 * @package app\store\model\sharing
 */
class GoodsSku extends BaseModel
{
    protected $name = 'sharing_goods_sku';

    /**
     * 批量添加商品sku记录
     * @param $goods_id
     * @param $spec_list
     * @return array|false
     */
    public function addSkuList($goods_id, $spec_list)
    {
        $data = [];
        foreach ($spec_list as $item) {
            $data[] = array_merge($item['form'], [
                'spec_sku_id' => $item['spec_sku_id'],
                'goods_id' => $goods_id,
                'wxapp_id' => self::getWxappId(),
            ]);
        }
        return $this->saveAll($data);
    }

    /**
     * 添加商品规格关系记录
     * @param $goods_id
     * @param $spec_attr
     * @return array|false
     */
    public function addGoodsSpecRel($goods_id, $spec_attr)
    {
        $data = [];
        array_map(function ($val) use (&$data, $goods_id) {
            array_map(function ($item) use (&$val, &$data, $goods_id) {
                $data[] = [
                    'goods_id' => $goods_id,
                    'spec_id' => $val['group_id'],
                    'spec_value_id' => $item['item_id'],
                    'wxapp_id' => self::getWxappId(),
                ];
            }, $val['spec_items']);
        }, $spec_attr);
        $model = new GoodsSpecRel;
        return $model->saveAll($data);
    }

    /**
     * 移除指定商品的所有sku与规格关系
     * @param $goods_id
     * @return int
     */
    public function removeAll($goods_id)
    {
        $model = new GoodsSpecRel;
        $model->where('goods_id', '=', $goods_id)->delete();
        return $this->where('goods_id', '=', $goods_id)->delete();
    }

}
