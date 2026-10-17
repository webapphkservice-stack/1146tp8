<?php
namespace app\common\enum\order;

/**
 * 门店配送（送水）订单状态
 * 10 待接单 / 20 配送中 / 30 已完成 / 40 已取消
 * 对齐 delta 脚本 order.shop_order_status 的默认值与含义。
 */
class ShopOrderStatus
{
    // 待接单
    const WAIT_ACCEPT = 10;
    // 配送中
    const DELIVERING = 20;
    // 已完成
    const COMPLETED = 30;
    // 已取消
    const CANCELLED = 40;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::WAIT_ACCEPT => ['name' => '待接单', 'value' => self::WAIT_ACCEPT],
            self::DELIVERING  => ['name' => '配送中', 'value' => self::DELIVERING],
            self::COMPLETED   => ['name' => '已完成', 'value' => self::COMPLETED],
            self::CANCELLED   => ['name' => '已取消', 'value' => self::CANCELLED],
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
