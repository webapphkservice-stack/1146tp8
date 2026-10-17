<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\User as UserModel;
use think\facade\Db;

/**
 * 用户中心（移植自 php_my api/controller/user/Index::detail）
 */
class Index extends Controller
{
    public function detail()
    {
        $user = $this->getUser(true);
        $userInfo = $user->toArray();
        $userInfo['order_count'] = $this->getOrderCount($user['user_id']);
        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 订单数量统计（待付款 / 待收货 / 待评价）
     * 注：Order 模型属后续批次，这里用 Db 直接统计，避免引入未迁移模块。
     */
    private function getOrderCount($userId)
    {
        $wxappId = UserModel::getWxappId();
        $where = ['user_id' => $userId, 'wxapp_id' => $wxappId, 'is_delete' => 0];

        $payment = Db::name('order')->where($where)
            ->where('pay_status', 10)
            ->where('order_status', 10)
            ->count();
        $received = Db::name('order')->where($where)
            ->where('pay_status', 20)
            ->where('delivery_status', 20)
            ->where('receipt_status', 10)
            ->count();
        $comment = Db::name('order')->where($where)
            ->where('order_status', 30)
            ->where('is_comment', 0)
            ->count();

        return ['payment' => $payment, 'received' => $received, 'comment' => $comment];
    }
}
