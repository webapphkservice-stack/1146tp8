<?php

namespace app\store\model\store\shop;

use app\common\model\store\shop\Clerk as ClerkModel;

/**
 * 商家门店店员模型（服务端渲染版）
 * Class Clerk
 * @package app\store\model\store\shop
 */
class Clerk extends ClerkModel
{
    const FORM_SCENE_ADD = 'add';
    const FORM_SCENE_EDIT = 'edit';

    /**
     * 获取列表数据（含门店、用户信息）
     * @param int $status 状态
     * @param int $shop_id 门店id
     * @param string $search 店员姓名/手机号
     * @return \think\Paginator
     */
    public function getList($status = -1, $shop_id = 0, $search = '')
    {
        $status > -1 && $this->where('status', '=', (int)$status);
        $shop_id > 0 && $this->where('shop_id', '=', (int)$shop_id);
        !empty($search) && $this->where('real_name|mobile', 'like', "%{$search}%");
        return $this->with(['user', 'shop'])
            ->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->paginate(15);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        if (!$this->validateForm($data, self::FORM_SCENE_ADD)) {
            return false;
        }
        $data['wxapp_id'] = self::getWxappId();
        return $this->save($data);
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (!$this->validateForm($data, self::FORM_SCENE_EDIT)) {
            return false;
        }
        return $this->save($data) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 获取门店下全部店员（送水配送员设置页）
     * @param int $shop_id
     * @return \think\Collection
     */
    public static function getListByShop($shop_id)
    {
        return (new static)->where('shop_id', '=', (int)$shop_id)
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'asc'])
            ->select();
    }

    /**
     * 表单验证
     * @param $data
     * @param string $scene
     * @return bool
     */
    private function validateForm($data, $scene = self::FORM_SCENE_ADD)
    {
        if ($scene === self::FORM_SCENE_ADD) {
            if (!isset($data['user_id']) || empty($data['user_id'])) {
                $this->error = '请选择用户';
                return false;
            }
            if (self::detail(['user_id' => $data['user_id'], 'is_delete' => 0])) {
                $this->error = '该用户已经是店员，无需重复添加';
                return false;
            }
        }
        return true;
    }
}
