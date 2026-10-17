<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团商品评价模型(后台，自包含)
 * Class Comment
 * @package app\store\model\sharing
 */
class Comment extends BaseModel
{
    protected $name = 'sharing_comment';

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 关联订单
     * @return \think\model\relation\BelongsTo
     */
    public function orderM()
    {
        return $this->belongsTo('app\store\model\sharing\Order', 'order_id', 'order_id');
    }

    /**
     * 关联订单商品
     * @return \think\model\relation\BelongsTo
     */
    public function OrderGoods()
    {
        return $this->belongsTo('app\store\model\sharing\OrderGoods', 'order_goods_id', 'order_goods_id');
    }

    /**
     * 关联评价图片
     * @return \think\model\relation\HasMany
     */
    public function image()
    {
        return $this->hasMany('app\store\model\sharing\CommentImage', 'comment_id', 'comment_id');
    }

    /**
     * 详情
     * @param $comment_id
     * @return null|\think\Model
     */
    public static function detail($comment_id)
    {
        return static::with(['user', 'orderM', 'OrderGoods', 'image'])->find($comment_id);
    }

    /**
     * 逻辑删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

    /**
     * 获取评价总数
     * @return int
     */
    public function getCommentTotal()
    {
        return $this->where(['is_delete' => 0])->count();
    }

    /**
     * 更新评价
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            $this->image()->delete();
            isset($data['images']) && $this->addCommentImages($data['images']);
            $data['is_picture'] = !$this->image()->select()->isEmpty();
            $this->save($data);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加评价图片
     * @param $images
     * @return array|\think\Collection
     */
    private function addCommentImages($images)
    {
        $data = array_map(function ($image_id) {
            return [
                'image_id' => $image_id,
                'wxapp_id' => self::getWxappId(),
            ];
        }, $images);
        return $this->image()->saveAll($data);
    }

    /**
     * 获取评价列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return $this->with(['user', 'orderM', 'OrderGoods'])
            ->where('is_delete', '=', 0)
            ->order(['sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

}
