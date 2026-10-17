<?php
namespace app\api\controller;

use app\api\model\UserAddress as UserAddressModel;

/**
 * 收货地址控制器
 * Class Address
 * @package app\api\controller
 */
class Address extends Controller
{
    /**
     * 地址列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $model = new UserAddressModel;
        $list = $model->getList($user['user_id']);
        $defaultId = $model->getDefaultId($user['user_id']);
        return $this->renderSuccess([
            'list'       => $list,
            'default_id' => $defaultId,
        ]);
    }

    /**
     * 地址详情
     */
    public function detail()
    {
        $user = $this->getUser(true);
        $addressId = (int)$this->request->param('address_id');
        $model = new UserAddressModel;
        $data = $model->detail($user['user_id'], $addressId);
        if ($data === false) {
            return $this->renderError('地址不存在');
        }
        return $this->renderSuccess($data);
    }

    /**
     * 新增地址
     */
    public function add()
    {
        $user = $this->getUser(true);
        $data = $this->postData();
        $model = new UserAddressModel;
        $addrId = $model->add($user['user_id'], $data);
        if ($addrId === false) {
            return $this->renderError($model->getError() ?: '保存失败');
        }
        return $this->renderSuccess([], '保存成功');
    }

    /**
     * 编辑地址
     */
    public function edit()
    {
        $user = $this->getUser(true);
        $data = $this->postData();
        $model = new UserAddressModel;
        if (!$model->edit($user['user_id'], $data)) {
            return $this->renderError($model->getError() ?: '保存失败');
        }
        return $this->renderSuccess([], '保存成功');
    }

    /**
     * 删除地址
     */
    public function delete()
    {
        $user = $this->getUser(true);
        $addressId = (int)$this->request->param('address_id');
        $model = new UserAddressModel;
        $model->remove($user['user_id'], $addressId);
        return $this->renderSuccess([], '删除成功');
    }

    /**
     * 设为默认
     */
    public function setDefault()
    {
        $user = $this->getUser(true);
        $addressId = (int)$this->request->param('address_id');
        $model = new UserAddressModel;
        $model->setDefault($user['user_id'], $addressId);
        return $this->renderSuccess([], '设置成功');
    }
}
