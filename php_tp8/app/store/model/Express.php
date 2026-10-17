<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;
use think\facade\Request;

/**
 * 物流公司模型
 * 路由设置 HTML CRUD 走这里；发货时 JSON API 直接用 Db::name('express')（见 controller/Express.php lists()）
 */
class Express extends BaseModel
{
    protected $name = 'express';
    protected $pk = 'express_id';
    protected $updateTime = false;

    /**
     * 获取全部物流公司（轻量数组，供订单详情页下拉选择）
     * @return array
     */
    public static function getAll()
    {
        return Db::name('express')->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')->select()->toArray();
    }

    /**
     * 后台分页列表
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        return $this->where('wxapp_id', self::getWxappId())
            ->order(['sort' => 'asc', $this->getPk() => 'desc'])
            ->paginate(15, false, [
                'query' => Request::instance()->request()
            ]);
    }

    /**
     * 详情
     * @param int $express_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($express_id)
    {
        return self::get($express_id);
    }

    /**
     * 新增
     * @param array $data
     * @return false|int
     */
    public function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        return $this->allowField(true)->save($data);
    }

    /**
     * 编辑
     * @param array $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data);
    }

    /**
     * 删除（有订单引用则阻止）
     * @return bool|int
     */
    public function remove()
    {
        $count = Db::name('order')->where('express_id', $this['express_id'])->count();
        if ($count > 0) {
            $this->error = "当前物流公司已被 {$count} 个订单使用，不允许删除";
            return false;
        }
        return $this->delete();
    }
}
