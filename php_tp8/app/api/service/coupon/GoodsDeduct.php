<?php
namespace app\api\service\coupon;

use app\common\library\helper;

/**
 * 优惠券抵扣金额分摊（移植自 php_my api/service/coupon/GoodsDeduct）
 *
 * 作用：把「整张优惠券的抵扣金额」按各商品的金额权重分摊到每个商品上，
 *      分摊结果以「分」为单位（整数），分摊完再各自除 100 转回元。
 *
 * 为什么必须用分为单位：按权重分摊必然产生小数，若直接用元做四舍五入，
 * 累计分摊额会与券面额对不上（多扣或少扣用户钱）。改为以分为单位做取整，
 * 再把余数逐件补到各商品上，可保证 sum(各商品分摊) === 券实际抵扣额。
 *
 * 修正的原版缺陷：
 *   php_my 的 setGoodsListCouponMoneyDiff() 写成 `$goods['coupon_money']++ && $tempDiff--;`
 *   当 $goods['coupon_money'] 为 0 时，后置 ++ 返回 0（falsy），&& 短路导致 $tempDiff-- 不执行，
 *   余数永远分不出去 → 分摊总额少于券面额。此处改为两条独立语句。
 */
class GoodsDeduct
{
    /** @var int 实际抵扣金额（单位：分） */
    private $actualReducedMoney = 0;

    /**
     * 设置商品优惠券抵扣金额
     * @param array $goodsList    商品列表（需含 total_price，单位元）
     * @param float $reducedMoney 券面抵扣金额（单位元）
     * @return array 已写入 coupon_money（单位分）的商品列表
     */
    public function setGoodsCouponMoney($goodsList, $reducedMoney)
    {
        // 统计订单商品总金额（单位分）
        $orderTotalPrice = 0;
        foreach ($goodsList as &$goods) {
            $goods['total_price'] = (int)round($goods['total_price'] * 100);
            $orderTotalPrice += $goods['total_price'];
        }
        unset($goods);
        // 计算实际抵扣金额
        $this->setActualReducedMoney($reducedMoney, $orderTotalPrice);
        // 实际抵扣金额为 0 时无需分摊
        if ($this->actualReducedMoney > 0) {
            // 计算商品价格权重
            $goodsList = $this->getGoodsListWeight($goodsList, $orderTotalPrice);
            // 计算各商品优惠券抵扣金额
            $this->setGoodsListCouponMoney($goodsList);
            // 总分摊金额
            $totalCouponMoney = $this->getTotalCouponMoney($goodsList);
            // 若权重过小导致全部分摊为 0，则按 1 分/件强制填充
            $this->setGoodsListCouponMoneyFill($goodsList, $totalCouponMoney);
            // 把整除产生的余数逐件补齐，保证总额严格等于券面额
            $totalCouponMoney = $this->getTotalCouponMoney($goodsList);
            $this->setGoodsListCouponMoneyDiff($goodsList, $totalCouponMoney);
        }
        return $goodsList;
    }

    /**
     * 实际抵扣金额（单位：分）
     * 注意：不能超过订单总额，且至少留 1 分（订单不能为 0 元）
     */
    public function getActualReducedMoney()
    {
        return $this->actualReducedMoney;
    }

    private function setActualReducedMoney($reducedMoney, $orderTotalPrice)
    {
        $reducedMoney = (int)round($reducedMoney * 100);
        $this->actualReducedMoney = ($reducedMoney >= $orderTotalPrice)
            ? max(0, $orderTotalPrice - 1)
            : $reducedMoney;
    }

    private function getGoodsListWeight($goodsList, $orderTotalPrice)
    {
        foreach ($goodsList as &$goods) {
            $goods['weight'] = $orderTotalPrice > 0 ? $goods['total_price'] / $orderTotalPrice : 0;
        }
        unset($goods);
        return helper::arraySort($goodsList, 'weight', true);
    }

    private function setGoodsListCouponMoney(&$goodsList)
    {
        foreach ($goodsList as &$goods) {
            $goods['coupon_money'] = (int)helper::bcmul($this->actualReducedMoney, $goods['weight'], 0);
        }
        unset($goods);
        return true;
    }

    /**
     * 总分摊金额（单位分，用整数累加避免浮点误差）
     */
    private function getTotalCouponMoney($goodsList)
    {
        $sum = 0;
        foreach ($goodsList as $goods) {
            $sum += (int)$goods['coupon_money'];
        }
        return $sum;
    }

    private function setGoodsListCouponMoneyFill(&$goodsList, $totalCouponMoney)
    {
        if ($totalCouponMoney === 0) {
            $temReducedMoney = $this->actualReducedMoney;
            foreach ($goodsList as &$goods) {
                if ($temReducedMoney === 0) {
                    break;
                }
                $goods['coupon_money'] = 1;
                $temReducedMoney--;
            }
            unset($goods);
        }
        return true;
    }

    private function setGoodsListCouponMoneyDiff(&$goodsList, $totalCouponMoney)
    {
        $tempDiff = $this->actualReducedMoney - $totalCouponMoney;
        foreach ($goodsList as &$goods) {
            if ($tempDiff < 1) {
                break;
            }
            // 修正：原版写成 `$goods['coupon_money']++ && $tempDiff--`，
            // coupon_money 为 0 时 && 短路，余数分不出去，导致分摊总额少于券面额。
            $goods['coupon_money']++;
            $tempDiff--;
        }
        unset($goods);
        return true;
    }
}
