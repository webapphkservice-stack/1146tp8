<?php
namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\store\Role as RoleModel;
use app\store\model\store\User as StoreUserModel;
use app\store\model\store\UserRole;

/**
 * 商家用户控制器（移植自 TP5 store/controller/store/User.php）
 */
class User extends Controller
{
    /**
     * 用户列表
     */
    public function index()
    {
        $model = new StoreUserModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加管理员
     */
    public function add()
    {
        $model = new StoreUserModel;
        if (!$this->request->isAjax()) {
            $roleList = (new RoleModel)->getList();
            return $this->fetch('add', compact('roleList'));
        }
        if ($model->add($this->postData('user'))) {
            return $this->renderSuccess('添加成功', (string)url('store.user/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新管理员
     * @param int $user_id
     */
    public function edit($user_id)
    {
        $model = StoreUserModel::detail($user_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', [
                'model'    => $model,
                'roleList' => (new RoleModel)->getList(),
                'roleIds'  => UserRole::getRoleIds($model['store_user_id']),
            ]);
        }
        $data = $this->postData('user');
        $data['store_user_id'] = $user_id;
        if ((new StoreUserModel)->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('store.user/index'));
        }
        return $this->renderError((new StoreUserModel)->getError() ?: '更新失败');
    }

    /**
     * 删除管理员
     * @param int $user_id
     */
    public function delete($user_id)
    {
        $model = new StoreUserModel;
        if (!$model->setDelete($user_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 更新当前管理员信息
     */
    public function renew()
    {
        $model = StoreUserModel::detail($this->store['user']['store_user_id']);
        if ($this->request->isAjax()) {
            if ((new StoreUserModel)->renew($this->postData('user'))) {
                return $this->renderSuccess('更新成功');
            }
            return $this->renderError((new StoreUserModel)->getError() ?: '更新失败');
        }
        return $this->fetch('renew', compact('model'));
    }
}
