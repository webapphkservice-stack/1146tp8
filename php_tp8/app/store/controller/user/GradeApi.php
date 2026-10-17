<?php
namespace app\store\controller\user;

use app\store\controller\Api;
use app\store\model\user\Grade as GradeModel;

/**
 * 商家后台 - 会员等级管理 JSON API（原 user/Grade 控制器，因页面版占用类名而改名）
 *
 * 路由（store 应用，URL 带 /store 前缀，均为 api/ 前缀以避让页面路由）：
 *  GET  api/user.grade/index
 *  GET  api/user.grade/detail
 *  POST api/user.grade/add
 *  POST api/user.grade/edit
 *  POST api/user.grade/delete
 *  POST api/user.grade/state
 */
class GradeApi extends Api
{
    public function index()
    {
        $model = new GradeModel;
        $list = $model->getList();
        return $this->renderSuccess(['list' => $list], 'ok');
    }

    public function detail()
    {
        $gradeId = (int)$this->request->param('grade_id', 0);
        $model = GradeModel::detail($gradeId);
        if (!$model) {
            return $this->renderError('会员等级不存在');
        }
        return $this->renderSuccess(['detail' => $model], 'ok');
    }

    public function add()
    {
        $data = $this->request->post();
        $data['wxapp_id'] = $this->getWxappId();
        $model = new GradeModel;
        if ($model->add($data)) {
            return $this->renderSuccess([], '添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    public function edit()
    {
        $gradeId = (int)$this->request->param('grade_id', 0);
        $model = GradeModel::detail($gradeId);
        if (!$model) {
            return $this->renderError('会员等级不存在');
        }
        $data = $this->request->post();
        if ($model->edit($data)) {
            return $this->renderSuccess([], '更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    public function delete()
    {
        $gradeId = (int)$this->request->param('grade_id', 0);
        $model = GradeModel::detail($gradeId);
        if (!$model) {
            return $this->renderError('会员等级不存在');
        }
        if ($model->setDelete()) {
            return $this->renderSuccess([], '删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

    public function state()
    {
        $gradeId = (int)$this->request->param('grade_id', 0);
        $state = (int)$this->request->param('state', 0);
        $model = GradeModel::detail($gradeId);
        if (!$model) {
            return $this->renderError('会员等级不存在');
        }
        $model->save(['status' => $state]);
        return $this->renderSuccess([], '操作成功');
    }
}
