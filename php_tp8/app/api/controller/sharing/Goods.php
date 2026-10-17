<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\api\service\sharing\Goods as GoodsService;

/**
 * 拼团商品：列表 / 详情
 */
class Goods extends Controller
{
    /**
     * 拼团商品列表
     */
    public function lists()
    {
        $param = $this->request->param();
        // RN 消费 res.list 为纯数组，取分页 data
        $list = GoodsService::getList($this->getWxappId(), $param)['data'];
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 拼团商品详情（含进行中拼单列表）
     */
    public function detail($goods_id)
    {
        $goods = GoodsService::getDetail($goods_id);
        if ($goods === false) {
            return $this->renderError('商品信息不存在或已下架');
        }
        $goodsList = GoodsService::getActiveList($goods_id, 5);
        $detail = [
            'people_num' => $goods['people_num'],
            'people'     => $goods['people'],
        ];
        // 键名必须为 goodsList（RN SharingDetailScreen 解析 {goods, detail, goodsList}）
        return $this->renderSuccess([
            'goods'     => $goods,
            'detail'    => $detail,
            'goodsList' => $goodsList,
        ]);
    }

    /**
     * 拼团商品海报（GD 合成）
     */
    public function poster()
    {
        $user = $this->getUser(true);
        $goodsId = (int)$this->request->param('goods_id', 0);
        $sg = \think\facade\Db::name('sharing_goods')->where('goods_id', $goodsId)
            ->where('is_delete', 0)->find();
        if (empty($sg)) {
            return $this->renderError('拼团商品不存在');
        }
        $sku = \think\facade\Db::name('sharing_goods_sku')->where('goods_id', $goodsId)
            ->order('sharing_price', 'asc')->find();
        try {
            $poster = (new \app\common\service\qrcode\GoodsPoster($this->getWxappId()))->make([
                'goods_id' => $goodsId,
                'goods_name' => $sg['goods_name'],
                'price' => $sku['sharing_price'] ?? '0.00',
                'page' => 'pages/sharing/goods',
            ]);
            return $this->renderSuccess(['poster' => $poster]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
