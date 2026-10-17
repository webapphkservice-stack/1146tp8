<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;
use think\Collection;

/**
 * 商品评价模型（服务端渲染版，移植自 TP5 store/model/Comment.php）
 */
class Comment extends BaseModel
{
    protected $name = 'comment';
    protected $pk = 'comment_id';
    protected $updateTime = false;

    /**
     * 评价列表（含订单商品/用户信息）
     */
    public function getList()
    {
        $list = Db::name('comment')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->order('create_time', 'desc')
            ->paginate(15);
        $items = $list->items();
        foreach ($items as &$item) {
            $og = Db::name('order_goods')
                ->where('order_id', $item['order_id'])
                ->where('goods_id', $item['goods_id'])
                ->field('goods_name, image_id')
                ->find();
            $item['order_goods'] = [
                'goods_name' => $og['goods_name'] ?? '',
                'image'      => ['file_path' => ($og['image_id'] ?? 0) ? self::getFileUrl($og['image_id']) : ''],
            ];
            $user = Db::name('user')->where('user_id', $item['user_id'])
                ->field('user_id, nickName')->find();
            $item['user'] = $user ?: ['user_id' => $item['user_id'], 'nickName' => ''];
            $item['image'] = [];
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 评价详情
     */
    public static function detail($commentId)
    {
        $row = Db::name('comment')->where('comment_id', $commentId)->find();
        if (!$row) {
            return [];
        }
        $og = Db::name('order_goods')
            ->where('order_id', $row['order_id'])
            ->where('goods_id', $row['goods_id'])
            ->field('goods_name, image_id')
            ->find();
        $row['order_goods'] = [
            'goods_name' => $og['goods_name'] ?? '',
            'image'      => ['file_path' => ($og['image_id'] ?? 0) ? self::getFileUrl($og['image_id']) : ''],
        ];
        $user = Db::name('user')->where('user_id', $row['user_id'])
            ->field('user_id, nickName')->find();
        $row['user'] = $user ?: ['user_id' => $row['user_id'], 'nickName' => ''];
        $row['image'] = [];
        return $row;
    }

    public function edit($data)
    {
        $commentId = (int)$data['comment_id'];
        return Db::name('comment')->where('comment_id', $commentId)->update([
            'score'       => (int)$data['score'],
            'content'     => $data['content'],
            'status'      => (int)$data['status'],
            'sort'        => (int)$data['sort'],
            'update_time' => time(),
        ]) !== false;
    }

    public function setDelete($commentId)
    {
        return Db::name('comment')->where('comment_id', $commentId)
            ->update(['is_delete' => 1, 'update_time' => time()]);
    }
}
