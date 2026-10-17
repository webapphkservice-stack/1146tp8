<?php

namespace app\common\model\store\shop;

use app\common\model\BaseModel;

/**
 * 商家门店核销订单记录模型
 * Class Order
 * @package app\common\model\store\shop
 */
class Order extends BaseModel
{
    protected $name = 'store_shop_order';
    protected $updateTime = false;

    /**
     * 关联门店表
     * @return \think\model\relation\BelongsTo
     */
    public function shop()
    {
        return $this->belongsTo('app\store\model\store\Shop');
    }

    /**
     * 关联店员表
     * @return \think\model\relation\BelongsTo
     */
    public function clerk()
    {
        return $this->belongsTo('app\store\model\store\shop\Clerk');
    }

    /**
     * 关联订单表
     * @return \think\model\relation\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('app\store\model\Order', 'order_id');
    }

    /**
     * 核销类型
     * @param $value
     * @return array
     */
    public function getOrderTypeAttr($value)
    {
        $map = [
            10 => '订单核销',
            20 => '订单核销',
        ];
        return ['text' => $map[$value] ?? '核销', 'value' => $value];
    }
}
