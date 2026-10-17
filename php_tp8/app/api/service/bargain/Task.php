<?php

namespace app\api\service\bargain;

use app\api\service\bargain\Amount;
use think\facade\Db;

/**
 * 砍价任务服务（发起 / 详情 / 帮砍一刀）
 *
 * 并发控制：php_my 用 app/common/library/Lock（TP8 未迁移），此处用
 * Db::transaction + 行锁（lock(true)）替代，保证 help_cut 原子性。
 */
class Task
{
    public static function getMyList($userId)
    {
        $rows = Db::name('bargain_task')
            ->where('user_id', $userId)
            ->where('is_delete', 0)
            ->order('task_id', 'desc')
            ->select()
            ->toArray();
        return array_map([self::class, 'formatTask'], $rows);
    }

    /**
     * 发起砍价：预分配每刀金额（Amount lottery）存入 section
     * @return array ['task_id'=>int] 或 ['error'=>string]
     */
    public static function partake($userId, $activeId, $specSkuId)
    {
        $active = Db::name('bargain_active')
            ->where('active_id', $activeId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->find();
        if (empty($active)) {
            return ['error' => '很抱歉，该砍价活动不存在或已结束'];
        }
        if ((int)$active['end_time'] < time()) {
            return ['error' => '很抱歉，该砍价活动已结束'];
        }
        $sku = Db::name('goods_sku')->where('goods_id', $active['goods_id'])
            ->order('goods_sku_id', 'asc')->find();
        $goodsPrice = $sku ? (float)$sku['goods_price'] : 0;
        $floorPrice = (float)$active['floor_price'];
        $peoples = (int)$active['peoples'];

        // 预分配每刀金额（和为 原价-底价）
        $section = [];
        $amount = $goodsPrice - $floorPrice;
        if ($amount > 0 && $peoples > 0) {
            $section = (new Amount($amount, $peoples))->handle();
        }

        $taskId = Db::name('bargain_task')->insertGetId([
            'active_id'    => $activeId,
            'user_id'     => $userId,
            'goods_id'    => $active['goods_id'],
            'spec_sku_id' => (string)$specSkuId,
            'goods_price' => $goodsPrice,
            'floor_price' => $floorPrice,
            'peoples'     => $peoples,
            'cut_people'  => 0,
            'section'     => json_encode($section),
            'cut_money'   => 0.00,
            'actual_price'=> $goodsPrice,
            'is_floor'    => 0,
            'end_time'    => time() + (int)$active['expiryt_time'],
            'is_buy'      => 0,
            'status'      => 1,
            'is_delete'   => 0,
            'wxapp_id'    => $active['wxapp_id'],
            'create_time' => time(),
            'update_time' => time(),
        ]);
        return ['task_id' => $taskId];
    }

    public static function getTaskDetail($taskId)
    {
        $task = Db::name('bargain_task')
            ->where('task_id', $taskId)
            ->where('is_delete', 0)
            ->find();
        if (empty($task)) {
            return false;
        }
        return self::formatTask($task, true);
    }

    /**
     * 任务格式化（含虚拟字段 surplus_money / cut_rate）
     * @param bool $withHelp 是否附带帮砍记录
     */
    public static function formatTask($task, $withHelp = false)
    {
        $goodsPrice = (float)$task['goods_price'];
        $floorPrice = (float)$task['floor_price'];
        $cutMoney = (float)$task['cut_money'];
        $surplus = ($goodsPrice - $floorPrice) - $cutMoney;
        $rate = ($goodsPrice - $floorPrice) > 0
            ? ($cutMoney / ($goodsPrice - $floorPrice) * 100) : 0;
        $data = [
            'task_id'       => (int)$task['task_id'],
            'active_id'     => (int)$task['active_id'],
            'goods_id'      => (int)$task['goods_id'],
            'spec_sku_id'   => $task['spec_sku_id'],
            'goods_price'   => $goodsPrice,
            'floor_price'   => $floorPrice,
            'peoples'       => (int)$task['peoples'],
            'cut_people'    => (int)$task['cut_people'],
            'cut_money'     => $cutMoney,
            'actual_price'  => (float)$task['actual_price'],
            'surplus_money' => (float)sprintf('%01.2f', max(0, $surplus)),
            'cut_rate'      => (float)sprintf('%01.2f', $rate),
            'is_floor'      => (int)$task['is_floor'],
            'is_buy'        => (int)$task['is_buy'],
            'status'        => (int)$task['status'],
            'end_time'      => (int)$task['end_time'],
            'create_time'   => (int)$task['create_time'],
        ];
        if ($withHelp) {
            $data['help_list'] = Db::name('bargain_task_help')
                ->alias('h')->join('user u', 'u.user_id = h.user_id', 'left')
                ->where('h.task_id', $task['task_id'])
                ->field('h.user_id, u.nickName, u.avatarUrl, h.cut_money')
                ->order('h.help_id', 'asc')->select()->toArray();
        }
        return $data;
    }

    /**
     * 帮砍一刀（并发安全：事务 + 行锁）
     * @return array ['cut_money'=>float] 或 ['error'=>string]
     */
    public static function helpCut($taskId, $userId)
    {
        return Db::transaction(function () use ($taskId, $userId) {
            $task = Db::name('bargain_task')->where('task_id', $taskId)
                ->where('is_delete', 0)->lock(true)->find();
            if (empty($task)) {
                return ['error' => '砍价任务不存在'];
            }
            if ((int)$task['status'] !== 1) {
                return ['error' => '该砍价任务已结束'];
            }
            if ((int)$task['is_floor'] === 1) {
                return ['error' => '已砍到最低价'];
            }
            if ((int)$task['end_time'] < time()) {
                return ['error' => '砍价已超时'];
            }
            // 每人限一刀
            $cut = Db::name('bargain_task_help')
                ->where('task_id', $taskId)->where('user_id', $userId)->find();
            if (!empty($cut)) {
                return ['error' => '您已经帮砍过啦'];
            }
            $section = json_decode($task['section'], true);
            $idx = (int)$task['cut_people'];
            if (!is_array($section) || !isset($section[$idx])) {
                return ['error' => '砍价次数已用完'];
            }
            $cutMoney = (float)$section[$idx];
            $actualPrice = (float)$task['actual_price'] - $cutMoney;
            $newCut = (float)$task['cut_money'] + $cutMoney;
            $isFloor = abs($actualPrice - (float)$task['floor_price']) < 0.001 ? 1 : 0;
            $status = $isFloor ? 2 : 1;
            Db::name('bargain_task')->where('task_id', $taskId)->update([
                'actual_price' => sprintf('%01.2f', $actualPrice),
                'cut_money'   => sprintf('%01.2f', $newCut),
                'cut_people'  => $idx + 1,
                'is_floor'    => $isFloor,
                'status'      => $status,
                'update_time' => time(),
            ]);
            Db::name('bargain_task_help')->insert([
                'active_id'   => $task['active_id'],
                'task_id'     => $taskId,
                'user_id'     => $userId,
                'is_creater'  => ((int)$task['user_id'] === $userId ? 1 : 0),
                'cut_money'   => sprintf('%01.2f', $cutMoney),
                'wxapp_id'    => $task['wxapp_id'],
                'create_time' => time(),
            ]);
            return ['cut_money' => $cutMoney];
        });
    }
}
