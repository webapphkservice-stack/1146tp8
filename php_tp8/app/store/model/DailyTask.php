<?php

namespace app\store\model;

use app\common\model\BaseModel;

/**
 * 每日任务管理模型(后台，自包含)
 * Class DailyTask
 * @package app\store\model
 */
class DailyTask extends BaseModel
{
    protected $name = 'daily_task';
    protected $pk = 'task_id';

    /**
     * 详情
     * @param $taskId
     * @return null|\think\Model
     */
    public static function detail($taskId)
    {
        return static::find($taskId);
    }

    /**
     * 获取任务列表
     * @return \think\Collection
     */
    public function getList()
    {
        return $this->where('wxapp_id', self::getWxappId())
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select();
    }

    /**
     * 添加任务
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $this->insert($data) !== false;
    }

    /**
     * 编辑任务
     * @param array $data
     * @return bool
     */
    public function edit($data)
    {
        $data['update_time'] = time();
        return $this->where('task_id', $data['task_id'])
            ->where('wxapp_id', self::getWxappId())
            ->update($data) !== false;
    }

    /**
     * 删除任务
     * @param int $taskId
     * @return bool
     */
    public function remove($taskId)
    {
        return $this->where('task_id', $taskId)
            ->where('wxapp_id', self::getWxappId())
            ->delete() !== false;
    }

    /**
     * 获取启用的任务列表(小程序端)
     * @return \think\Collection
     */
    public static function getEnableList()
    {
        return (new static)->where('wxapp_id', self::getWxappId())
            ->where('is_enable', 1)
            ->order(['sort' => 'asc'])
            ->select();
    }

}
