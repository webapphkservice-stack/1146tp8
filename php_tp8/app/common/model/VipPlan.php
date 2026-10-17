<?php

namespace app\common\model;

/**
 * VIP会员套餐模型
 * Class VipPlan
 * @package app\common\model
 */
class VipPlan extends BaseModel
{
    protected $name = 'vip_plan';

    /**
     * 套餐详情
     * @param $planId
     * @return null|static
     */
    public static function detail($planId)
    {
        // TP8 think-orm v4 中 get() 已为非静态，改用静态代理 find()
        return static::find($planId);
    }

    /**
     * 获取已启用套餐
     * @return \think\model\collection\Collection
     */
    public function getEnableList()
    {
        return $this->where('is_enable', '=', 1)
            ->order(['sort' => 'asc', 'plan_id' => 'asc'])
            ->select();
    }
}
