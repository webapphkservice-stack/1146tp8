<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\OrderRefund;
use app\api\model\OrderGoods;
use app\api\model\Express;

/**
 * 售后/退款（对齐 RN 订单详情退款流程）
 *  - lists  列表
 *  - apply  提交申请
 *  - detail 详情（含物流公司列表）
 *  - delivery 用户回寄
 */
class Refund extends Controller
{
    public function lists()
    {
        $user = $this->getUser();
        $state = $this->request->param('state', '');
        $list = OrderRefund::getList($user['user_id'], $state);
        return $this->renderSuccess(compact('list'));
    }

    public function apply()
    {
        $user = $this->getUser();
        $post = $this->request->param('post');
        $formData = $post ? json_decode(htmlspecialchars_decode($post), true) : $this->request->post();
        $orderGoods = OrderGoods::detail($formData['order_goods_id'] ?? 0);
        if (!$orderGoods) {
            return $this->renderError('订单商品不存在');
        }
        $refundId = OrderRefund::apply($user, $orderGoods, $formData);
        return $this->renderSuccess(['refund_id' => $refundId], '提交成功');
    }

    public function detail()
    {
        $user = $this->getUser();
        $id = $this->request->param('order_refund_id') ?: $this->request->param('id');
        $detail = OrderRefund::detail($id, $user['user_id']);
        if (!$detail) {
            return $this->renderError('售后单不存在');
        }
        $expressList = Express::getAll();
        return $this->renderSuccess(compact('detail', 'expressList'));
    }

    public function delivery()
    {
        $user = $this->getUser();
        $post = $this->request->param('post');
        $data = $post ? json_decode(htmlspecialchars_decode($post), true) : $this->request->post();
        if (OrderRefund::delivery($data)) {
            return $this->renderSuccess([], '提交成功');
        }
        return $this->renderError('提交失败');
    }
}
