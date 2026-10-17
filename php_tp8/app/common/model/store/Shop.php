<?php

namespace app\common\model\store;

use app\common\model\BaseModel;
use app\common\model\Region as RegionModel;

/**
 * 商家门店模型
 * Class Shop
 * @package app\common\model\store
 */
class Shop extends BaseModel
{
    protected $name = 'store_shop';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['region'];

    /**
     * 关联门店logo
     * @return \think\model\relation\HasOne
     */
    public function logo()
    {
        return $this->hasOne('app\common\model\UploadFile', 'file_id', 'logo_image_id');
    }

    /**
     * 地区名称（省/市/区）
     * @param $value
     * @param $data
     * @return array
     */
    public function getRegionAttr($value, $data)
    {
        return [
            'province' => RegionModel::getNameById($data['province_id'] ?? 0),
            'city'     => RegionModel::getNameById($data['city_id'] ?? 0),
            'region'   => ($data['region_id'] ?? 0) == 0 ? '' : RegionModel::getNameById($data['region_id']),
        ];
    }

    /**
     * 门店详情
     * @param $shop_id
     * @return static|null
     */
    public static function detail($shop_id)
    {
        return static::with(['logo'])->find($shop_id);
    }
}
