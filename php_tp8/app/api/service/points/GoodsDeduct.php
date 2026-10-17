<?php
namespace app\api\service\points;

use app\common\library\helper;
use app\common\model\Setting as SettingModel;

/**
 * 积分抵扣分摊（移植自 php_my api/service/points/GoodsDeduct）
 *
 * 作用：把「本次实际使用的积分数量」按各商品的最大可抵扣积分数占比分摊到每个商品，
 *      再按积分抵扣比例（discount_ratio）换算成每个商品的抵扣金额。
 *
 * 与优惠券分摊一样存在整除余数问题，故同样采用「按比例分摊 → 填充 → 补差」三步，
 * 保证各商品 points_num 之和严格等于实际使用的积分数量。
 */
class GoodsDeduct
{
    /** @var array 商品列表 */
    private $goodsList;

    /**
     * @param array $goodsList 商品列表，需已填充 is_points_discount 与 max_points_num
     */
    public function __construct($goodsList)
    {
        $this->goodsList = $goodsList;
    }

    /**
     * @param int $maxPointsNumCount 订单最多可抵扣积分总数
     * @param int $actualPointsNum   实际可抵扣积分数量
     * @return array 已写入 points_num / points_money 的商品列表
     */
    public function setGoodsPoints($maxPointsNumCount, $actualPointsNum)
    {
        // 按各商品最大可抵扣积分占比，分配实际使用的积分数量
        $this->setGoodsListPointsNum($maxPointsNumCount, $actualPointsNum);
        // 总抵扣数量
        $totalPointsNum = $this->getTotalPointsNum();
        // 若全部按占比分摊后为 0，则按 1 分/件填充；再把余数逐件补齐
        $this->setGoodsListPointsNumFill($actualPointsNum, $totalPointsNum);
        $totalPointsNum = $this->getTotalPointsNum();
        $this->setGoodsListPointsNumDiff($actualPointsNum, $totalPointsNum);
        // 按积分抵扣比例换算成抵扣金额
        $this->setGoodsListPointsMoney();
        return $this->goodsList;
    }

    public function getGoodsList()
    {
        return $this->goodsList;
    }

    private function setGoodsListPointsNum($maxPointsNumCount, $actualPointsNum)
    {
        foreach ($this->goodsList as &$goods) {
            if (empty($goods['is_points_discount'])) {
                continue;
            }
            $goods['points_num'] = $maxPointsNumCount > 0
                ? (int)floor($goods['max_points_num'] / $maxPointsNumCount * $actualPointsNum)
                : 0;
        }
        unset($goods);
    }

    private function setGoodsListPointsMoney()
    {
        $setting = SettingModel::getItem('points');
        $ratio = isset($setting['discount']['discount_ratio']) ? $setting['discount']['discount_ratio'] : 0;
        foreach ($this->goodsList as &$goods) {
            if (empty($goods['is_points_discount'])) {
                continue;
            }
            $goods['points_money'] = helper::number2(helper::bcmul($goods['points_num'], $ratio));
        }
        unset($goods);
    }

    private function setGoodsListPointsNumFill($actualPointsNum, $totalPointsNum)
    {
        if ($totalPointsNum === 0) {
            $temPointsNum = $actualPointsNum;
            foreach ($this->goodsList as &$goods) {
                if (empty($goods['is_points_discount'])) {
                    continue;
                }
                if ($temPointsNum === 0) {
                    break;
                }
                $goods['points_num'] = 1;
                $temPointsNum--;
            }
            unset($goods);
        }
        return true;
    }

    private function setGoodsListPointsNumDiff($actualPointsNum, $totalPointsNum)
    {
        $tempDiff = $actualPointsNum - $totalPointsNum;
        foreach ($this->goodsList as &$goods) {
            if (empty($goods['is_points_discount'])) {
                continue;
            }
            if ($tempDiff < 1) {
                break;
            }
            $goods['points_num'] = $goods['points_num'] + 1;
            $tempDiff--;
        }
        unset($goods);
        return true;
    }

    private function getTotalPointsNum()
    {
        $sum = 0;
        foreach ($this->goodsList as $goods) {
            $sum += (int)($goods['points_num'] ?? 0);
        }
        return $sum;
    }
}
