<?php

namespace app\store\model\dealer;

use think\facade\Db;
use think\Collection;
use app\common\model\BaseModel;
use app\store\model\Order as OrderModel;

/**
 * 分销商订单模型(后台，自包含)
 * Class Order
 * @package app\store\model\dealer
 */
class Order extends BaseModel
{
    protected $name = 'dealer_order';

    /**
     * 一级分销
     * @return \think\model\relation\BelongsTo
     */
    public function dealerFirst()
    {
        return $this->belongsTo('app\store\model\dealer\User', 'first_user_id', 'user_id');
    }

    /**
     * 二级分销
     * @return \think\model\relation\BelongsTo
     */
    public function dealerSecond()
    {
        return $this->belongsTo('app\store\model\dealer\User', 'second_user_id', 'user_id');
    }

    /**
     * 三级分销
     * @return \think\model\relation\BelongsTo
     */
    public function dealerThird()
    {
        return $this->belongsTo('app\store\model\dealer\User', 'third_user_id', 'user_id');
    }

    /**
     * 关联主订单
     * @return \think\model\relation\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('app\store\model\Order', 'order_id', 'order_id');
    }

    /**
     * 获取分销商订单列表
     * @param null $user_id
     * @param int $is_settled
     * @return \think\Paginator
     */
    public function getList($user_id = null, $is_settled = -1)
    {
        $model = $this->withoutGlobalScope();
        $user_id > 1 && $model->where('first_user_id|second_user_id|third_user_id', '=', $user_id);
        $is_settled > -1 && $model->where('is_settled', '=', !!$is_settled);
        $list = $model->with([
            'dealer_first.user',
            'dealer_second.user',
            'dealer_third.user',
        ])
            ->order(['create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);

        // 组装主订单展示数据（商品/买家/状态文案），视图以 order_master 读取
        $orderModel = new OrderModel;
        $items = $list->items();
        foreach ($items as &$item) {
            $master = Db::name('order')->where('order_id', $item['order_id'])->find();
            $item['order_master'] = $master ? $orderModel->enrichOrder($master) : [];
        }
        unset($item);
        $list->setCollection(Collection::make($items));
        return $list;
    }

}
