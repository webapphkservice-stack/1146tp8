<?php

namespace app\h5\controller;

use app\common\model\Setting as SettingModel;
use app\common\model\TaxiOrder as TaxiOrderModel;
use app\common\model\BaseModel;

/**
 * 手机网站 - 出租车预约
 *
 * 访问路径：/pages/taxi/index（app_map: pages => h5）或 /h5/taxi/index。
 * 机场/价格取自后台设置（setting 表 taxi 项），与 api/Taxi 保持一致；
 * 提交需 h5 登录（未登录跳转登录页）。
 */
class Taxi extends Controller
{
    public function index()
    {
        // 读取出租车设置（与 api 相同的默认值兜底）
        $values = SettingModel::getItem('taxi');
        if (empty($values)) {
            $values = (new SettingModel)->defaultData()['taxi']['values'] ?? [];
        }
        $currency = $values['currency'] ?? 'RM';
        $airports = is_array($values['airports'] ?? null) ? $values['airports'] : [];
        $enabled  = !isset($values['enable']) || (int)$values['enable'] === 1;

        $input = [
            'airport_name'   => trim((string)$this->request->param('airport_name', '')),
            'car_type'       => (int)$this->request->param('car_type', 0),
            'flight_no'      => trim((string)$this->request->param('flight_no', '')),
            'departure_time' => trim((string)$this->request->param('departure_time', '')),
            'takeoff_time'   => trim((string)$this->request->param('takeoff_time', '')),
            'landing_time'   => trim((string)$this->request->param('landing_time', '')),
            'hotel_name'     => trim((string)$this->request->param('hotel_name', '')),
            'hotel_address'  => trim((string)$this->request->param('hotel_address', '')),
            'contact_name'   => trim((string)$this->request->param('contact_name', '')),
            'contact_phone'  => trim((string)$this->request->param('contact_phone', '')),
            'remark'         => trim((string)$this->request->param('remark', '')),
        ];
        if (!in_array($input['car_type'], [0, 1], true)) {
            $input['car_type'] = 0;
        }

        $error   = '';
        $success = '';
        $orderId = 0;

        if ($this->request->isPost() && $enabled) {
            // 校验（与 api/Taxi::submit 一致）
            if ($input['airport_name'] === '') {
                $error = '请选择抵达机场';
            } elseif ($input['flight_no'] === '') {
                $error = '请填写航班号';
            } else {
                // 价格以服务端配置为准，避免客户端篡改
                $price = null;
                foreach ($airports as $item) {
                    if (($item['name'] ?? '') === $input['airport_name']) {
                        $price = $input['car_type'] === 1 ? ($item['price_business'] ?? null) : ($item['price_normal'] ?? null);
                        break;
                    }
                }
                if ($price === null || $price === '') {
                    $error = '该机场暂未配置价格，请联系客服';
                } elseif (!$this->h5User()) {
                    // 需登录：跳转登录页并在登录后回跳
                    return $this->requireLogin();
                } else {
                    $model = new TaxiOrderModel;
                    $input['user_id']  = $this->h5User()['user_id'];
                    $input['price']    = $price;
                    $input['currency'] = $currency;
                    $input['wxapp_id'] = BaseModel::getWxappId();
                    $orderId = $model->add($input);
                    if (!$orderId) {
                        $error = $model->getError() ?: '提交失败，请稍后重试';
                    } else {
                        $success = '预约提交成功';
                    }
                }
            }
        }

        // 当前所选组合的价格（表单展示用）
        $price = '';
        foreach ($airports as $item) {
            if (($item['name'] ?? '') === $input['airport_name']) {
                $price = (string)($input['car_type'] === 1 ? ($item['price_business'] ?? '') : ($item['price_normal'] ?? ''));
                break;
            }
        }

        return $this->fetch('taxi/index', [
            'enabled'  => $enabled,
            'airports' => $airports,
            'currency' => $currency,
            'input'    => $input,
            'price'    => $price,
            'error'    => $error,
            'success'  => $success,
            'orderId'  => $orderId,
            'storeName'=> $this->storeName(),
        ]);
    }
}
