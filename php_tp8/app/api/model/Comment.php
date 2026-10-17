<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商品评价模型（精简版，对齐 RN CommentListScreen）
 * 列表项：user{nickName,avatarUrl}, score, create_time, content, image[{file_path}], order_goods{goods_attr}
 * total：{ all, praise, review, negative }
 */
class Comment extends BaseModel
{
    protected $name = 'comment';

    protected $pageSize = 15;

    /**
     * 商品评价列表（分页）
     * @param int   $goodsId
     * @param int   $scoreType -1 全部 / 10 好评 / 20 中评 / 30 差评
     * @param int   $page
     * @return array
     */
    public function getGoodsCommentList($goodsId, $scoreType = -1, $page = 1)
    {
        $query = $this->where('goods_id', $goodsId)->where('status', 1)->where('is_delete', 0);
        if ($scoreType != -1) {
            $query->where('score', $scoreType);
        }
        $size = $this->pageSize;
        $count = $query->count();
        $rows = $query->page($page, $size)->order('create_time', 'desc')->select()->toArray();

        foreach ($rows as &$c) {
            $user = Db::name('user')->where('user_id', $c['user_id'])
                ->field('nickName,avatarUrl')->find();
            $c['user'] = $user ?: ['nickName' => '', 'avatarUrl' => ''];
            $imgIds = Db::name('comment_image')->where('comment_id', $c['comment_id'])->column('image_id');
            $c['image'] = array_map(function ($id) {
                return ['file_path' => self::getFileUrl($id)];
            }, $imgIds);
            $og = Db::name('order_goods')->where('order_goods_id', $c['order_goods_id'])
                ->field('goods_attr')->find();
            $c['order_goods'] = $og ?: ['goods_attr' => ''];
            $ts = is_numeric($c['create_time']) ? (int)$c['create_time'] : strtotime($c['create_time']);
            $c['create_time'] = $ts ? date('Y-m-d H:i', $ts) : '';
        }

        $lastPage = $count > 0 ? (int)ceil($count / $size) : 1;
        return [
            'total' => $count,
            'per_page' => $size,
            'current_page' => $page,
            'last_page' => $lastPage,
            'data' => $rows,
        ];
    }

    /**
     * 评价计数（按评分类型）
     * @param int $goodsId
     * @return array {all, praise, review, negative}
     */
    public function getTotal($goodsId)
    {
        $base = ['goods_id' => $goodsId, 'status' => 1, 'is_delete' => 0];
        $all = $this->where($base)->count();
        $praise = $this->where($base)->where('score', 10)->count();
        $review = $this->where($base)->where('score', 20)->count();
        $negative = $this->where($base)->where('score', 30)->count();
        return ['all' => $all, 'praise' => $praise, 'review' => $review, 'negative' => $negative];
    }

    // -------------------- 订单评价（移植自 php_my user/Comment） --------------------

    public $error = '';

    public function getError()
    {
        return $this->error ?: '';
    }

    /**
     * 验证订单是否允许评价：已完成且未评价
     */
    public function checkOrderAllowComment($order)
    {
        if ((int)$order['order_status']['value'] != 30) {
            $this->error = '该订单未完成，无法评价';
            return false;
        }
        if ((int)$order['is_comment'] == 1) {
            $this->error = '该订单已完成评价';
            return false;
        }
        return true;
    }

