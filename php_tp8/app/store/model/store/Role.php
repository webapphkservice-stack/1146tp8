<?php
namespace app\store\model\store;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商家用户角色模型（服务端渲染版，移植自 TP5 store/model/store/Role.php）
 *
 * TP5 版继承 app\common\model\store\Role（该 common 基类 TP8 未迁移），
 * 此处用 Db 直查自包含实现，保持与 TP8 既有 store 模型一致的风格，
 * 不依赖缺失的 common 基类。
 */
class Role extends BaseModel
{
    protected $name = 'store_role';
    protected $pk = 'role_id';
    protected $updateTime = false;

    /**
     * 角色列表（树形）
     */
    public function getList()
    {
        $all = $this->getAll();
        return $this->formatTreeData($all);
    }

    /**
     * 新增角色
     */
    public function add($data)
    {
        if (empty($data['access'])) {
            $this->error = '请选择权限';
            return false;
        }
        Db::startTrans();
        try {
            $roleId = Db::name('store_role')->insertGetId([
                'role_name' => $data['role_name'],
                'parent_id' => (int)($data['parent_id'] ?? 0),
                'sort'      => (int)($data['sort'] ?? 100),
                'wxapp_id'  => self::getWxappId(),
                'create_time' => time(),
                'update_time' => time(),
            ]);
            (new RoleAccess)->add($roleId, $data['access']);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新角色
     */
    public function edit($data)
    {
        $roleId = (int)$data['role_id'];
        if (empty($data['access'])) {
            $this->error = '请选择权限';
            return false;
        }
        if (!empty($data['parent_id']) && $data['parent_id'] > 0) {
            $parentIds = $this->getTopRoleIds($data['parent_id']);
            if (in_array($roleId, $parentIds)) {
                $this->error = '上级角色不允许设置为当前子角色';
                return false;
            }
        }
        Db::startTrans();
        try {
            Db::name('store_role')->where('role_id', $roleId)->update([
                'role_name'   => $data['role_name'],
                'parent_id'   => (int)($data['parent_id'] ?? 0),
                'sort'        => (int)($data['sort'] ?? 100),
                'update_time' => time(),
            ]);
            (new RoleAccess)->edit($roleId, $data['access']);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 删除角色（不允许有下级角色）
     */
    public function remove($roleId)
    {
        if (Db::name('store_role')->where('parent_id', $roleId)->count() > 0) {
            $this->error = '当前角色下存在子角色，不允许删除';
            return false;
        }
        RoleAccess::deleteAll(['role_id' => $roleId]);
        return Db::name('store_role')->where('role_id', $roleId)->delete();
    }

    /**
     * 角色详情
     */
    public static function detail($roleId)
    {
        return Db::name('store_role')->where('role_id', $roleId)->find() ?: [];
    }

    /**
     * 获取所有角色
     */
    private function getAll()
    {
        return Db::name('store_role')
            ->order('sort', 'asc')
            ->order('create_time', 'asc')
            ->select()->toArray();
    }

    /**
     * 获取所有上级 id 集
     */
    private function getTopRoleIds($role_id, &$all = null)
    {
        static $ids = [];
        is_null($all) && $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['role_id'] == $role_id && $item['parent_id'] > 0) {
                $ids[] = $item['parent_id'];
                $this->getTopRoleIds($item['parent_id'], $all);
            }
        }
        return $ids;
    }

    /**
     * 格式化为树形列表（带层级前缀）
     */
    private function formatTreeData(&$all, $parent_id = 0, $deep = 1)
    {
        static $tempTreeArr = [];
        static $init = false;
        if ($parent_id === 0) {
            $tempTreeArr = [];
        }
        foreach ($all as $key => $val) {
            if ($val['parent_id'] == $parent_id) {
                $val['deep'] = $deep;
                $val['role_name_h1'] = $this->htmlPrefix($deep) . $val['role_name'];
                $tempTreeArr[] = $val;
                $this->formatTreeData($all, $val['role_id'], $deep + 1);
            }
        }
        return $tempTreeArr;
    }

    private function htmlPrefix($deep)
    {
        $prefix = '';
        if ($deep > 1) {
            for ($i = 1; $i <= $deep - 1; $i++) {
                $prefix .= '&nbsp;&nbsp;&nbsp;├ ';
            }
            $prefix .= '&nbsp;';
        }
        return $prefix;
    }
}
