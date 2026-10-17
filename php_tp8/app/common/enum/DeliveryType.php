<?php
namespace app\common\enum;

/**
 * 配送方式枚举（移植自 TP5 common/enum/DeliveryType）
 * 视图中以 DeliveryTypeEnum 别名使用：app\common\enum\DeliveryType
 */
class DeliveryType
{
    // 快递配送
    const EXPRESS = 10;

    // 上门自提
    const EXTRACT = 20;

    // 门店配送（送水）
    const SHOP_DELIVERY = 30;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::EXPRESS => [
                'name'  => '快递配送',
                'value' => self::EXPRESS,
            ],
            self::EXTRACT => [
                'name'  => '上门自提',
                'value' => self::EXTRACT,
            ],
            self::SHOP_DELIVERY => [
                'name'  => '门店配送',
                'value' => self::SHOP_DELIVERY,
            ],
        ];
    }

    /**
     * 获取名称
     * @param int $value
     * @return string
     */
    public static function getName($value)
    {
        $data = self::data();
        return $data[$value]['name'] ?? '未知';
    }
}
