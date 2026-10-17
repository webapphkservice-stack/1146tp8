<?php
namespace app\store\controller;

use app\store\controller\Controller;
use app\store\model\Order as OrderModel;
use app\store\model\Express as ExpressModel;
use app\store\model\store\shop\Clerk as ShopClerkModel;
use app\store\model\store\Shop as ShopModel;

/**
 * 订单管理（服务端渲染版，移植自 TP5 store/controller/Order.php）
 * 菜单入口：order/all_list、order/delivery_list、order/receipt_list 等
 */
class Order extends Controller
{
    /** @var OrderModel */
    private $model;

    public function initialize()
    {
        parent::initialize();
        $this->model = (new OrderModel)->setWxappId(self::getWxappId());
    }

    /** 待发货订单列表 */
    public function delivery_list()
    {
        return $this->getList('待发货订单列表', 'delivery');
    }

    /** 待收货订单列表 */
    public function receipt_list()
    {
        return $this->getList('待收货订单列表', 'receipt');
    }

    /** 待付款订单列表 */
    public function pay_list()
    {
        return $this->getList('待付款订单列表', 'pay');
    }

    /** 已完成订单列表 */
    public function complete_list()
    {
        return $this->getList('已完成订单列表', 'complete');
    }

    /** 已取消订单列表 */
    public function cancel_list()
    {
        return $this->getList('已取消订单列表', 'cancel');
    }


    /** 订单列表(通用入口，/store/order/lists → 全部订单) */
    public function lists()
    {
        return $this->all_list();
    }
    /** 全部订单列表 */
    public function all_list()
    {
        return $this->getList('全部订单列表', 'all');
    }

    /** 虚拟订单列表 */
    public function virtual_list()
    {
        return $this->getList('虚拟订单列表', 'virtual');
    }

    /**
     * 订单详情
     * @param int $order_id
     */
    public function detail($order_id)
    {
        // 本地 DB 可能缺订单表/字段，try-catch 降级
        try {
            $detail = $this->model->detailForAdmin($order_id);
        } catch (\Throwable $e) {
            \think\facade\Log::warning('Order::detail 降级：' . $e->getMessage());
            $detail = null;
        }
        // 物流公司列表
        $expressList = ExpressModel::getAll();
        // 门店店员列表
        $shopClerkList = (new ShopClerkModel)->getList(true);
        return $this->fetch('detail', compact('detail', 'expressList', 'shopClerkList'));
    }

    /**
     * 确认发货
     * @param int $order_id
     */
    public function delivery($order_id)
    {
        if (!$this->model->delivery($order_id, $this->postData('order'))) {
            return $this->renderError($this->model->getError() ?: '发货失败');
        }
        return $this->renderSuccess('发货成功');
    }

    /**
     * 修改订单价格
     * @param int $order_id
     */
    public function updatePrice($order_id)
    {
        if (!$this->model->updatePrice($order_id, $this->postData('order'))) {
            return $this->renderError($this->model->getError() ?: '修改失败');
        }
        return $this->renderSuccess('修改成功');
    }

    /**
     * 订单列表（公共）
     * @param string $title
     * @param string $dataType
     */
    private function getList($title, $dataType)
    {
        $list = $this->model->getListForAdmin($dataType, $this->request->param());
        // 自提门店列表
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('title', 'dataType', 'list', 'shopList'));
    }
}
