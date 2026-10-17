<?php
namespace app\api\model\user;

use think\facade\Db;
use think\Model;

/**
 * 用户积分明细模型——php_tp8 轻量版
 *
 * 对齐 RN 端 src/screens/User/PointsScreen.tsx 的实际消费方式：
 *   - `l?.list || []` 后直接 list.map(...)      → list 必须是【纯数组】
 *   - 渲染 log.points / log.describe / log.create_time
 *
 * 两点与 balance 流水不同，不可照搬 BalanceLog：
 *   1. BalanceLog::getList 返回 paginate() 的分页对象（含 data/total 等键），
 *      而 PointsScreen 直接对 list 做 .map()，传分页对象会崩溃，故此处返回纯数组。
 *   2. 表字段是 value，RN 读的是 points，需做映射（value → points）。
 */
class PointsLog extends Model
{
    protected $name = 'user_points_log';

    /**
     * 积分明细列表
     *
     * @param int $userId
     * @param int $listRows 取多少条。PointsScreen 无分页/加载更多交互（仅 useEffect 拉一次），
     *                      故取较大值以保证明细可见；旧版为分页 15 条。
     * @return array 纯数组
     */
    public static function getList($userId, $listRows = 50)
    {
        $rows = Db::name('user_points_log')
            ->where('user_id', $userId)
            ->order('create_time', 'desc')
            ->order('log_id', 'desc')
            ->limit($listRows)
            ->select()
            ->toArray();

        foreach ($rows as &$item) {
            // 表字段 value（可正可负：正数获得、负数消耗）→ RN 契约字段名 points
            $item['points']      = (int)$item['value'];
            $item['log_id']      = (int)$item['log_id'];
            $item['create_time'] = (int)$item['create_time'];
            $item['describe']    = (string)$item['describe'];
            $item['remark']      = (string)$item['remark'];
        }
        unset($item);

        return $rows;
    }
}
