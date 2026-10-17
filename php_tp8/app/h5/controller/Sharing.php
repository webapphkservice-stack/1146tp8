<?php
namespace app\h5\controller;

use app\api\service\sharing\Goods as GoodsService;
use think\facade\Db;

/**
 * H5 - 拼团模块
 */
class Sharing extends Controller
{
    /**
     * 拼团首页（分类 + 全部商品）
     */
    public function index()
    {
        $categoryList = Db::name('sharing_category')
            ->where('wxapp_id', 10001)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        $goods = GoodsService::getList(10001, [])['data'] ?? [];
        return $this->fetch('sharing/index', [
            'categoryList' => $categoryList,
            'goodsList'    => $goods,
            'storeName'    => $this->storeName(),
        ]);
    }

    /**
     * 拼团商品详情
     */
    public function goods()
    {
        $goodsId = (int)$this->request->param('goods_id', 0);
        $goods = GoodsService::getDetail($goodsId);
        if ($goods === false) {
            return $this->fetch('sharing/goods', ['goods' => null, 'storeName' => $this->storeName()]);
        }
        $activeList = GoodsService::getActiveList($goodsId, 10);
        return $this->fetch('sharing/goods', [
            'goods'      => $goods,
            'activeList' => $activeList,
            'storeName'  => $this->storeName(),
        ]);
    }

    /**
     * 拼单详情
     */
    public function active()
    {
        $activeId = (int)$this->request->param('active_id', 0);
        $detail = Db::name('sharing_active')->where('active_id', $activeId)->find();
        if (empty($detail)) {
            return $this->fetch('sharing/active', ['detail' => null, 'storeName' => $this->storeName()]);
        }
        $goods = GoodsService::getDetail($detail['goods_id']);
        return $this->fetch('sharing/active', [
            'detail'    => $detail,
            'goods'     => $goods,
            'storeName' => $this->storeName(),
        ]);
    }
}
