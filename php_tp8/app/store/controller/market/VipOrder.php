<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\common\model\VipOrder as VipOrderModel;

/**
 * VIP开通订单（后台）
 * TP8 适配：input() 助手在 TP8 仍可用（vendor/topthink/framework/src/helper.php），故控制器逻辑无需改动
 * Class VipOrder
 * @package app\store\controller\market
 */
class VipOrder extends Controller
{
    /**
     * VIP开通订单列表
     * @return mixed
     */
    public function index()
    {
        $filter = [
            'pay_status' => input('pay_status/d', 0),
            'pay_type'   => input('pay_type/d', 0),
            'keyword'    => input('keyword/s', ''),
        ];
        $list = (new VipOrderModel)->getList($filter);
        return $this->fetch('index', compact('list', 'filter'));
    }
}
