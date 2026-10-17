<?php

namespace app\store\model\bargain;

use app\common\model\BaseModel;

/**
 * 砍价任务模型(后台，自包含)
 * Class Task
 * @package app\store\model\bargain
 */
class Task extends BaseModel
{
    protected $name = 'bargain_task';

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 关联商品
     * @return \think\model\relation\BelongsTo
     */
    public function goods()
    {
        return $this->belongsTo('app\store\model\Goods', 'goods_id', 'goods_id');
    }

    /**
     * 获取列表数据
     * @param string $search
     * @return \think\Paginator
     */
    public function getList($search = '')
    {
        // 多表 join 后 wxapp_id 归属不明确，关闭全局作用域并限定 bt 前缀
        $model = $this->withoutGlobalScope(['wxappId'])
            ->alias('bt')
            ->join('goods g', 'g.goods_id = bt.goods_id')
            ->join('user u', 'u.user_id = bt.user_id')
            ->where('bt.wxapp_id', self::getWxappId())
            ->with(['user', 'goods']);
        if (!empty($search)) {
            $model->where(function ($query) use ($search) {
                $query->whereOr('g.goods_name', 'like', "%{$search}%");
                $query->whereOr('u.nickName', 'like', "%{$search}%");
            });
        }
        $list = $model->where('bt.is_delete', '=', 0)
            ->order(['bt.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    /**
     * 详情
     * @param $taskId
     * @return null|\think\Model
     */
    public static function detail($taskId)
    {
        return static::find($taskId);
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

}
