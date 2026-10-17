<?php
namespace app\store\model\store;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商家用户-角色关系模型（移植自 TP5 common/model/store/UserRole.php）
 * 对应表 store_user_role
 */
class UserRole extends BaseModel
{
    protected $name = 'store_user_role';
    protected $updateTime = false;

    /**
     * 获取用户所属角色 id 集
     * @param int $storeUserId
     * @return array
     */
    public static function getRoleIds($storeUserId)
    {
        return Db::name('store_user_role')
            ->where('store_user_id', $storeUserId)
            ->column('role_id');
    }

    /**
     * 新增关系记录
     * @param int $storeUserId
     * @param array $roleIds
     * @return int
     */
    public function add($storeUserId, $roleIds)
    {
        $data = [];
        foreach ((array)$roleIds as $roleId) {
            $data[] = [
                'store_user_id' => $storeUserId,
                'role_id'       => $roleId,
                'wxapp_id'      => self::getWxappId(),
            ];
        }
        return empty($data) ? 0 : Db::name('store_user_role')->insertAll($data);
    }

    /**
     * 更新关系记录（先删后增）
     * @param int $storeUserId
     * @param array $newRole
     * @return bool
     */
    public function edit($storeUserId, $newRole)
    {
        Db::name('store_user_role')->where('store_user_id', $storeUserId)->delete();
        $this->add($storeUserId, $newRole);
        return true;
    }

    /**
     * 删除指定用户的所有角色关系
     * @param int $storeUserId
     * @return int
     */
    public static function deleteAll($storeUserId)
    {
        return Db::name('store_user_role')->where('store_user_id', $storeUserId)->delete();
    }
}
