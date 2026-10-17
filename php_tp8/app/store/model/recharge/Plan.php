<?php

namespace app\store\model\recharge;

use app\common\model\BaseModel;

/**
 * 充值套餐模型(后台)
 * Class Plan
 * @package app\store\model\recharge
 */
class Plan extends BaseModel
{
    protected $name = 'recharge_plan';

    /**
     * 套餐详情
     * @param $plan_id
     * @return null|\think\Model
     */
    public static function detail($plan_id)
    {
        return static::find($plan_id);
    }

    /**
     * 获取套餐列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
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
        return $this->save($data) !== false;
    }

    /**
     * 删除记录 (软删除)
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

}
