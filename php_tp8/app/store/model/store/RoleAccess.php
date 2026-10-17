<?php
namespace app\store\model\store;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 角色-权限关系模型（移植自 TP5 common/model/store/RoleAccess.php）
 * 对应表 store_role_access
 */
class RoleAccess extends BaseModel
{
    protected $name = 'store_role_access';
    protected $updateTime = false;

    /**
     * 根据角色 id 集获取权限 id 集
     * @param int|array $roleIds
     * @return array
     */
    public static function getAccessIds($roleIds)
    {
        if (empty($roleIds)) {
            return [];
        }
        return Db::name('store_role_access')
            ->whereIn('role_id', (array)$roleIds)
            ->column('access_id');
    }

    /**
     * 新增关系记录
     * @param int $roleId
     * @param array $accessIds
     * @return int
     */
    public function add($roleId, $accessIds)
    {
        $data = [];
        foreach ((array)$accessIds as $accessId) {
            $data[] = [
                'role_id'    => $roleId,
                'access_id'  => $accessId,
                'wxapp_id'   => self::getWxappId(),
            ];
        }
        return empty($data) ? 0 : Db::name('store_role_access')->insertAll($data);
    }

    /**
     * 更新关系记录（先删后增）
     * @param int $roleId
     * @param array $newAccess
     * @return bool
     */
    public function edit($roleId, $newAccess)
    {
        Db::name('store_role_access')->where('role_id', $roleId)->delete();
        $this->add($roleId, $newAccess);
        return true;
    }
}
