<?php

namespace app\api\service\dealer;

use think\facade\Db;
use think\facade\Log;

/**
 * 分销订单创建服务
 *
 * 职责：在订单支付成功时（OrderStateMachine::paySuccess 事务内），
 * 按推荐链和佣金规则写入 mybaixin_dealer_order 记录。
 * 佣金实际发放由定时任务 order:cron settled 完成，此处仅做"记账"。
 *
 * 为什么放在独立类：
 *   - 与 sharp/PaySuccess 保持同级结构，dispatchPaySuccess 里按来源分派
 *   - 方法必须是 static（OrderStateMachine::paySuccess 的事务闭包内无 $this）
 *   - 失败不能阻断支付成功，故所有异常在本方法内捕获后只记日志
 */
class OrderService
{
    /**
     * 支付成功后创建分销订单记录
     *
     * @param array $order 订单行（需含 order_id / order_no / user_id / order_price / wxapp_id）
     * @return void 无返回值（失败静默降级，不阻断支付）
     */
    public static function createOnPaySuccess(array $order)
    {
        try {
            $wxappId = (int)($order['wxapp_id'] ?? 0);
            if ($wxappId <= 0) {
                return;
            }

            // ---- 1. 幂等 + 分销开关 ----
            if (self::exists((int)$order['order_id'], $wxappId)) {
                return;
            }
            $basic = self::getSetting('basic', $wxappId);
            if (empty($basic['is_open'])) {
                return; // 分销未开启
            }

            // ---- 2. 找推荐链 ----
            $selfBuy = (int)($basic['self_buy'] ?? 1);
            $maxLevel = (int)($basic['level'] ?? 2); // 1=一级分销 2=二级分销 3=三级分销
            $chain = self::getRefereeChain((int)$order['user_id'], $wxappId, $selfBuy, $maxLevel);
            if (empty($chain)) {
                return; // 无推荐链，不生成分销记录
            }

            // ---- 3. 计算每级佣金 ----
            $commission = self::getSetting('commission', $wxappId);
            $goodsList = Db::name('order_goods')
                ->where('order_id', $order['order_id'])
                ->select()->toArray();
            $money = self::calcCommission($goodsList, $commission, count($chain));

            // 佣金全 0 不生成
            if ($money['first_money'] + $money['second_money'] + $money['third_money'] <= 0) {
                return;
            }

            // ---- 4. 写入 dealer_order ----
            $now = time();
            $data = [
                'user_id'       => (int)$order['user_id'],
                'order_id'      => (int)$order['order_id'],
                'order_type'    => 10,
                'order_no'      => $order['order_no'],
                'order_price'   => (float)$order['order_price'],
                'first_user_id'  => $chain[0] ?? 0,
                'second_user_id' => $chain[1] ?? 0,
                'third_user_id'  => $chain[2] ?? 0,
                'first_money'   => $money['first_money'],
                'second_money'  => $money['second_money'],
                'third_money'   => $money['third_money'],
                'is_invalid'    => 0,
                'is_settled'    => 0,
                'settle_time'   => 0,
                'wxapp_id'      => $wxappId,
                'create_time'   => $now,
                'update_time'   => $now,
            ];
            Db::name('dealer_order')->insert($data);
        } catch (\Throwable $e) {
            Log::write('分销订单创建失败[' . ($order['order_no'] ?? '') . ']：' . $e->getMessage(), 'error');
        }
    }

    /**
     * dealer_order 是否已存在（幂等）
     */
    private static function exists(int $orderId, int $wxappId): bool
    {
        return Db::name('dealer_order')
            ->where('order_id', $orderId)
            ->where('order_type', 10)
            ->where('wxapp_id', $wxappId)
            ->count() > 0;
    }

    /**
     * 读分销设置（单 key）
     */
    private static function getSetting(string $key, int $wxappId): array
    {
        $row = Db::name('dealer_setting')
            ->where('wxapp_id', $wxappId)
            ->where('key', $key)
            ->find();
        return $row ? (json_decode($row['values'], true) ?: []) : [];
    }

