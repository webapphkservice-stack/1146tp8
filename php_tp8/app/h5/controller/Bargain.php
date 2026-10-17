<?php
namespace app\h5\controller;

use app\api\service\bargain\Active as ActiveService;
use think\facade\Db;

/**
 * H5 - 砍价模块
 */
class Bargain extends Controller
{
    /**
     * 砍价会场（首页）
     */
    public function index()
    {
        $list = ActiveService::getHallList(10001, [])['data'] ?? [];
        return $this->fetch('bargain/index', [
            'list'      => $list,
            'storeName' => $this->storeName(),
        ]);
    }

    /**
     * 砍价商品详情
     */
    public function goods()
    {
        $activeId = (int)$this->request->param('active_id', 0);
        $active = ActiveService::getDetail($activeId);
        if ($active === false) {
            return $this->fetch('bargain/goods', ['active' => null, 'storeName' => $this->storeName()]);
        }
        $goods = ActiveService::getGoodsBrief($active['goods_id']);
        return $this->fetch('bargain/goods', [
            'active'    => $active,
            'goods'     => $goods,
            'storeName' => $this->storeName(),
        ]);
    }

    /**
     * 砍价任务（我的砍价列表）
     */
    public function task()
    {
        // H5 端先展示公开进行中的砍价活动
        $list = Db::name('bargain_active')
            ->where('wxapp_id', 10001)
            ->where('status', 1)
            ->order('active_id', 'desc')
            ->limit(20)
            ->select()
            ->toArray();
        return $this->fetch('bargain/task', [
            'list'      => $list,
            'storeName' => $this->storeName(),
        ]);
    }
}
