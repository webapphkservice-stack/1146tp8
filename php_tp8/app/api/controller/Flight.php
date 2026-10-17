<?php

namespace app\api\controller;

use app\common\model\Flight as FlightModel;
use app\common\model\Setting;
use app\api\service\FlightSearch;

/**
 * 航班查询接口（带数据库缓存，减少 Bright Data 额度消耗）
 * Class Flight
 * @package app\api\controller
 *
 * RN 调用点：flightApi.search -> flight/search；flightApi.defaults -> flight/defaults
 */
class Flight extends Controller
{
    /**
     * 搜索航班
     * 命中顺序：后台默认航班 -> 接口缓存 -> 调用 Bright Data
     */
    public function search()
    {
        $param = $this->request->param();
        $wxappId = \app\common\model\BaseModel::getWxappId();

        $origin = strtoupper(trim($param['origin'] ?? ''));
        $destination = strtoupper(trim($param['destination'] ?? ''));
        $departureDate = trim($param['date'] ?? '');
        $returnDate = !empty($param['return_date']) ? trim($param['return_date']) : '';
        $cabin = (int)($param['cabin'] ?? 1);
        $adult = (int)($param['adult'] ?? 1);

        // 货币：优先取请求参数，否则取后台配置
        $setting = Setting::getItem('brightdata');
        $currency = trim($param['currency'] ?? ($setting['currency'] ?? 'CNY'));
        $currency = $currency ?: 'CNY';

        if (!$origin || !$destination || !$departureDate) {
            return $this->renderError('请填写出发地、目的地与出发日期');
        }

        $search = [
            'origin'         => $origin,
            'destination'    => $destination,
            'departure_date' => $departureDate,
            'return_date'    => $returnDate,
            'trip_type'      => $returnDate ? 2 : 1,
            'cabin'          => $cabin ?: 1,
            'currency'       => $currency,
            'adult'          => $adult ?: 1,
        ];

        $route = [
            'origin'         => $search['origin'],
            'destination'    => $search['destination'],
            'departure_date' => $search['departure_date'],
            'return_date'    => $search['return_date'],
            'trip_type'      => $search['trip_type'],
            'cabin'          => $search['cabin'],
            'currency'       => $search['currency'],
            'adult'          => $search['adult'],
        ];

        // 1. 命中缓存 / 后台默认
        if ($row = FlightModel::getMatch($search)) {
            FlightModel::incHits($row['flight_id']);
            return $this->renderSuccess([
                'source'  => $row['source'] == 1 ? 'default' : 'cache',
                'route'   => $route,
                'title'   => $row['title'],
                'flights' => FlightModel::normalizeFlights($row['data']),
            ]);
        }

        // 2. 调用 Bright Data 接口
        $service = new FlightSearch($wxappId);
        if (!$service->isEnabled()) {
            return $this->renderError('暂无该航线参考航班，请联系管理员在后台添加默认航班或配置接口');
        }

        try {
            $result = $service->search($search);
        } catch (\Exception $e) {
            return $this->renderError('航班查询失败：' . $e->getMessage());
        }

        // 3. 写入接口缓存
        FlightModel::saveCache($search, $result['flights'], (int)($service->getConfig()['expire_hours'] ?? 168));

        return $this->renderSuccess([
            'source'  => 'api',
            'route'   => $route,
            'title'   => '',
            'flights' => $result['flights'],
        ]);
    }

    /**
     * 获取后台默认航班（供查询页直接展示）
     */
    public function defaults()
    {
        $row = FlightModel::getDefaultRow();
        if (!$row) {
            return $this->renderSuccess(['flight' => null]);
        }
        return $this->renderSuccess([
            'flight' => [
                'flight_id'      => $row['flight_id'],
                'title'          => $row['title'],
                'flight_no'      => $row['flight_no'] ?? '',
                'origin'         => $row['origin'],
                'destination'    => $row['destination'],
                'departure_date' => $row['departure_date'],
                'cabin'          => $row['cabin'],
                'currency'       => $row['currency'],
                'adult'          => $row['adult'],
                'flights'        => FlightModel::normalizeFlights($row['data']),
            ],
        ]);
    }
}
