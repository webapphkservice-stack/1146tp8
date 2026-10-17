<?php
namespace app\h5\controller;

use app\api\service\sharp\Active as ActiveService;

/**
 * H5 - 整点秒杀模块
 */
class Sharp extends Controller
{
    /**
     * 秒杀会场首页（场次 tab + 首场商品）
     */
    public function index()
    {
        $service = new ActiveService(10001);
        $data = $service->getHallIndex();
        return $this->fetch('sharp/index', [
            'tabbar'    => $data['tabbar'] ?? [],
            'goodsList' => $data['goodsList'] ?? [],
            'storeName' => $this->storeName(),
        ]);
    }

    /**
     * 秒杀商品详情
     */
    public function goods()
    {
        $activeTimeId = (int)$this->request->param('active_time_id', 0);
        $sharpGoodsId = (int)$this->request->param('sharp_goods_id', 0);
        $service = new ActiveService(10001);
        $detail = $service->getActiveGoodsDetail($activeTimeId, $sharpGoodsId);
        if ($detail === false) {
            return $this->fetch('sharp/goods', ['detail' => null, 'storeName' => $this->storeName()]);
        }
        return $this->fetch('sharp/goods', [
            'detail'    => $detail,
            'storeName' => $this->storeName(),
        ]);
    }
}
