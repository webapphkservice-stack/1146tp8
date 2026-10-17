<?php
namespace app\admin\controller\store;

use think\facade\Db;
use app\common\controller\Controller;

/**
 * 商户后台权限管理 RBAC（移植自 php admin/controller/store/Access.php）
 *
 * 纯 JSON：
 *   GET  /admin/store.access/index                 权限树（扁平，带 deep / name_h1）
 *   POST /admin/store.access/add                   新增
 *   POST /admin/store.access/edit?access_id=1      编辑
 *   POST /admin/store.access/delete?access_id=1    删除
 *
 * 说明：store_access 表无 wxapp_id 列（TP5 的 add() 写了但被 allowField 过滤），
 * 这里不再写入；权限以 parent_id 自引用构成菜单树，
 * url 为商户后台页面路由（如 store.user/index、goods/index）。
 */
class Access extends Controller
{
    /**
     * 权限列表（扁平树）
     */
    public function index()
    {
        $list = Db::name('store_access')
            ->order('sort', 'asc')
            ->order('create_time', 'asc')
            ->select()->toArray();
        return $this->renderSuccess(['list' => $this->formatTree($list)], 'ok');
    }

    /**
     * 新增
     */
    public function add()
    {
        $data = $this->filterPost();
        if (empty($data['name'])) {
            return $this->renderError('权限名称不能为空');
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        $accessId = Db::name('store_access')->insertGetId($data);
        return $this->renderSuccess(['access_id' => $accessId], '添加成功');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $accessId = (int)$this->request->param('access_id', 0);
        $row      = Db::name('store_access')->where('access_id', $accessId)->find();
        if (!$row) {
            return $this->renderError('记录不存在');
        }
        $data = $this->filterPost();
        $parentId = (int)($data['parent_id'] ?? 0);
        // 禁止把上级设成自己的子级（会造成树断裂）
        if ($parentId > 0 && in_array($parentId, $this->getChildIds($accessId), true)) {
            return $this->renderError('上级权限不允许设置为当前子权限');
        }
        $data['update_time'] = time();
        Db::name('store_access')->where('access_id', $accessId)->update($data);
        return $this->renderSuccess([], '更新成功');
    }

    /**
     * 删除（存在子权限时拒绝）
     */
    public function delete()
    {
        $accessId = (int)$this->request->param('access_id', 0);
        $row      = Db::name('store_access')->where('access_id', $accessId)->find();
        if (!$row) {
            return $this->renderError('记录不存在');
        }
        $childCount = Db::name('store_access')->where('parent_id', $accessId)->count();
        if ($childCount > 0) {
            return $this->renderError('当前权限下存在子权限，请先删除');
        }
        Db::name('store_access')->where('access_id', $accessId)->delete();
        return $this->renderSuccess([], '删除成功');
    }

    /**
     * 递归取某权限的全部后代 id（含自身）
     * @param int $accessId
     * @return array
     */
    private function getChildIds($accessId)
    {
        $ids   = [(int)$accessId];
        $queue = [(int)$accessId];
        while (!empty($queue)) {
            $current = array_pop($queue);
            $children = Db::name('store_access')->where('parent_id', $current)->column('access_id');
            foreach ($children as $childId) {
                $childId = (int)$childId;
                if (!in_array($childId, $ids, true)) {
                    $ids[] = $childId;
                    $queue[] = $childId;
                }
            }
        }
        return $ids;
    }

    /**
     * 组装扁平树（附加 deep 层级与 name_h1 缩进展示名）
     * @param array $list
     * @return array
     */
    private function formatTree(array $list, $parentId = 0, $deep = 0)
    {
        $result = [];
        foreach ($list as $item) {
            if ((int)$item['parent_id'] !== (int)$parentId) {
                continue;
            }
            $item['deep']    = $deep;
            $item['name_h1'] = $deep > 0
                ? str_repeat('&nbsp;&nbsp;&nbsp;', $deep) . '├ ' . $item['name']
                : $item['name'];
            $result[] = $item;
            $children = $this->formatTree($list, (int)$item['access_id'], $deep + 1);
            $result   = array_merge($result, $children);
        }
        return $result;
    }

    /**
     * 规范化提交数据
     * @return array
     */
    private function filterPost()
    {
        $data = $this->request->param('access/a', []);
        return [
            'name'      => trim((string)($data['name'] ?? '')),
            'url'       => trim((string)($data['url'] ?? '')),
            'parent_id' => (int)($data['parent_id'] ?? 0),
            'sort'      => (int)($data['sort'] ?? 100),
        ];
    }
}
