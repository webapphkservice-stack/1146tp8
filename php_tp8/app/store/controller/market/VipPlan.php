<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\VipPlan as VipPlanModel;

/**
 * VIP会员套餐管理
 * Class VipPlan
 * @package app\store\controller\market
 */
class VipPlan extends Controller
{
    /* @var VipPlanModel $model */
    private $model;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->model = new VipPlanModel;
    }

    /**
     * 套餐列表
     * @return mixed
     */
    public function index()
    {
        $list = $this->model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加套餐
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($this->model->add($this->postData('plan'))) {
            return $this->renderSuccess('添加成功', url('market.vip_plan/index'));
        }
        return $this->renderError($this->model->getError() ?: '添加失败');
    }

    /**
     * 更新套餐
     * @param $plan_id
     * @return array|mixed
     */
    public function edit($plan_id)
    {
        // 套餐详情
        $model = VipPlanModel::detail($plan_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('plan'))) {
            return $this->renderSuccess('更新成功', url('market.vip_plan/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除套餐
     * @param $plan_id
     * @return array|mixed
     */
    public function delete($plan_id)
    {
        // 套餐详情
        $model = VipPlanModel::detail($plan_id);
        // 删除记录
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功', url('market.vip_plan/index'));
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

}
