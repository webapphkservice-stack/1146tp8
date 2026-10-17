<?php

namespace app\api\controller;

use app\api\model\store\Shop as ShopModel;

/**
 * 自提点（门店）控制器
 * Class Shop
 * @package app\api\controller
 */
class Shop extends Controller
{
    /**
     * 自提点列表（仅返回支持自提核销的门店；可传经纬度按距离排序）
     */
    public function lists($longitude = '', $latitude = '')
    {
        $list = ShopModel::getList(true, $longitude, $latitude);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 自提点详情
     */
    public function detail($shop_id)
    {
        $detail = ShopModel::detail($shop_id);
        if (empty($detail)) {
            return $this->renderError('门店不存在');
        }
        return $this->renderSuccess(compact('detail'));
    }
}
