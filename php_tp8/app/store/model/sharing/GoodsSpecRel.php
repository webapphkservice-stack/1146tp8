<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团商品规格关系模型(后台，自包含)
 * Class GoodsSpecRel
 * @package app\store\model\sharing
 */
class GoodsSpecRel extends BaseModel
{
    protected $name = 'sharing_goods_spec_rel';

    /**
     * 关联规格组
     * @return \think\model\relation\BelongsTo
     */
    public function spec()
    {
        return $this->belongsTo('app\common\model\Spec', 'spec_id', 'spec_id');
    }

    /**
     * 关联规格值
     * @return \think\model\relation\BelongsTo
     */
    public function specValue()
    {
        return $this->belongsTo('app\common\model\SpecValue', 'spec_value_id', 'spec_value_id');
    }

}
