<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团订单商品模型(后台，自包含)
 * Class OrderGoods
 * @package app\store\model\sharing
 */
class OrderGoods extends BaseModel
{
    protected $name = 'sharing_order_goods';

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('app\store\model\sharing\Goods', 'goods_id', 'goods_id');
    }

    /**
     * 关联图片
     * @return \think\model\relation\BelongsTo
     */
    public function image()
    {
        return $this->belongsTo('app\common\model\UploadFile', 'image_id', 'file_id')
            ->bind(['file_path']);
    }

    /**
     * 退还库存
     * @param $goodsId
     * @param $goodsSkuId
     * @param $goodsNum
     */
    public function backGoodsStock($goodsId, $goodsSkuId, $goodsNum)
    {
        if ($goodsSkuId > 0) {
            $sku = (new GoodsSku)->where('goods_sku_id', '=', $goodsSkuId)->find();
            $sku && $sku->setInc('stock_num', $goodsNum);
        }
        $goods = (new Goods)->where('goods_id', '=', $goodsId)->find();
        $goods && $goods->setInc('total_stock', $goodsNum);
    }

}
