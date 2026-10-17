<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Order as OrderModel;

/**
 * 订单核销记录
 * Class Order
 * @package app\store\controller\shop
 */
class Order extends Controller
{
    /**
     * 核销记录列表
     * @param int $shop_id
     * @param string $search
     * @return mixed
     */
    public function index($shop_id = 0, $search = '')
    {
        $model = new OrderModel;
        $list = $model->getList($shop_id, $search);
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('list', 'shopList'));
    }
}
