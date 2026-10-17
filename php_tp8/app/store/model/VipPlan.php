<?php

namespace app\store\model;

use app\common\model\VipPlan as VipPlanModel;

/**
 * VIP会员套餐模型(后台)
 * Class VipPlan
 * @package app\store\model
 */
class VipPlan extends VipPlanModel
{
    /**
     * 获取套餐列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->order(['sort' => 'asc', 'plan_id' => 'asc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

    /**
     * 添加新记录
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();
        $data['update_time'] = time();
        // think-orm v4 不支持 allowField(true)，直接 save 全部表字段
        return $this->save($data);
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $data['update_time'] = time();
        return $this->save($data) !== false;
    }

    /**
     * 删除记录
     * @return bool
     */
    public function setDelete()
    {
        return $this->delete();
    }

}
