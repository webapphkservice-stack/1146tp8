<?php

namespace app\h5\controller;

use app\common\model\Flight as FlightModel;
use app\common\model\Setting as SettingModel;
use app\api\service\FlightSearch;

/**
 * 手机网站 - 航班查询
 *
 * 访问路径：/pages/flight/index（app_map: pages => h5）或 /h5/flight/index。
 * 查询命中顺序与 api/flight/search 一致：后台默认/接口缓存 -> Bright Data。
 */
class Flight extends Controller
{
    public function index()
    {
        $origin      = strtoupper(trim($this->request->param('origin', '')));
        $destination = strtoupper(trim($this->request->param('destination', '')));
        $date        = trim($this->request->param('date', ''));
        $cabin       = (int)$this->request->param('cabin', 1) ?: 1;
        $searched    = $origin !== '' && $destination !== '' && $date !== '';

        $flights = [];
        $title   = '';
        $source  = '';
        $error   = '';

        if ($searched) {
            // 与 api/Flight::search 相同的参数组装与命中顺序
            $setting = SettingModel::getItem('brightdata');
            $search  = [
                'origin'         => $origin,
                'destination'    => $destination,
                'departure_date' => $date,
                'return_date'    => '',
                'trip_type'      => 1,
                'cabin'          => $cabin,
                'currency'       => $setting['currency'] ?? 'CNY',
                'adult'          => 1,
            ];

            if ($row = FlightModel::getMatch($search)) {
                FlightModel::incHits($row['flight_id']);
                $source  = $row['source'] == 1 ? 'default' : 'cache';
                $title   = (string)$row['title'];
                $flights = FlightModel::normalizeFlights($row['data']);
            } else {
                $service = new FlightSearch(\app\common\model\BaseModel::getWxappId());
                if (!$service->isEnabled()) {
                    $error = '暂无该航线参考航班，请联系管理员在后台添加默认航班';
                } else {
                    try {
                        $result  = $service->search($search);
                        FlightModel::saveCache(
                            $search,
                            $result['flights'],
                            (int)($service->getConfig()['expire_hours'] ?? 168)
                        );
                        $source  = 'api';
                        $flights = $result['flights'];
                    } catch (\Throwable $e) {
                        $error = '航班查询失败，请稍后再试';
                    }
                }
            }
        } else {
            // 未查询：直接展示后台默认航班（与 api/flight/defaults 一致）
            if ($row = FlightModel::getDefaultRow()) {
                $origin      = (string)$row['origin'];
                $destination = (string)$row['destination'];
                $date        = (string)$row['departure_date'];
                $cabin       = (int)($row['cabin'] ?: 1);
                $title       = (string)$row['title'];
                $source      = 'default';
                $flights     = FlightModel::normalizeFlights($row['data']);
            }
        }

        return $this->fetch('flight/index', [
            'origin'      => $origin,
            'destination' => $destination,
            'date'        => $date,
            'cabin'       => $cabin,
            'flights'     => $flights,
            'title'       => $title,
            'source'      => $source,
            'error'       => $error,
            'searched'    => $searched,
            'storeName'   => $this->storeName(),
        ]);
    }
}
