<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼单模型(后台，自包含)
 * Class Active
 * @package app\store\model\sharing
 */
class Active extends BaseModel
{
    protected $name = 'sharing_active';

    /**
     * 关联拼团商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('app\store\model\sharing\Goods', 'goods_id', 'goods_id');
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
     * @param null $active_id
     * @return \think\Paginator
     */
    public function getList($active_id = null)
    {
        $active_id > 0 && $this->where('active_id', '=', $active_id);
        return $this->with(['goods', 'user'])
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 详情
     * @param $activeId
     * @return null|\think\Model
     */
    public static function detail($activeId)
    {
        return static::find($activeId);
    }

}
