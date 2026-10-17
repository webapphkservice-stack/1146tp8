<?php

namespace app\store\model\wxapp;

use app\common\model\BaseModel;

/**
 * form_id 模型(后台)
 * Class Formid
 * @package app\store\model\wxapp
 */
class Formid extends BaseModel
{
    protected $name = 'wxapp_formid';

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User');
    }

    /**
     * 获取活跃用户列表
     * @return \think\Paginator
     */
    public function getUserList()
    {
        return $this->with(['user'])
            ->field(['user_id', 'count(id) AS total_formid'])
            ->where('is_used', '=', 0)
            ->where('expiry_time', '>', time())
            ->group('user_id')
            ->order(['total_formid' => 'desc'])
            ->paginate(15, false, [
                'query' => request()->request()
            ]);
    }

}
