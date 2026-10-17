<?php

namespace app\api\controller;

use app\common\model\Setting as SettingModel;
use app\common\model\TaxiOrder as TaxiOrderModel;
use app\common\model\BaseModel;

/**
 * 出租车 API（移植自 php api/controller/Taxi）
 * Class Taxi
 * @package app\api\controller
 *
 * RN 调用点：getTaxiSetting -> taxi/setting；submitTaxiOrder -> taxi/submit；getTaxiOrders -> taxi/orders
 */
class Taxi extends Controller
{
    /**
     * 获取出租车设置（机场列表 + 价格）
     */
    public function setting()
    {
        $values = SettingModel::getItem('taxi');
        if (empty($values)) {
            $values = (new SettingModel)->defaultData()['taxi']['values'] ?? [];
        }
        return $this->renderSuccess($values);
    }

    /**
     * 提交出租车预约订单
     */
    public function submit()
    {
        $user = $this->getUser();
        $request = $this->request;

        $airportName = trim((string)$request->param('airport_name', ''));
        $carType = (int)$request->param('car_type', 0);
        $flightNo = trim((string)$request->param('flight_no', ''));

        if ($airportName === '') {
            return $this->renderError('请选择抵达机场');
        }
        if (!in_array($carType, [0, 1], true)) {
            return $this->renderError('请选择出租车类型');
        }
        if ($flightNo === '') {
            return $this->renderError('请填写航班号');
        }

        // 价格以服务端配置为准，避免客户端篡改
        $values = SettingModel::getItem('taxi');
        if (empty($values)) {
            $values = (new SettingModel)->defaultData()['taxi']['values'] ?? [];
        }
        if (isset($values['enable']) && (int)$values['enable'] !== 1) {
            return $this->renderError('出租车服务暂未开启');
        }
        $price = null;
        $airports = $values['airports'] ?? [];
        foreach ($airports as $item) {
            if (isset($item['name']) && $item['name'] === $airportName) {
                $price = $carType === 1 ? $item['price_business'] : $item['price_normal'];
                break;
            }
        }
        if ($price === null || $price === '') {
            return $this->renderError('该机场暂未配置价格，请联系客服');
        }

        $model = new TaxiOrderModel;
        $orderId = $model->add([
            'user_id'        => $user['user_id'],
            'airport_name'   => $airportName,
            'car_type'       => $carType,
            'price'          => $price,
            'currency'       => $values['currency'] ?? 'RM',
            'flight_no'      => $flightNo,
            'departure_time' => trim((string)$request->param('departure_time', '')),
            'takeoff_time'   => trim((string)$request->param('takeoff_time', '')),
            'landing_time'   => trim((string)$request->param('landing_time', '')),
            'hotel_name'     => trim((string)$request->param('hotel_name', '')),
            'hotel_address'  => trim((string)$request->param('hotel_address', '')),
            'contact_name'   => trim((string)$request->param('contact_name', '')),
            'contact_phone'  => trim((string)$request->param('contact_phone', '')),
            'remark'         => trim((string)$request->param('remark', '')),
            'wxapp_id'       => BaseModel::getWxappId(),
        ]);
        if (!$orderId) {
            return $this->renderError($model->getError() ?: '提交失败，请稍后重试');
        }
        return $this->renderSuccess([
            'order_id' => $orderId,
        ], '预约提交成功');
    }

    /**
     * 我的出租车订单
     */
    public function orders()
    {
        $user = $this->getUser();
        $list = TaxiOrderModel::getListByUser($user['user_id'], BaseModel::getWxappId());
        return $this->renderSuccess(compact('list'));
    }
}
