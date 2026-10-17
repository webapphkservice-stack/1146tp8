<?php

namespace app\common\model;

/**
 * 用户优惠券模型（领取记录，冗余存储优惠券信息）
 * Class UserCoupon
 * @package app\common\model
 */
class UserCoupon extends BaseModel
{
    protected $name = 'user_coupon';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['state'];

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\User');
    }

    /**
     * 优惠券状态
     * @param $value
     * @param $data
     * @return array
     */
    public function getStateAttr($value, $data)
    {
        if ($data['is_use']) {
            return ['text' => '已使用', 'value' => 0];
        }
        if ($data['is_expire']) {
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
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 修改器：适用范围配置
     * @param $array
     * @return mixed
     */
    public function setApplyRangeConfigAttr($array)
    {
        return json_encode((array)$array, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 优惠券详情
     * @param $userCouponId
     * @return null|static
     */
    public static function detail($userCouponId)
    {
        return static::find($userCouponId);
    }
}
