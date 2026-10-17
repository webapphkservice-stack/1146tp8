<?php
namespace app\h5\controller;

use app\api\model\LocalDelivery;

/**
 * H5 - 外送/配送
 */
class Delivery extends Controller
{
    public function index()
    {
        $list = LocalDelivery::getApiList();
        return $this->fetch('delivery/index', [
            'list'      => $list,
            'storeName' => $this->storeName(),
        ]);
    }
}
