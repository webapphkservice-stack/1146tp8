<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团订单评价（完整移植 php api/controller/sharing/Comment）
 *
 * 小程序调用点：sharing.comment/order（GET 评价页 / POST 提交）、sharing.comment/lists
 */
class Comment extends Controller
{
    /**
     * 评价页（GET：待评价商品 / POST：提交评价）
     */
    public function order()
    {
        $user = $this->getUser(true);
        $orderId = (int)$this->request->param('order_id', 0);
        $order = Db::name('sharing_order')->where('order_id', $orderId)
            ->where('user_id', $user['user_id'])->where('is_delete', 0)->find();
        if (empty($order)) {
            return $this->renderError('订单不存在');
        }
        if ((int)$order['order_status'] != 30) {
            return $this->renderError('该订单未完成，无法评价');
        }
        if ((int)$order['is_comment'] == 1) {
            return $this->renderError('该订单已完成评价');
        }
        if (!$this->request->isPost()) {
            $goodsList = Db::name('sharing_order_goods')->where('order_id', $orderId)->select()->toArray();
            foreach ($goodsList as &$g) {
                $g['image'] = BaseModel::getFileUrl($g['image_id']);
            }
            unset($g);
            return $this->renderSuccess(['goodsList' => $goodsList]);
        }
        $formData = json_decode(htmlspecialchars_decode((string)$this->request->param('formData', '')), true);
        if (!is_array($formData)) {
            return $this->renderError('提交的数据不合法');
        }
        $now = time();
        $wxappId = $this->getWxappId();
        $count = 0;
        Db::startTrans();
        try {
            foreach ($formData as $item) {
                $content = trim((string)($item['content'] ?? ''));
                if ($content === '') {
                    continue;
                }
                Db::name('sharing_comment')->insert([
                    'order_id' => $orderId,
                    'goods_id' => (int)($item['goods_id'] ?? 0),
                    'order_goods_id' => (int)($item['order_goods_id'] ?? 0),
                    'score' => (int)($item['score'] ?? 10),
                    'content' => $content,
                    'is_picture' => !empty($item['uploaded']) ? 1 : 0,
                    'sort' => 100,
                    'status' => 1,
                    'user_id' => $user['user_id'],
                    'wxapp_id' => $wxappId,
                    'is_delete' => 0,
                    'create_time' => $now,
                    'update_time' => $now,
                ]);
                if (!empty($item['order_goods_id'])) {
                    Db::name('sharing_order_goods')->where('order_goods_id', (int)$item['order_goods_id'])
                        ->update(['is_comment' => 1]);
                }
                $count++;
            }
            if ($count === 0) {
                Db::rollback();
                return $this->renderError('没有输入评价内容');
            }
            Db::name('sharing_order')->where('order_id', $orderId)->update(['is_comment' => 1, 'update_time' => $now]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError('评价失败');
        }
        return $this->renderSuccess([], '评价成功');
    }

    /**
     * 我的拼团评价列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $rows = Db::name('sharing_comment')->where('user_id', $user['user_id'])
            ->where('is_delete', 0)->order('create_time', 'desc')->limit(50)->select()->toArray();
        $list = [];
        foreach ($rows as $c) {
            $list[] = [
                'comment_id' => (int)$c['comment_id'],
                'score' => (int)$c['score'],
                'content' => $c['content'],
                'create_time' => date('Y-m-d H:i', $c['create_time']),
            ];
        }
        return $this->renderSuccess(compact('list'));
    }
}
