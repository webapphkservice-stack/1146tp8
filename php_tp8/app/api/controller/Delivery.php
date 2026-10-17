<?php
namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\LocalDelivery;
use app\api\model\LocalDeliveryShop;
use think\facade\Db;
use app\common\model\Setting;

/**
 * 配送/外送（对齐 RN delivery.ts lists/detail/checkCountry/shop/goods）
 * 路由置于 Token 中间件分组内（注入 wxapp_id；RN 请求携带 token）
 */
class Delivery extends Controller
{
    /** 配送列表 */
    public function lists()
    {
        $list = LocalDelivery::getApiList();
        $setting = Setting::getItem('delivery');
        return $this->renderSuccess(compact('list', 'setting'));
    }

    /** 配送详情 */
    public function detail()
    {
        $id = $this->request->param('delivery_id') ?: $this->request->param('id');
        $detail = LocalDelivery::detail($id);
        if (!$detail) {
            return $this->renderError('配送服务不存在');
        }
        $setting = Setting::getItem('delivery');
        return $this->renderSuccess(compact('detail', 'setting'));
    }

    /** 校验用户收货国家（用于判断是否开放外送） */
    public function checkCountry()
    {
        $user = $this->getUser();
        $country = Db::name('user_address')
            ->where('user_id', $user['user_id'])
            ->order('address_id', 'desc')
            ->value('country');
        return $this->renderSuccess(['country' => $country ?: 'CN']);
    }

    /** 店铺列表 */
    public function shop()
    {
        $list = LocalDeliveryShop::getApiEnabledShops();
        return $this->renderSuccess(compact('list'));
    }

    /** 店铺外送商品（含配送模板） */
    public function goods()
    {
        $shopId = $this->request->param('shop_id');
        $detail = LocalDelivery::getApiListByShop($shopId);
        if (!$detail) {
            return $this->renderError('配送服务不存在');
        }
        return $this->renderSuccess(compact('detail'));
    }
}
