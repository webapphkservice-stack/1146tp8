<?php

namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class OrderAddress extends BaseModel
{
    protected $name = 'order_address';
    protected $updateTime = false;

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];

    /**
     * 地区名称
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        // 马来西亚：无三级地区，直接展示州/城市
        if (isset($data['country']) && $data['country'] === 'MY') {
            return [
                'province' => $data['state'],
                'city' => $data['city'],
                'region' => '',
            ];
        }
        return [
            'province' => Region::getNameById($data['province_id']),
            'city' => Region::getNameById($data['city_id']),
            'region' => $data['region_id'] == 0 ? '' : Region::getNameById($data['region_id']),
        ];
    }

    /**
     * 获取完整地址
     * @return string
     */
    public function getFullAddress()
    {
        return $this['region']['province'] . $this['region']['city'] . $this['region']['region'] . $this['detail'];
    }

}