    /**
     * 查推荐链：从下单用户向上找推荐人（最多 3 级）
     *
     * dealer_referee 表结构：
     *   dealer_id = 推荐人（分销商 user_id）
     *   user_id   = 被推荐人
     *
     * @param int $userId     下单用户 id
     * @param int $wxappId
     * @param int $selfBuy    1=自购也算（下单者本身是分销商时算一级）
     * @param int $maxLevel   分销层级上限
     * @return array [一级分销商, 二级分销商, 三级分销商]
     */
    private static function getRefereeChain(int $userId, int $wxappId, int $selfBuy, int $maxLevel): array
    {
        $chain = [];
        $current = $userId;

        // 自购：下单者本身是分销商 → 自己当一级
        if ($selfBuy === 1 && self::isDealer($userId, $wxappId)) {
            $chain[] = $userId;
            $maxLevel = min($maxLevel + 1, 3); // 自购额外占一级
        }

        while (count($chain) < min($maxLevel, 3)) {
            $ref = Db::name('dealer_referee')
                ->where('user_id', $current)
                ->where('wxapp_id', $wxappId)
                ->find();
            if (empty($ref) || (int)$ref['dealer_id'] <= 0) {
                break;
            }
            $dealerId = (int)$ref['dealer_id'];
            if (!self::isDealer($dealerId, $wxappId)) {
                break; // 推荐人必须是有效分销商
            }
            if (in_array($dealerId, $chain)) {
                break; // 防止循环
            }
            $chain[] = $dealerId;
            $current = $dealerId;
        }

        // 截断到最多 3 级
        return array_slice($chain, 0, 3);
    }

    /**
     * 是否有效分销商
     */
    private static function isDealer(int $userId, int $wxappId): bool
    {
        return Db::name('dealer_user')
            ->where('user_id', $userId)
            ->where('wxapp_id', $wxappId)
            ->where('is_delete', 0)
            ->count() > 0;
    }

    /**
     * 计算每级佣金
     *
     * 规则：
     *   - 优先用商品独立佣金（goods.is_ind_dealer=1 时 goods.first_money/second_money/third_money，
     *     单位是"元"而不是百分比，独立佣金为 0 视为未设置）
     *   - 商品没有独立佣金时回退全局比例（dealer_setting.commission 的 first_money/second_money/third_money，
     *     单位是百分比，按 order_goods 的 line_price × num 计算）
     *
     * @param array $goodsList 订单商品行
     * @param array $commission 全局佣金比例（commission.first_money 等，值是 "10" 表示 10%）
     * @param int   $chainCount 实际推荐链深度（用于控制哪些级有佣金）
     * @return array ['first_money'=>float, 'second_money'=>float, 'third_money'=>float]
     */
    private static function calcCommission(array $goodsList, array $commission, int $chainCount): array
    {
        $firstRate  = (float)($commission['first_money'] ?? 0);
        $secondRate = (float)($commission['second_money'] ?? 0);
        $thirdRate  = (float)($commission['third_money'] ?? 0);

        $first  = 0.0;
        $second = 0.0;
        $third  = 0.0;

        foreach ($goodsList as $g) {
            $goods = Db::name('goods')->where('goods_id', $g['goods_id'])->find();
            $goodsPrice = (float)$g['goods_price'];
            $totalNum   = (int)$g['total_num'];
            $lineAmount = round($goodsPrice * $totalNum, 2);

            // 商品独立佣金
            if (!empty($goods) && (int)($goods['is_ind_dealer'] ?? 0) === 1) {
                $indFirst  = round((float)($goods['first_money'] ?? 0) * $totalNum, 2);
                $indSecond = round((float)($goods['second_money'] ?? 0) * $totalNum, 2);
                $indThird  = round((float)($goods['third_money'] ?? 0) * $totalNum, 2);

                // 独立佣金有效则用独立的（>0 视为已设置），否则回退全局
                if ($indFirst > 0)  $first  += $indFirst;
                else                $first  += round($lineAmount * $firstRate / 100, 2);
                if ($indSecond > 0) $second += $indSecond;
                else                $second += round($lineAmount * $secondRate / 100, 2);
                if ($indThird > 0)  $third  += $indThird;
                else                $third  += round($lineAmount * $thirdRate / 100, 2);
            } else {
                $first  += round($lineAmount * $firstRate / 100, 2);
                $second += round($lineAmount * $secondRate / 100, 2);
                $third  += round($lineAmount * $thirdRate / 100, 2);
            }
        }

        // 根据实际推荐链深度裁剪
        if ($chainCount < 2) $second = 0;
        if ($chainCount < 3) $third  = 0;

        return [
            'first_money'  => round($first, 2),
            'second_money' => round($second, 2),
            'third_money'  => round($third, 2),
        ];
    }
}
