<?php
namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\Grade as GradeModel;

/**
 * 会员等级（服务端渲染版，移植自 TP5 store/controller/user/Grade.php）
 * 菜单：用户管理 -> 会员等级 -> 等级管理（user.grade/index）
 */
class Grade extends Controller
{
    /**
     * 会员等级列表
     */
    public function index()
    {
        $model = new GradeModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加等级
     */
    public function add()
    {
        $model = new GradeModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        if ($model->add($this->postData('grade'))) {
            return $this->renderSuccess('添加成功', (string)url('user.grade/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑会员等级
     * @param int $grade_id
     */
    public function edit($grade_id)
    {
        $model = GradeModel::detail($grade_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->postData('grade'))) {
            return $this->renderSuccess('更新成功', (string)url('user.grade/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除会员等级
     * @param int $grade_id
     */
    public function delete($grade_id)
    {
        $model = GradeModel::detail($grade_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
