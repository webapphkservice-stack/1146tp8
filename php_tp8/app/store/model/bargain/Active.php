<?php

namespace app\store\model\bargain;

use app\common\model\BaseModel;

/**
 * 砍价活动模型(后台，自包含)
 * Class Active
 * @package app\store\model\bargain
 */
class Active extends BaseModel
{
    protected $name = 'bargain_active';

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
        $list = $this->withoutGlobalScope(['wxappId'])
            ->alias('ba')
            ->join('goods g', 'g.goods_id = ba.goods_id')
            ->where('ba.wxapp_id', self::getWxappId())
            ->where('ba.is_delete', '=', 0)
            ->order(['ba.sort' => 'asc', 'ba.create_time' => 'desc'])
            ->field('ba.*, g.goods_name')
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        return $list;
    }

    /**
     * 详情
     * @param $activeId
     * @return null|\think\Model
     */
    public static function detail($activeId)
    {
        return static::find($activeId);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        if (!$this->onValidate($data, 'add')) {
            return false;
        }
        $data['wxapp_id'] = self::getWxappId();
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        return $this->save($data) !== false;
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (!$this->onValidate($data, 'edit')) {
            return false;
        }
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        return $this->save($data) !== false;
    }

    /**
     * 表单验证
     * @param $data
     * @param string $scene
     * @return bool
     */
    private function onValidate($data, $scene = 'add')
    {
        if ($scene === 'add') {
            if (!isset($data['goods_id']) || empty($data['goods_id'])) {
                $this->error = '请选择商品';
                return false;
            }
        }
        if (empty($data['start_time']) || empty($data['end_time'])) {
            $this->error = '请选择活动的开始时间与截止时间';
            return false;
        }
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        if ($data['end_time'] <= $data['start_time']) {
            $this->error = '活动结束时间必须大于开始时间';
            return false;
        }
        return true;
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]) !== false;
    }

    /**
     * 商品ID是否存在
     * @param $goodsId
     * @return bool
     */
    public static function isExistGoodsId($goodsId)
    {
        return !!(new static)->where('goods_id', '=', $goodsId)
            ->where('is_delete', '=', 0)
            ->value('active_id');
    }

}
