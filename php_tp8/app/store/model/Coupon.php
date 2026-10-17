<?php

namespace app\store\model;

use app\common\model\Coupon as CouponModel;

/**
 * 优惠券模型（门店后台）
 * Class Coupon
 * @package app\store\model
 */
class Coupon extends CouponModel
{
    /**
     * 获取优惠券列表
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
        if (($data['expire_type'] ?? 10) == 20) {
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time']   = strtotime($data['end_time']);
        }
        $data['apply_range_config'] = $data['apply_range_config'] ?? [];
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
        if (($data['expire_type'] ?? 10) == 20) {
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time']   = strtotime($data['end_time']);
        }
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
