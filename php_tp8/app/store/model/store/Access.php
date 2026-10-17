<?php
namespace app\store\model\store;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商家用户权限模型（移植自 TP5 common/model/store/Access.php）
 * 对应表 store_access：权限节点（url）清单，用于角色授权与菜单过滤。
 */
class Access extends BaseModel
{
    protected $name = 'store_access';
    protected $pk = 'access_id';

    /**
     * 获取所有权限
     * @return array
     */
    public static function getAll()
    {
        return Db::name('store_access')
            ->order('sort', 'asc')
            ->order('create_time', 'asc')
            ->select()->toArray();
    }

    /**
     * 权限信息
     * @param int|array $where
     * @return array
     */
    public static function detail($where)
    {
        $query = Db::name('store_access');
        is_array($where) ? $query->where($where) : $query->where('access_id', $where);
        return $query->find() ?: [];
    }

    /**
     * 获取权限 url 集
     * @param array $accessIds
     * @return array
     */
    public static function getAccessUrls($accessIds)
    {
        $urls = [];
        foreach (self::getAll() as $item) {
            if (in_array($item['access_id'], (array)$accessIds)) {
                $urls[] = $item['url'];
            }
        }
        return $urls;
    }

    /**
     * 获取权限列表（jstree 格式，角色授权页使用）
     * @param int|null $roleId
     * @return string
     */
    public function getJsTree($roleId = null)
    {
        $accessIds = is_null($roleId) ? [] : RoleAccess::getAccessIds($roleId);
        $jsTree = [];
        foreach (self::getAll() as $item) {
            $jsTree[] = [
                'id'     => $item['access_id'],
                'parent' => $item['parent_id'] > 0 ? $item['parent_id'] : '#',
                'text'   => $item['name'],
                'state'  => [
                    'selected' => in_array($item['access_id'], $accessIds)
                        && !$this->hasChildren($item['access_id']),
                ],
            ];
        }
        return json_encode($jsTree);
    }

    /**
     * 是否存在子级
     * @param int $accessId
     * @return bool
     */
    private function hasChildren($accessId)
    {
        return (int)Db::name('store_access')->where('parent_id', $accessId)->count() > 0;
    }
}
