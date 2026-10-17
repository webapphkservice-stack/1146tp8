<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团订单售后服务（完整移植 php api/controller/sharing/Refund）
 *
 * 小程序调用点：sharing.refund/{lists,apply,detail,delivery}
 */
class Refund extends Controller
{
    private function statusText($status)
    {
        return [1 => '待审核', 2 => '已同意', 3 => '已拒绝', 4 => '已退货', 5 => '已退款'][$status] ?? '';
    }

    /**
     * 用户售后单列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $state = (int)$this->request->param('state', -1);
        $query = Db::name('sharing_order_refund')->where('user_id', $user['user_id'])
            ->where('wxapp_id', $this->getWxappId());
        if ($state > 0) {
            $query->where('status', $state);
        }
        $rows = $query->order('order_refund_id', 'desc')->limit(50)->select()->toArray();
        $list = [];
        foreach ($rows as $r) {
            $goods = Db::name('sharing_order_goods')->where('order_goods_id', $r['order_goods_id'])->find();
            $list[] = [
                'order_refund_id' => (int)$r['order_refund_id'],
                'order_id' => (int)$r['order_id'],
                'order_no' => Db::name('sharing_order')->where('order_id', $r['order_id'])->value('order_no') ?: '',
                'type' => (int)$r['type'],
                'type_text' => (int)$r['type'] == 10 ? '退货退款' : '仅退款',
                'apply_desc' => $r['apply_desc'],
                'refund_money' => $r['refund_money'],
                'status' => (int)$r['status'],
                'status_text' => $this->statusText((int)$r['status']),
                'goods' => $goods ? [
                    'goods_id' => (int)$goods['goods_id'],
                    'goods_name' => $goods['goods_name'],
                    'image' => BaseModel::getFileUrl($goods['image_id']),
                    'goods_attr' => $goods['goods_attr'] ?: '',
                    'total_num' => (int)$goods['total_num'],
                ] : null,
            ];
        }
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 申请售后（GET 详情 / POST 提交）
     */
    public function apply()
    {
        $user = $this->getUser(true);
        $orderGoodsId = (int)$this->request->param('order_goods_id', 0);
        $goods = Db::name('sharing_order_goods')->where('order_goods_id', $orderGoodsId)
            ->where('user_id', $user['user_id'])->find();
        if (empty($goods)) {
            return $this->renderError('订单商品不存在');
        }
        if (!$this->request->isPost()) {
            return $this->renderSuccess(['detail' => [
                'order_goods_id' => $orderGoodsId,
                'goods_name' => $goods['goods_name'],
                'image' => BaseModel::getFileUrl($goods['image_id']),
                'goods_attr' => $goods['goods_attr'] ?: '',
                'total_pay_price' => $goods['total_pay_price'],
            ]]);
        }
        $exists = Db::name('sharing_order_refund')->where('order_goods_id', $orderGoodsId)
            ->where('status', '>', 0)->count();
        if ($exists > 0) {
            return $this->renderError('该商品已申请售后');
        }
        $applyDesc = trim((string)$this->request->param('apply_desc', ''));
        if ($applyDesc === '') {
            return $this->renderError('请填写申请原因');
        }
        $now = time();
        Db::name('sharing_order_refund')->insert([
            'order_id' => $goods['order_id'],
            'order_goods_id' => $orderGoodsId,
            'user_id' => $user['user_id'],
            'type' => (int)$this->request->param('type', 10),
            'apply_desc' => $applyDesc,
            'is_agree' => 0,
            'refuse_desc' => '',
            'refund_money' => (string)$this->request->param('refund_money', $goods['total_pay_price']),
            'is_user_send' => 0,
            'send_time' => 0,
            'express_id' => '',
            'express_no' => '',
            'is_receipt' => 0,
            'status' => 1,
            'wxapp_id' => $this->getWxappId(),
            'create_time' => $now,
            'update_time' => $now,
        ]);
        Db::name('sharing_order')->where('order_id', $goods['order_id'])->update(['is_refund' => 1, 'update_time' => $now]);
        return $this->renderSuccess([], '申请成功');
    }

    /**
     * 售后单详情
     */
    public function detail()
    {
        $user = $this->getUser(true);
        $orderRefundId = (int)$this->request->param('order_refund_id', 0);
        $r = Db::name('sharing_order_refund')->where('order_refund_id', $orderRefundId)
            ->where('user_id', $user['user_id'])->find();
        if (empty($r)) {
            return $this->renderError('售后单不存在');
        }
        $r['status_text'] = $this->statusText((int)$r['status']);
        $r['type_text'] = (int)$r['type'] == 10 ? '退货退款' : '仅退款';
        return $this->renderSuccess(['detail' => $r]);
    }

    /**
     * 用户发货（退货退款填快递单号）
     */
    public function delivery()
    {
        $user = $this->getUser(true);
        $orderRefundId = (int)$this->request->param('order_refund_id', 0);
        $r = Db::name('sharing_order_refund')->where('order_refund_id', $orderRefundId)
            ->where('user_id', $user['user_id'])->find();
        if (empty($r)) {
            return $this->renderError('售后单不存在');
        }
        $expressNo = (string)$this->request->param('express_no', '');
        if ($expressNo === '') {
            return $this->renderError('请填写快递单号');
        }
        Db::name('sharing_order_refund')->where('order_refund_id', $orderRefundId)->update([
            'express_id' => (string)$this->request->param('express_id', ''),
            'express_no' => $expressNo,
            'is_user_send' => 1,
            'send_time' => time(),
            'status' => 4,
            'update_time' => time(),
        ]);
        return $this->renderSuccess([], '发货成功');
    }
}
