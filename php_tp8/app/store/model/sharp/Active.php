<?php

namespace app\store\model\sharp;

use app\common\model\BaseModel;

/**
 * 整点秒杀-活动会场模型(后台，自包含)
 * Class Active
 * @package app\store\model\sharp
 */
class Active extends BaseModel
{
    protected $name = 'sharp_active';

    /**
     * 关联场次
     * @return \think\model\relation\HasMany
     */
    public function activeTime()
    {
        return $this->hasMany('app\store\model\sharp\ActiveTime', 'active_id', 'active_id');
    }

    /**
     * 获取器：活动日期
     * @param $value
     * @return false|string
     */
    public function getActiveDateAttr($value)
    {
        return date('Y-m-d', $value);
    }

    /**
     * 获取活动会场列表
     * @return \think\Paginator
     */
    public function getList()
    {
        $list = $this->with(['active_time'])
            ->where('is_delete', '=', 0)
            ->order(['active_date' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
        return $this->getActiveTimeCount($list);
    }

    /**
     * 计算各会场场次数量
     * @param $list
     * @return mixed
     */
    private function getActiveTimeCount($list)
    {
        foreach ($list as &$item) {
            $activeTimeList = $item['active_time'] ?? [];
            if ($activeTimeList instanceof \think\model\Collection) {
                $activeTimeList = $activeTimeList->toArray();
            }
            $activeTimeArr = array_column($activeTimeList, 'active_time');
            $item['active_time_count'] = count($activeTimeArr);
        }
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
        if (!$this->onValidate($data)) {
            return false;
        }
        $activeTimes = $data['active_times'] ?? [];
        $sharpGoods = $data['sharp_goods'] ?? [];
        unset($data['active_times'], $data['sharp_goods']);
        $data['wxapp_id'] = self::getWxappId();
        $data['active_date'] = strtotime($data['active_date']);
        return $this->transaction(function () use ($data, $activeTimes, $sharpGoods) {
            $this->save($data);
            (new ActiveTime)->onBatchAdd($this['active_id'], $activeTimes, $sharpGoods);
            return true;
        });
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function onValidate($data)
    {
        if ($this->isExistByActiveDate($data['active_date'])) {
            $this->error = '该活动日期已存在';
            return false;
        }
        return true;
    }

    /**
     * 活动日期是否已存在
     * @param $date
     * @return bool
     */
    private function isExistByActiveDate($date)
    {
        return !!(new static)->where('active_date', '=', strtotime($date))
            ->where('is_delete', '=', 0)
            ->value('active_id');
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
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        (new ActiveTime)->onDeleteByActiveId($this['active_id']);
        return $this->save(['is_delete' => 1]) !== false;
    }

}
