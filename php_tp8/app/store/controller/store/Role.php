<?php
namespace app\store\controller\store;

use app\store\controller\Controller;
use app\store\model\store\Role as RoleModel;
use app\store\model\store\Access as AccessModel;

/**
 * 商家用户角色控制器（移植自 TP5 store/controller/store/Role.php）
 */
class Role extends Controller
{
    /**
     * 角色列表
     */
    public function index()
    {
        $model = new RoleModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加角色
     */
    public function add()
    {
        $model = new RoleModel;
        if (!$this->request->isAjax()) {
            $accessList = (new AccessModel)->getJsTree();
            $roleList = $model->getList();
            return $this->fetch('add', compact('accessList', 'roleList'));
        }
        if ($model->add($this->postData('role'))) {
            return $this->renderSuccess('添加成功', (string)url('store.role/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 更新角色
     * @param int $role_id
     */
    public function edit($role_id)
    {
        $model = RoleModel::detail($role_id);
        if (!$this->request->isAjax()) {
            $accessList = (new AccessModel)->getJsTree($model['role_id']);
            $roleList = (new RoleModel)->getList();
            return $this->fetch('edit', compact('model', 'accessList', 'roleList'));
        }
        $data = $this->postData('role');
        $data['role_id'] = $role_id;
        if ((new RoleModel)->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('store.role/index'));
        }
        return $this->renderError((new RoleModel)->getError() ?: '更新失败');
    }

    /**
     * 删除角色
     * @param int $role_id
     */
    public function delete($role_id)
    {
        $model = new RoleModel;
        if (!$model->remove($role_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
