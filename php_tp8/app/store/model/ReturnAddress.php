<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 退货地址模型（服务端渲染版，移植自 TP5 store/model/ReturnAddress.php）
 */
class ReturnAddress extends BaseModel
{
    protected $name = 'return_address';
    protected $pk = 'address_id';
    protected $updateTime = false;

    /**
     * 获取全部退货地址（售后详情下拉用）
     * @return array
     */
    public function getAll()
    {
        return Db::name('return_address')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')->order('address_id', 'desc')
            ->select()->toArray();
    }

    /**
     * 退货地址列表
     * @return \think\Paginator
     */
    public function getList()
    {
        return Db::name('return_address')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')->order('address_id', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);
    }
}
