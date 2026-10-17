<?php

namespace app\common\model;

use app\common\model\BaseModel;

/**
 * 地区模型
 * Class Region
 * @package app\common\model
 */
class Region extends BaseModel
{
    protected $name = 'region';
    protected $updateTime = false;

    // region 表无 wxapp_id 列，关闭全局作用域（避免拼接 wxapp_id 过滤报错）
    protected $globalScope = [];

    /**
     * 根据 id 获取地区名称
     * @param int $id
     * @return string
     */
    public static function getNameById($id)
    {
        if (empty($id)) {
            return '';
        }
        $name = (new static)->where('id', '=', (int)$id)->value('name');
        return $name ? (string)$name : '';
    }

    /**
     * 获取所有地区(树状结构)，按省 id 索引
     * @return array
     */
    public static function getCacheTree()
    {
        return (new static)->buildTree();
    }

    /**
     * 获取所有地区列表（按 id 索引）
     * @return array
     */
    public static function getCacheAll()
    {
        return (new static)->buildAll();
    }

    /**
     * 获取所有地区的总数 [total, province, city, region]
     * @return array
     */
    public static function getCacheCounts()
    {
        return (new static)->buildCounts();
    }

    /**
     * 所有地区（按 id 索引）
     * @return array
     */
    private function buildAll()
    {
        $list = $this->field('id, pid, name, level')
            ->order('id', 'asc')
            ->select()
            ->toArray();
        $indexed = [];
        foreach ($list as $item) {
            $indexed[$item['id']] = $item;
        }
        return $indexed;
    }

    /**
     * 格式化为树状格式（按省 id 索引，省下挂 city，市下挂 region）
     * @return array
     */
    private function buildTree()
    {
        $allList = $this->buildAll();
        $treeList = [];
        foreach ($allList as $pKey => $province) {
            if ($province['level'] == 1) {    // 省份
                $treeList[$province['id']] = $province;
                unset($allList[$pKey]);
                foreach ($allList as $cKey => $city) {
                    if ($city['level'] == 2 && $city['pid'] == $province['id']) {    // 城市
                        $treeList[$province['id']]['city'][$city['id']] = $city;
                        unset($allList[$cKey]);
                        foreach ($allList as $rKey => $region) {
                            if ($region['level'] == 3 && $region['pid'] == $city['id']) {    // 地区
                                $treeList[$province['id']]['city'][$city['id']]['region'][$region['id']] = $region;
                                unset($allList[$rKey]);
                            }
                        }
                    }
                }
            }
        }
        return $treeList;
    }

    /**
     * 统计地区数量
     * @return array
     */
    private function buildCounts()
    {
        $allList = $this->buildAll();
        $counts = [
            'total'    => count($allList),
            'province' => 0,
            'city'     => 0,
            'region'   => 0,
        ];
        $level = [1 => 'province', 2 => 'city', 3 => 'region'];
        foreach ($allList as $item) {
            $counts[$level[$item['level']]]++;
        }
        return $counts;
    }
}
