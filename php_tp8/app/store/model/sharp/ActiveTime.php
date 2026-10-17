<?php

namespace app\store\model\sharp;

use app\common\model\BaseModel;

/**
 * 整点秒杀-活动会场场次模型(后台，自包含)
 * Class ActiveTime
 * @package app\store\model\sharp
 */
class ActiveTime extends BaseModel
{
    protected $name = 'sharp_active_time';

    /**
     * 关联活动会场
     * @return \think\model\relation\BelongsTo
     */
    public function active()
    {
        return $this->belongsTo('app\store\model\sharp\Active', 'active_id', 'active_id');
    }

    /**
     * 关联商品关联记录
     * @return \think\model\relation\HasMany
     */
    public function goods()
    {
        return $this->hasMany('app\store\model\sharp\ActiveGoods', 'active_time_id', 'active_time_id');
    }

    /**
     * 场次列表
     * @param $activeId
     * @return \think\Paginator
     */
    public function getList($activeId)
    {
        return $this->with(['active'])
            ->withCount(['goods'])
            ->where('active_id', '=', $activeId)
            ->order(['active_time' => 'asc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 修改状态
     * @param $state
     * @return bool
     */
    public function setStatus($state)
    {
        return $this->save(['status' => (int)$state]) !== false;
    }

    /**
     * 获取指定会场的所有场次时间
     * @param $activeId
     * @return array
     */
    public function getActiveTimeData($activeId)
    {
        return $this->where('active_id', '=', $activeId)->column('active_time');
    }

    /**
     * 根据活动场次ID获取商品列表 (格式化后用于编辑页)
     * @param $activeTimeId
     * @return array
     */
    public function getGoodsListByActiveTimeId($activeTimeId)
    {
        $data = [];
        foreach (ActiveGoods::getGoodsListByActiveTimeId($activeTimeId) as $item) {
            $data[] = [
                'goods_id' => $item['sharp_goods_id'],
                'goods_name' => $item['goods']['goods_name'] ?? '',
                'goods_image' => $item['goods']['goods_image'] ?? '',
            ];
        }
        return $data;
    }

    /**
     * 新增记录
     * @param $activeId
     * @param $data
     * @return bool
     */
    public function add($activeId, $data)
    {
        if (!$this->onValidate($data)) {
            return false;
        }
        return $this->transaction(function () use ($activeId, $data) {
            $this->onBatchAdd(
                $activeId,
                $data['active_times'],
                $data['sharp_goods'],
                $data['status'] ?? 1
            );
            return true;
        });
    }

    /**
     * 更新记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (!$this->onValidateSharpGoods($data)) {
            return false;
        }
        $sharpGoods = $data['sharp_goods'] ?? [];
        unset($data['sharp_goods'], $data['active_times']);
        return $this->transaction(function () use ($data, $sharpGoods) {
            $this->save($data);
            $this->onUpdateActiveGoodsRec($sharpGoods);
            return true;
        });
    }

    /**
     * 更新当前场次的商品关联记录
     * @param $sharpGoodsIds
     * @return array|false
     */
    private function onUpdateActiveGoodsRec($sharpGoodsIds)
    {
        $saveData = [];
        foreach ($sharpGoodsIds as $goodsId) {
            $saveData[] = [
                'active_id' => $this['active_id'],
                'active_time_id' => $this['active_time_id'],
                'sharp_goods_id' => $goodsId,
                'wxapp_id' => self::getWxappId(),
            ];
        }
        $this->goods()->delete();
        return (new ActiveGoods)->saveAll($saveData);
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function onValidate($data)
    {
        if (!isset($data['active_times']) || empty($data['active_times'])) {
            $this->error = '您还没有选择活动场次';
            return false;
        }
        return $this->onValidateSharpGoods($data);
    }

    /**
     * 验证是否选择商品
     * @param $data
     * @return bool
     */
    private function onValidateSharpGoods($data)
    {
        if (!isset($data['sharp_goods']) || empty($data['sharp_goods'])) {
            $this->error = '您还没有选择秒杀商品';
            return false;
        }
        return true;
    }

    /**
     * 批量新增活动场次
     * @param $activeId
     * @param array $times
     * @param array $sharpGoodsIds
     * @param int $status
     * @return bool
     */
    public function onBatchAdd($activeId, $times, $sharpGoodsIds, $status = 1)
    {
        $saveData = [];
        foreach ($times as $time) {
            $saveData[] = [
                'active_id' => $activeId,
                'active_time' => (int)$time,
                'status' => (int)$status,
                'wxapp_id' => self::getWxappId(),
            ];
        }
        $activeTimes = $this->saveAll($saveData);
        if (!empty($sharpGoodsIds)) {
            $this->onBatchAddActiveGoodsRec($activeTimes, $sharpGoodsIds);
        }
        return true;
    }

    /**
     * 新增活动场次与商品关联记录
     * @param $activeTimes
     * @param $sharpGoodsIds
     * @return array|false
     */
    private function onBatchAddActiveGoodsRec($activeTimes, $sharpGoodsIds)
    {
        $saveData = [];
        foreach ($activeTimes as $item) {
            foreach ($sharpGoodsIds as $goodsId) {
                $saveData[] = [
                    'active_id' => $item['active_id'],
                    'active_time_id' => $item['active_time_id'],
                    'sharp_goods_id' => $goodsId,
                    'wxapp_id' => self::getWxappId(),
                ];
            }
        }
        return (new ActiveGoods)->saveAll($saveData);
    }

    /**
     * 根据活动ID删除全部场次和商品关系
     * @param $activeId
     * @return bool
     */
    public function onDeleteByActiveId($activeId)
    {
        $this->where('active_id', '=', $activeId)->delete();
        (new ActiveGoods)->where('active_id', '=', $activeId)->delete();
        return true;
    }

    /**
     * 删除当前场次
     * @return bool
     */
    public function onDelete()
    {
        $this->delete();
        $this->goods()->delete();
        return true;
    }

}
