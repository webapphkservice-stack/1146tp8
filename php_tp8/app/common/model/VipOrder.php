<?php

namespace app\common\model;

use think\facade\Db;

/**
 * VIP开通订单模型
 * Class VipOrder
 * @package app\common\model
 */
class VipOrder extends BaseModel
{
    protected $name = 'vip_order';

    /**
     * 生成订单号
     * @return string
     */
    public function orderNo()
    {
        // 注意：implode 第一个参数在 PHP 8.1+ 不接受 NULL（已废弃并触发异常），改用空字符串分隔符
        return date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 用户开通记录
     * @param $userId
     * @return array
     */
    public function getListByUser($userId)
    {
        return $this->where('user_id', '=', $userId)
            ->order(['create_time' => 'desc'])
            ->select()
            ->toArray();
    }

    /**
     * 根据订单号获取支付详情（供支付回调使用）
     * @param $orderNo
     * @return array|false|\think\Model
     */
    public function getPayDetail($orderNo)
    {
        return $this->where('order_no', '=', $orderNo)->find();
    }

    /**
     * 后台VIP开通订单列表（关联用户）
     *
     * TP8 适配：
     *  - join 的 user 表同样含 wxapp_id 列，必须关闭 wxappId 全局作用域并显式用 o.wxapp_id，
     *    否则裸列 wxapp_id 会触发 "Column 'wxapp_id' in where clause is ambiguous"
     *  - 用 Db::name('user')->getTable() 取带前缀表名，避免依赖 api\model\User
     * @param array $filter pay_status / pay_type / keyword
     * @return \think\Paginator
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
        if (!empty($filter['pay_type'])) {
            $model = $model->where('o.pay_type', '=', (int)$filter['pay_type']);
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
