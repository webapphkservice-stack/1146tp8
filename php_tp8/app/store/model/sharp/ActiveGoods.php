<?php

namespace app\store\model\sharp;

use app\common\model\BaseModel;

/**
 * 整点秒杀-活动会场与商品关联模型(后台，自包含)
 * Class ActiveGoods
 * @package app\store\model\sharp
 */
class ActiveGoods extends BaseModel
{
    protected $name = 'sharp_active_goods';

    /**
     * 关联秒杀商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('app\store\model\sharp\Goods', 'sharp_goods_id', 'sharp_goods_id');
    }

    /**
     * 同步删除活动会场与商品关联记录
     * @param $sharpGoodsId
     */
    public function onDeleteSharpGoods($sharpGoodsId)
    {
        $this->where('sharp_goods_id', '=', $sharpGoodsId)->delete();
    }

    /**
     * 根据活动场次ID获取商品关联记录
     * @param $activeTimeId
     * @return \think\Collection
     */
    public static function getGoodsListByActiveTimeId($activeTimeId)
    {
        return (new static)->with(['goods'])
            ->where('active_time_id', '=', $activeTimeId)
            ->select();
    }

}
