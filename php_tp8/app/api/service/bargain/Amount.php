<?php

namespace app\api\service\bargain;

/**
 * 砍价金额预分配（复现 php_my api/service/bargain/Amount.php）
 *
 * 设计：发起砍价时一次性生成 $peoples 个随机金额（和为 $amount = 原价-底价），
 * shuffle 后存入 task.section；后续每刀按序取 section[cut_people]。
 * 即「预分配 lottery」，非实时计算，保证总额恒定且每人金额确定。
 */
class Amount
{
    private $amount;
    private $num;
    private $couponMin;
    private $items = [];

    public function __construct($amount, $num = 1, $couponMin = 0.01)
    {
        $this->amount = (float)$amount;
        $this->num = (int)$num;
        $this->couponMin = (float)$couponMin;
    }

    /**
     * @return array 金额序列（已 shuffle）
     * @throws \Exception
     */
    public function handle()
    {
        if ($this->amount < ($valid = $this->couponMin * $this->num)) {
            throw new \Exception('砍价总金额必须≥' . $valid . '元');
        }
        $this->apportion();
        return $this->items;
    }

    private function apportion()
    {
        $num = $this->num;
        $amount = $this->amount;
        while ($num >= 1) {
            if ($num == 1) {
                $coupon = $this->decimalNumber($amount);
            } else {
                $avg = $this->decimalNumber($amount / $num);
                $coupon = $this->decimalNumber($this->calcCouponAmount($avg, $amount, $num));
            }
            $this->items[] = $coupon;
            $amount = $this->decimalNumber($amount - $coupon);
            --$num;
        }
        shuffle($this->items);
    }

    private function calcCouponAmount($avg, $amount, $num)
    {
        if ($avg <= $this->couponMin) {
            return $this->couponMin;
        }
        $coupon = $this->decimalNumber($avg * (1 + $this->apportionRandRatio()));
        if ($coupon < $this->couponMin || $coupon > $this->calcCouponAmountMax($amount, $num)) {
            return $this->calcCouponAmount($avg, $amount, $num);
        }
        return $coupon;
    }

    private function calcCouponAmountMax($amount, $num)
    {
        return $this->couponMin + $amount - $num * $this->couponMin;
    }

    private function apportionRandRatio()
    {
        // 60% 概率大幅浮动(±70%)，其余 ±30%
        if (rand(1, 100) <= 60) {
            return rand(-70, 70) / 100;
        }
        return rand(-30, 30) / 100;
    }

    private function decimalNumber($amount)
    {
        return (float)sprintf('%01.2f', round($amount, 2));
    }
}
