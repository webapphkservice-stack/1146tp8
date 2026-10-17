<?php

namespace app\api\model\store;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商家门店（自提点）模型（轻量 Db 实现，移植自 php_my api/model/store/Shop）
 *
 * RN 调用点：shopApi.lists() -> shop/lists（自提点选择）
 *           shopApi.detail() -> shop/detail
 */
class Shop extends BaseModel
{
    protected $name = 'store_shop';

    /**
     * 获取门店列表
     * @param null $isCheck 是否仅返回支持自提核销的门店（1）
     * @param string $longitude 用户经度
     * @param string $latitude 用户纬度
     * @param bool $limit 限制条数
     */
    public static function getList($isCheck = null, $longitude = '', $latitude = '', $limit = false)
    {
        $query = Db::name('store_shop')
            ->where('is_delete', 0)
            ->where('status', 1)
            ->where('wxapp_id', self::getWxappId());
        if ($isCheck) {
            $query->where('is_check', '=', $isCheck);
        }
        if ($limit !== false) {
            $query->limit($limit);
        }
        $rows = $query->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->select()->toArray();
        if (!empty($longitude) && !empty($latitude)) {
            $rows = self::sortByDistance($rows, $longitude, $latitude);
        }
        return array_map([self::class, 'format'], $rows);
    }

    /**
     * 门店详情
     */
    public static function detail($shopId)
    {
        if ($shopId <= 0) {
            return [];
        }
        $row = Db::name('store_shop')
            ->where('shop_id', $shopId)
            ->where('is_delete', 0)
            ->where('status', 1)
            ->where('wxapp_id', self::getWxappId())
            ->find();
        return $row ? self::format($row) : [];
    }

    /**
     * 统一字段格式化
     */
    public static function format($shop)
    {
        $distance = $shop['distance'] ?? 0;
        $distanceUnit = $distance >= 1000
            ? bcdiv($distance, 1000, 2) . 'km'
            : $distance . 'm';
        return [
            'shop_id'     => $shop['shop_id'],
            'shop_name'   => $shop['shop_name'],
            'logo'        => BaseModel::getFileUrl($shop['logo_image_id']),
            'linkman'     => $shop['linkman'],
            'phone'       => $shop['phone'],
            'shop_hours'  => $shop['shop_hours'],
            'address'     => $shop['address'],
            'longitude'   => $shop['longitude'],
            'latitude'    => $shop['latitude'],
            'summary'     => $shop['summary'],
            'is_check'    => $shop['is_check'],
            'distance'    => $distance,
            'distance_unit' => $distanceUnit,
        ];
    }

    /**
     * 根据距离排序（近的在前）
     */
    private static function sortByDistance($rows, $longitude, $latitude)
    {
        $sortArr = [];
        foreach ($rows as &$shop) {
            $shop['distance'] = self::getDistance($longitude, $latitude, $shop['longitude'], $shop['latitude']);
            $sortArr[] = $shop['distance'];
        }
        array_multisort($sortArr, SORT_ASC, $rows);
        return $rows;
    }

    /**
     * 计算两个坐标点距离（米）
     */
    private static function getDistance($ulon, $ulat, $slon, $slat)
    {
        if (!$ulon || !$ulat || !$slon || !$slat) {
            return 0;
        }
        $radLat1 = deg2rad((float)$ulat);
        $radLat2 = deg2rad((float)$slat);
        $radLng1 = deg2rad((float)$ulon);
        $radLng2 = deg2rad((float)$slon);
        $s = acos(cos($radLat1) * cos($radLat2) * cos($radLng1 - $radLng2)
            + sin($radLat1) * sin($radLat2)) * 6378137;
        return round($s * 10000) / 10000;
    }
}
