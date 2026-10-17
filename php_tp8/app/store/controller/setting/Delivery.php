<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\common\model\Region as RegionModel;
use app\store\model\Delivery as DeliveryModel;

/**
 * 配送设置（运费模板）
 *
 * TP8 适配：Region 模型在 TP8 位于 app\common\model\Region（非 app\store\model\Region），
 * 故 use 路径相应调整；getCacheTree() / getCacheCounts() 均为已存在的静态方法。
 */
class Delivery extends Controller
{
    /**
     * 配送模板列表
     * @return mixed
     */
    public function index()
    {
        $model = new DeliveryModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     */
    public function delete($delivery_id)
    {
        $model = DeliveryModel::detail($delivery_id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加配送模板
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $regionData = json_encode(RegionModel::getCacheTree());
            // 地区总数
            $cityCount = RegionModel::getCacheCounts()['city'];
            return $this->fetch('add', compact('regionData', 'cityCount'));
        }
        // 新增记录
        $model = new DeliveryModel;
        if ($model->add($this->postData('delivery'))) {
            return $this->renderSuccess('添加成功', (string)url('setting.delivery/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑配送模板
     * @param $delivery_id
     * @return array|mixed
     */
    public function edit($delivery_id)
    {
        // 模板详情
        $model = DeliveryModel::detail($delivery_id);
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $regionData = json_encode(RegionModel::getCacheTree());
            // 地区总数
            $cityCount = RegionModel::getCacheCounts()['city'];
            // 获取配送区域及运费设置项
            $formData = json_encode($model->getFormList());
            return $this->fetch('add', compact('model', 'regionData', 'cityCount', 'formData'));
        }
        // 更新记录
        if ($model->edit($this->postData('delivery'))) {
            return $this->renderSuccess('更新成功', (string)url('setting.delivery/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}
