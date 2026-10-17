<?php

namespace app\common\model;

/**
 * 优惠券模型
 * Class Coupon
 * @package app\common\model
 */
class Coupon extends BaseModel
{
    protected $name = 'coupon';

    /**
     * 追加字段
     * @var array
     */
    protected $append = [
        'state'
    ];

    /**
     * 默认数据: 适用范围配置
     * @var array[]
     */
    protected $applyRangeConfig = [
        'applyGoodsIds'    => [],
        'excludedGoodsIds' => []
    ];

    /**
     * 优惠券状态 (是否可领取)
     * @param $value
     * @param $data
     * @return array
     */
    public function getStateAttr($value, $data)
    {
        if (!empty($data['is_receive'])) {
            return ['text' => '已领取', 'value' => 0];
        }
        if ($data['total_num'] > -1 && $data['receive_num'] >= $data['total_num']) {
            return ['text' => '已抢光', 'value' => 0];
        }
        if ($data['expire_type'] == 20 && ($data['end_time'] + 86400) < time()) {
            return ['text' => '已过期', 'value' => 0];
        }
        return ['text' => '', 'value' => 1];
    }

    /**
     * 优惠券颜色
     * @param $value
     * @return mixed
     */
    public function getColorAttr($value)
    {
        $status = [10 => 'blue', 20 => 'red', 30 => 'violet', 40 => 'yellow'];
        return ['text' => $status[$value] ?? '', 'value' => $value];
    }

    /**
     * 优惠券类型
     * @param $value
     * @return mixed
     */
    public function getCouponTypeAttr($value)
    {
        $status = [10 => '满减券', 20 => '折扣券', 30 => '运费券'];
        return ['text' => $status[$value] ?? '', 'value' => $value];
    }

    /**
     * 折扣率（存储值/10）
     * @param $value
     * @return mixed
     */
    public function getDiscountAttr($value)
    {
        return $value / 10;
    }

    /**
     * 有效期-开始时间
     * @param $value
     * @return mixed
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y/m/d', (int)$value), 'value' => (int)$value];
    }

    /**
     * 有效期-结束时间
     * @param $value
     * @return mixed
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y/m/d', (int)$value), 'value' => (int)$value];
    }

    /**
     * 获取器：适用范围配置
     * @param $value
     * @return mixed
     */
    public function getApplyRangeConfigAttr($value)
    {
        $array = $value ? json_decode($value, true) : [];
        return array_merge($this->applyRangeConfig, $array);
    }

    /**
     * 修改器：适用范围配置
     * @param $array
     * @return mixed
     */
    public function setApplyRangeConfigAttr($array)
    {
        return json_encode(array_merge($this->applyRangeConfig, (array)$array), JSON_UNESCAPED_UNICODE);
    }

    /**
     * 修改器：折扣率（存储值 = 输入值*10）
     * @param $value
     * @return mixed
     */
    public function setDiscountAttr($value)
    {
        return (float)$value * 10;
    }

    /**
     * 优惠券详情
     * @param $couponId
     * @return null|static
     */
    public static function detail($couponId)
    {
        return static::find($couponId);
    }
}
