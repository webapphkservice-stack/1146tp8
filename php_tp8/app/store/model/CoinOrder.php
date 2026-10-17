<?php

namespace app\store\model;

use app\common\model\BaseModel;

/**
 * 代币充值订单（后台管理）
 * Class CoinOrder
 * @package app\store\model
 */
class CoinOrder extends BaseModel
{
    protected $name = 'coin_order';

    /**
     * 列表（关联用户）
     *
     * TP8 适配（同 VipOrder::getList）：join 的 user 表同样含 wxapp_id 列，
     * 必须关闭 wxappId 全局作用域并显式用 o.wxapp_id，否则裸列 wxapp_id 会触发
     * "Column 'wxapp_id' in where clause is ambiguous"。
     * @param array $filter pay_status / keyword
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($filter = [])
    {
        // 取带前缀的 user 表名（think-orm v4 的 Query 无 getTable()，改用配置前缀拼接）
        $userTable = config('database.connections.mysql.prefix') . 'user';
        $model = $this->withoutGlobalScope(['wxappId'])
            ->alias('o')
            ->field(['o.*', 'u.nickName', 'u.avatarUrl'])
            ->join($userTable . ' u', 'u.user_id = o.user_id', 'LEFT')
            ->where('o.wxapp_id', self::getWxappId());

        if (!empty($filter['pay_status'])) {
            $model = $model->where('o.pay_status', '=', (int)$filter['pay_status']);
        }
        if (!empty($filter['keyword'])) {
            $keyword = trim($filter['keyword']);
            $model = $model->where(function ($query) use ($keyword) {
                $query->where('o.order_no', 'like', "%{$keyword}%")
                    ->whereOr('u.nickName', 'like', "%{$keyword}%");
            });
        }
        return $model->order('o.order_id', 'desc')
            ->paginate(['list_rows' => 15, 'query' => request()->param()]);
    }
}
