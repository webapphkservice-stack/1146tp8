<?php

namespace app\api\controller\recharge;

use app\api\controller\Controller;
use think\facade\Db;

/**
 * 充值记录（移植自 php api/controller/recharge/Order，Db 轻量版）
 * Class Order
 * @package app\api\controller\recharge
 *
 * 小程序调用点：recharge.order/lists
 */
class Order extends Controller
{
    /**
     * 我的充值记录列表（仅已支付）
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $page = max(1, (int)$this->request->param('page', 1));
        $size = 15;
        $query = Db::name('recharge_order')
            ->where('user_id', $user['user_id'])
            ->where('pay_status', 20);
        $count = $query->count();
        $rows = $query->order('create_time', 'desc')->page($page, $size)->select()->toArray();
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'order_id'   => (int)$r['order_id'],
                'order_no'   => $r['order_no'],
                'pay_price'  => $r['pay_price'],
                'gift_money' => $r['gift_money'],
                'actual_money' => $r['actual_money'],
                'pay_status' => (int)$r['pay_status'],
                'pay_time_text' => $r['pay_time'] > 0 ? date('Y-m-d H:i', $r['pay_time']) : '',
                'create_time' => date('Y-m-d H:i', $r['create_time']),
            ];
        }
        $lastPage = $count > 0 ? (int)ceil($count / $size) : 1;
        return $this->renderSuccess([
            'list' => [
                'total' => $count, 'per_page' => $size, 'current_page' => $page,
                'last_page' => $lastPage, 'data' => $list,
            ],
        ]);
    }
}
