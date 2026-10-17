<?php
namespace app\h5\controller;

use app\api\model\store\Shop as ShopModel;

/**
 * H5 - 门店详情
 */
class Shop extends Controller
{
    public function detail()
    {
        $shopId = (int)$this->request->param('shop_id', 0);
        $detail = ShopModel::detail($shopId);
        return $this->fetch('shop/detail', [
            'detail'    => $detail,
            'storeName' => $this->storeName(),
        ]);
    }
}
