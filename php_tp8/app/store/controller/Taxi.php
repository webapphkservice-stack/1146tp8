<?php

namespace app\store\controller;

use app\common\model\Setting;
use app\common\model\TaxiOrder as TaxiOrderModel;
use think\facade\Cache;

/**
 * 出租车（商户后台）
 * Class Taxi
 * @package app\store\controller
 */
class Taxi extends Controller
{
    /**
     * 出租车设置
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $values = Setting::getItem('taxi', $this->getWxappId()) ?: [];
            return $this->fetch('taxi/setting', [
                'values' => $values,
            ]);
        }
        $data = $this->postData('taxi');
        $wxappId = $this->getWxappId();
        $setting = Setting::detail('taxi');
        if (!$setting) {
            $setting = new Setting;
            $setting->key = 'taxi';
            $setting->describe = '出租车设置';
            $setting->wxapp_id = $wxappId;
        } else {
            $setting->isUpdate(true, ['key' => 'taxi', 'wxapp_id' => $wxappId]);
        }
        $setting->values = $data;
        $setting->update_time = time();
        if ($setting->save() !== false) {
            // 清除设置缓存，否则刷新后读到旧缓存
            Cache::rm('setting_' . $wxappId);
            return $this->renderSuccess('保存成功', url('taxi/setting'));
        }
        return $this->renderError($setting->getError() ?: '保存失败');
    }

    /**
     * 出租车订单列表
     */
    public function order()
    {
        $model = new TaxiOrderModel;
        $list = $model->getListForStore($this->getWxappId(), $this->request->param());
        $taxiValues = Setting::getItem('taxi', $this->getWxappId()) ?: [];
        $vehicles = isset($taxiValues['vehicles']) && is_array($taxiValues['vehicles']) ? $taxiValues['vehicles'] : [];
        $vehicleOptions = [];
        foreach ($vehicles as $vi => $vv) {
            $vehicleOptions[] = [
                'index' => $vi,
                'label' => (isset($vv['type']) ? $vv['type'] : '') . ' '
                    . (isset($vv['brand']) ? $vv['brand'] : '') . ' '
                    . (isset($vv['number']) ? $vv['number'] : ''),
            ];
        }
        return $this->fetch('taxi/order', [
            'list' => $list,
            'vehicles' => $vehicles,
            'vehicle_options' => $vehicleOptions,
        ]);
    }

    /**
     * 修改出租车订单状态 + 派车
     */
    public function orderState($order_id = 0)
    {
        $orderId = (int)$this->request->post('order_id', $order_id);
        $status = (int)$this->request->post('status', -1);
        $vehicleIndex = $this->request->post('vehicle_index', '');
        if ($orderId <= 0) {
            return $this->error('缺少参数：order_id');
        }
        if (!in_array($status, [0, 1, 2, 3], true)) {
            return $this->error('请选择有效的订单状态');
        }
        $model = TaxiOrderModel::find($orderId);
        if (!$model || $model['wxapp_id'] != $this->getWxappId()) {
            return $this->error('订单不存在或无权操作');
        }
        TaxiOrderModel::ensureDispatchFields();
        // 解析派车选择
        $dispatchCar = $model['dispatch_car'];
        if ($vehicleIndex === 'clear') {
            $dispatchCar = '';
        } elseif ($vehicleIndex !== '' && is_numeric($vehicleIndex)) {
            $values = Setting::getItem('taxi', $this->getWxappId()) ?: [];
            $vehicles = isset($values['vehicles']) && is_array($values['vehicles']) ? $values['vehicles'] : [];
            $idx = (int)$vehicleIndex;
            if (isset($vehicles[$idx])) {
                $v = $vehicles[$idx];
                $jsonOpt = defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
                $dispatchCar = json_encode([
                    'type'     => isset($v['type']) ? $v['type'] : '',
                    'brand'    => isset($v['brand']) ? $v['brand'] : '',
                    'number'   => isset($v['number']) ? $v['number'] : '',
                    'whatsapp' => isset($v['whatsapp']) ? $v['whatsapp'] : '',
                ], $jsonOpt);
            }
        }
        if (!$model->setStatus($status, $dispatchCar)) {
            return $this->error($model->getError() ?: '操作失败');
        }
        return $this->success('操作成功', url('taxi/order'));
    }
}