    /**
     * 根据已完成订单商品添加评价（RN 提交 formData 为 JSON 字符串数组）
     */
    public function addForOrder($order, $goodsList, $formDataJson)
    {
        $formDataList = json_decode(htmlspecialchars_decode($formDataJson), true);
        if (!is_array($formDataList)) {
            $this->error = '提交的数据不合法';
            return false;
        }
        $formData = [];
        foreach ($formDataList as $item) {
            if (isset($item['order_goods_id'])) {
                $formData[$item['order_goods_id']] = $item;
            }
        }
        $data = [];
        foreach ($goodsList as $goods) {
            if (!isset($formData[$goods['order_goods_id']])) {
                continue;
            }
            $item = $formData[$goods['order_goods_id']];
            $content = trim($item['content'] ?? '');
            if ($content === '') {
                continue;
            }
            $data[] = [
                'score'          => (int)($item['score'] ?? 10),
                'content'        => $content,
                'is_picture'     => !empty($item['uploaded']) ? 1 : 0,
                'sort'           => 100,
                'status'         => 1,
                'order_id'       => $order['order_id'],
                'goods_id'       => (int)($item['goods_id'] ?? $goods['goods_id']),
                'order_goods_id' => $goods['order_goods_id'],
                'wxapp_id'       => self::getWxappId(),
                'append_content' => '',
                'append_time'    => 0,
                'create_time'    => time(),
                'update_time'    => time(),
            ];
        }
        if (empty($data)) {
            $this->error = '没有输入评价内容';
            return false;
        }
        // getUserOrderDetail 返回的格式化数组不含 user_id，此处按订单号回查
        $orderUserId = (int)Db::name('order')->where('order_id', $order['order_id'])->value('user_id');
        if ($orderUserId <= 0) {
            $this->error = '订单不存在';
            return false;
        }
        foreach ($data as &$d) {
            $d['user_id'] = $orderUserId;
        }
        unset($d);
        return Db::transaction(function () use ($order, $goodsList, $formData, $data) {
            $commentMap = [];
            foreach ($data as $d) {
                $cid = Db::name('comment')->insertGetId($d);
                $commentMap[$d['order_goods_id']] = $cid;
            }
            $imageData = [];
            foreach ($formData as $ogId => $item) {
                if (!isset($commentMap[$ogId])) {
                    continue;
                }
                $cid = $commentMap[$ogId];
                foreach (($item['uploaded'] ?? []) as $imageId) {
                    $imageData[] = [
                        'comment_id'  => $cid,
                        'image_id'    => (int)$imageId,
                        'wxapp_id'    => self::getWxappId(),
                        'create_time' => time(),
                    ];
                }
            }
            if (!empty($imageData)) {
                Db::name('comment_image')->insertAll($imageData);
            }
            foreach ($commentMap as $ogId => $cid) {
                Db::name('order_goods')->where('order_goods_id', $ogId)->update(['is_comment' => 1]);
            }
            $isComment = count($goodsList) === count($data);
            if ($isComment) {
                Db::name('order')->where('order_id', $order['order_id'])->update(['is_comment' => 1]);
            }
            return true;
        });
    }

    /**
     * 我的评价列表
     */
    public function getUserCommentList($userId, $scoreType = -1, $page = 1, $sort = 'desc')
    {
        $query = Db::name('comment')->where('user_id', $userId)->where('is_delete', 0);
        if ($scoreType > 0) {
            $query->where('score', $scoreType);
        }
        $size = $this->pageSize;
        $count = $query->count();
        $rows = $query->page($page, $size)
            ->order('create_time', $sort === 'asc' ? 'asc' : 'desc')
            ->select()->toArray();
        foreach ($rows as &$c) {
            $og = Db::name('order_goods')->where('order_goods_id', $c['order_goods_id'])
                ->field('goods_name,goods_attr,image_id')->find();
            $c['goods'] = $og ? [
                'goods_name'  => $og['goods_name'],
                'goods_attr'  => $og['goods_attr'],
                'image'       => BaseModel::getFileUrl($og['image_id']),
            ] : ['goods_name' => '', 'goods_attr' => '', 'image' => ''];
            $imgIds = Db::name('comment_image')->where('comment_id', $c['comment_id'])->column('image_id');
            $c['image'] = array_map(function ($id) {
                return ['file_path' => BaseModel::getFileUrl($id)];
            }, $imgIds);
            $ts = is_numeric($c['create_time']) ? (int)$c['create_time'] : strtotime($c['create_time']);
            $c['create_time'] = $ts ? date('Y-m-d H:i', $ts) : '';
        }
        $lastPage = $count > 0 ? (int)ceil($count / $size) : 1;
        return [
            'total'        => $count,
            'per_page'     => $size,
            'current_page' => $page,
            'last_page'    => $lastPage,
            'data'         => $rows,
        ];
    }

    /**
     * 我的评价计数（按评分类型）
     */
    public function getUserCommentTotal($userId)
    {
        $base = ['user_id' => $userId, 'is_delete' => 0];
        $all = Db::name('comment')->where($base)->count();
        $praise = Db::name('comment')->where($base)->where('score', 10)->count();
        $review = Db::name('comment')->where($base)->where('score', 20)->count();
        $negative = Db::name('comment')->where($base)->where('score', 30)->count();
        return ['all' => $all, 'praise' => $praise, 'review' => $review, 'negative' => $negative];
    }

    /**
     * 软删除自己的评价
     */
    public function softDelete($commentId, $userId)
    {
        return Db::name('comment')->where('comment_id', $commentId)
            ->where('user_id', $userId)->update(['is_delete' => 1]);
    }

    /**
     * 追评
     */
    public function appendComment($commentId, $userId, $content)
    {
        $content = trim($content);
        if ($content === '') {
            $this->error = '请输入追评内容';
            return false;
        }
        $comment = Db::name('comment')->where('comment_id', $commentId)
            ->where('user_id', $userId)->where('is_delete', 0)->find();
        if (!$comment) {
            $this->error = '评价不存在';
            return false;
        }
        return Db::name('comment')->where('comment_id', $commentId)
            ->update(['append_content' => $content, 'append_time' => time()]);
    }
}
