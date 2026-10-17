<?php
namespace app\api\controller\sharp;

use app\api\controller\Controller;
use app\api\service\sharp\Active as ActiveService;

/**
 * 整点秒杀-商品
 *
 * 路由（免登录；wxapp_id 由 Token 中间件注入）：
 *  GET sharp/goods/lists  —— 某场次的秒杀商品列表
 *  GET sharp/goods/detail —— 秒杀商品详情（活动信息 + 商品 + SKU）
 */
class Goods extends Controller
{
    /**
     * 场次商品列表
     */
    public function lists()
    {
        $activeTimeId = (int)$this->request->param('active_time_id', 0);
        if ($activeTimeId <= 0) {
            return $this->renderError('缺少参数：active_time_id');
        }
        $service = new ActiveService($this->getWxappId());
        $list = $service->getGoodsListByActiveTimeId($activeTimeId);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 秒杀商品详情
     */
    public function detail()
    {
        $activeTimeId = (int)$this->request->param('active_time_id', 0);
        $sharpGoodsId = (int)$this->request->param('sharp_goods_id', 0);
        if ($activeTimeId <= 0 || $sharpGoodsId <= 0) {
            return $this->renderError('缺少参数：active_time_id / sharp_goods_id');
        }
        $service = new ActiveService($this->getWxappId());
        $data = $service->getActiveGoodsDetail($activeTimeId, $sharpGoodsId);
        if ($data === false) {
            return $this->renderError($service->getError() ?: '秒杀商品不存在');
        }
        return $this->renderSuccess($data);
    }

    /**
     * 秒杀商品海报（GD 合成）
     */
    public function poster()
    {
        $user = $this->getUser(true);
        $sharpGoodsId = (int)$this->request->param('sharp_goods_id', 0);
        $sg = \think\facade\Db::name('sharp_goods')->where('sharp_goods_id', $sharpGoodsId)
            ->where('is_delete', 0)->find();
        if (empty($sg)) {
            return $this->renderError('秒杀商品不存在');
        }
        $sku = \think\facade\Db::name('sharp_goods_sku')->where('sharp_goods_id', $sharpGoodsId)
            ->order('seckill_price', 'asc')->find();
        $goods = \think\facade\Db::name('goods')->where('goods_id', $sg['goods_id'])->find();
        try {
            $poster = (new \app\common\service\qrcode\GoodsPoster($this->getWxappId()))->make([
                'goods_id' => (int)$sg['goods_id'],
                'goods_name' => $goods['goods_name'] ?? '',
                'price' => $sku['seckill_price'] ?? '0.00',
                'page' => 'pages/sharp/goods',
            ]);
            return $this->renderSuccess(['poster' => $poster]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
