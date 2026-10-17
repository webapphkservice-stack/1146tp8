<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼单成员模型(后台，自包含)
 * Class ActiveUsers
 * @package app\store\model\sharing
 */
class ActiveUsers extends BaseModel
{
    protected $name = 'sharing_active_users';

    /**
     * 关联拼团订单
     * @return \think\model\relation\BelongsTo
     */
    public function sharingOrder()
    {
        return $this->belongsTo('app\store\model\sharing\Order', 'order_id', 'order_id');
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 获取列表
     * @param $active_id
     * @return \think\Paginator
     */
    public function getList($active_id)
    {
        return $this->with(['sharingOrder', 'user'])
            ->where('active_id', '=', $active_id)
            ->order(['create_time' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

}
