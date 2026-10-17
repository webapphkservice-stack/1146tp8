<?php
namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\recharge\Order as OrderModel;

/**
 * 充值记录（服务端渲染版，移植自 TP5 store/controller/user/Recharge.php）
 * 菜单：用户管理 -> 余额记录 -> 充值记录（user.recharge/order）
 */
class Recharge extends Controller
{
    /**
     * 充值记录
     */
    public function order()
    {
        $model = new OrderModel;
        return $this->fetch('order', [
            // 充值记录列表
            'list' => $model->getList($this->request->param()),
            // 属性集
            'attributes' => OrderModel::getAttributes(),
        ]);
    }
}
