<?php

namespace app\store\model\store\shop;

use app\common\model\store\shop\Order as OrderModel;

/**
 * 商家门店核销订单记录模型（服务端渲染版）
 * Class Order
 * @package app\store\model\store\shop
 */
class Order extends OrderModel
{
    /**
     * 获取列表数据
     * @param int $shop_id 门店id
     * @param string $search 核销员姓名
     * @return \think\Paginator
     */
    public function getList($shop_id = 0, $search = '')
    {
        $shop_id > 0 && $this->where('store_shop_order.shop_id', '=', (int)$shop_id);
        if (!empty($search)) {
            $this->where('clerk_id', 'IN', function ($query) use ($search) {
                $query->table('store_shop_clerk')->where('real_name', 'like', "%{$search}%")->field('clerk_id');
            });
        }
        return $this->with(['shop', 'clerk', 'order'])
            ->field('store_shop_order.*')
            ->order(['store_shop_order.create_time' => 'desc'])
            ->paginate(15);
    }
}
