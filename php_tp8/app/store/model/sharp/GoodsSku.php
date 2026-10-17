<?php

namespace app\store\model\sharp;

use app\common\model\BaseModel;

/**
 * 整点秒杀-秒杀商品sku模型(后台，自包含)
 * Class GoodsSku
 * @package app\store\model\sharp
 */
class GoodsSku extends BaseModel
{
    protected $name = 'sharp_goods_sku';

    /**
     * 批量添加商品sku记录
     * @param $sharpGoodsId
     * @param $specList
     * @return array|false
     */
    public function addSkuList($sharpGoodsId, $specList)
    {
        $data = [];
        foreach ($specList as $item) {
            $data[] = array_merge($item['form'], [
                'spec_sku_id' => $item['spec_sku_id'],
                'sharp_goods_id' => $sharpGoodsId,
                'wxapp_id' => self::getWxappId(),
            ]);
        }
        return $this->saveAll($data);
    }

    /**
     * 移除指定商品的所有sku
     * @param $sharpGoodsId
     * @return int
     */
    public function removeAll($sharpGoodsId)
    {
        return $this->where('sharp_goods_id', '=', $sharpGoodsId)->delete();
    }

}
