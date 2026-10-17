<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\DailyTask as DailyTaskModel;

/**
 * 每日任务管理
 * Class Task
 * @package app\store\controller\apps\dealer
 */
class Task extends Controller
{
    /**
     * 任务列表
     * @return mixed
     */
    public function index()
    {
        $model = new DailyTaskModel;
        return $this->fetch('index', [
            'list' => $model->getList(),
        ]);
    }

    /**
     * 添加任务
     * @return array|mixed
     */
    public function add()
    {
        $model = new DailyTaskModel;
        if ($this->request->isAjax()) {
            $data = $this->postData('task');
            if ($model->add($data)) {
                return $this->renderSuccess('添加成功');
            }
            return $this->renderError($model->getError() ?: '添加失败');
        }
        return $this->fetch('add');
    }

    /**
     * 编辑任务
     * @param $task_id
     * @return array|mixed
     */
    public function edit($task_id)
    {
        $model = DailyTaskModel::detail($task_id);
        if ($this->request->isAjax()) {
            $data = $this->postData('task');
            if ($model->edit($data)) {
                return $this->renderSuccess('更新成功');
            }
            return $this->renderError($model->getError() ?: '更新失败');
        }
        return $this->fetch('edit', compact('model'));
    }

    /**
     * 删除任务
     * @param $task_id
     * @return array
     */
    public function delete($task_id)
    {
        $model = new DailyTaskModel;
        if ($model->remove($task_id)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

}
