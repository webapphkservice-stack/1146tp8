<?php

namespace app\store\model\bargain;

use app\common\model\BaseModel;

/**
 * 砍价任务助力记录模型(后台，自包含)
 * Class TaskHelp
 * @package app\store\model\bargain
 */
class TaskHelp extends BaseModel
{
    protected $name = 'bargain_task_help';

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 获取列表数据
     * @param $task_id
     * @return \think\Paginator
     */
    public function getList($task_id)
    {
        return $this->with(['user'])
            ->where('task_id', '=', $task_id)
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

}
