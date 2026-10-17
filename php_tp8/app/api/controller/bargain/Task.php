<?php

namespace app\api\controller\bargain;

use app\api\controller\Controller;
use app\api\service\bargain\Active as ActiveService;
use app\api\service\bargain\Task as TaskService;

/**
 * 砍价任务：我的列表 / 发起 / 详情 / 帮砍一刀
 */
class Task extends Controller
{
    /**
     * 我的砍价列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $myList = TaskService::getMyList($user['user_id']);
        return $this->renderSuccess(compact('myList'));
    }

    /**
     * 发起砍价（POST）
     * 注意：goods_sku_id 按 spec_sku_id 匹配（单规格传 '0'）
     */
    public function partake()
    {
        $param = $this->request->param();
        $activeId = (int)($param['active_id'] ?? 0);
        $goodsSkuId = (string)($param['goods_sku_id'] ?? '0');
        $user = $this->getUser(true);
        $res = TaskService::partake($user['user_id'], $activeId, $goodsSkuId);
        if (isset($res['error'])) {
            return $this->renderError($res['error']);
        }
        return $this->renderSuccess($res);
    }

    /**
     * 砍价任务详情
     */
    public function detail($task_id)
    {
        $task = TaskService::getTaskDetail($task_id);
        if ($task === false) {
            return $this->renderError('砍价任务不存在');
        }
        $setting = ActiveService::getSetting($this->getWxappId());
        return $this->renderSuccess(array_merge($task, ['setting' => $setting]));
    }

    /**
     * 帮砍一刀（POST）
     */
    public function help_cut()
    {
        $taskId = (int)($this->request->param('task_id') ?? 0);
        $user = $this->getUser(true);
        $res = TaskService::helpCut($taskId, $user['user_id']);
        if (isset($res['error'])) {
            return $this->renderError($res['error']);
        }
        return $this->renderSuccess(['cut_money' => $res['cut_money']]);
    }
}
