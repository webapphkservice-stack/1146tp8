<?php

namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\User as UserModel;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 每日任务（移植自 php api/controller/user/Task，Db 轻量版）
 * Class Task
 * @package app\api\controller\user
 *
 * 小程序调用点：user.task/lists、user.task/receive
 */
class Task extends Controller
{
    /**
     * 每日任务列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $date = date('Y-m-d');
        $tasks = Db::name('daily_task')->where('wxapp_id', BaseModel::getWxappId())
            ->where('is_enable', 1)
            ->order('sort', 'asc')->select()->toArray();
        $receivedIds = Db::name('user_task_log')->where('user_id', $user['user_id'])
            ->where('task_date', $date)->column('task_id');
        $list = [];
        foreach ($tasks as $item) {
            $list[] = [
                'task_id'    => (int)$item['task_id'],
                'task_name'  => $item['task_name'],
                'task_icon'  => $item['task_icon'],
                'task_type'  => (int)$item['task_type'],
                'points'     => (int)$item['points'],
                'is_received' => in_array($item['task_id'], $receivedIds),
            ];
        }
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 领取任务奖励
     */
    public function receive()
    {
        $user = $this->getUser(true);
        $taskId = (int)$this->request->param('task_id', 0);
        $task = Db::name('daily_task')->where('task_id', $taskId)->find();
        if (!$task || !$task['is_enable']) {
            return $this->renderError('任务不存在');
        }
        $date = date('Y-m-d');
        $received = Db::name('user_task_log')->where('user_id', $user['user_id'])
            ->where('task_id', $taskId)->where('task_date', $date)->count();
        if ($received > 0) {
            return $this->renderError('今日已领取');
        }
        Db::startTrans();
        try {
            Db::name('user_task_log')->insert([
                'wxapp_id'   => BaseModel::getWxappId(),
                'user_id'    => $user['user_id'],
                'task_id'    => $taskId,
                'task_type'  => $task['task_type'],
                'points'     => $task['points'],
                'task_date'  => $date,
                'create_time' => time(),
            ]);
            UserModel::changePoints($user['user_id'], $task['points'], '完成每日任务：' . $task['task_name']);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('领取失败');
        }
        return $this->renderSuccess([], '领取成功');
    }
}
