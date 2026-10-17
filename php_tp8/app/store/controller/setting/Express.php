<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Express as ExpressModel;

/**
 * 物流公司管理（后台 HTML CRUD）
 *
 * 对应 TP5 的 setting/Express.php；路由写法 setting.express/index
 * 发货场景的 JSON API 仍走根级 controller/Express.php::lists()（express/lists）
 */
class Express extends Controller
{
    /**
     * 物流公司列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ExpressModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加物流公司
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        $model = new ExpressModel;
        if ($model->add($this->postData('express'))) {
            return $this->renderSuccess('添加成功', url('setting.express/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param int $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($express_id)
    {
        $model = ExpressModel::detail($express_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->postData('express'))) {
            return $this->renderSuccess('更新成功', url('setting.express/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除物流公司
     * @param int $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($express_id)
    {
        $model = ExpressModel::detail($express_id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 物流公司编码表（帮助页）
     * @return mixed
     */
    public function company()
    {
        return $this->fetch('company');
    }
}
