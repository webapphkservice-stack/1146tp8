<?php
namespace app\api\controller;

use app\api\model\Goods as GoodsModel;
use app\api\model\Cart as CartModel;
use app\common\service\qrcode\GoodsPoster;
use think\facade\Db;

/**
 * 商品控制器
 * Class Goods
 * @package app\api\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     * @return array
     */
    public function lists()
    {
        $param = array_merge($this->request->param(), ['status' => 10]);
        $model = new GoodsModel;
        $list = $model->getList($param);
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 商品详情
     * @return array
     */
    public function detail()
    {
        $goodsId = (int)$this->request->param('goods_id');
        $model = new GoodsModel;
        $goods = $model->getDetail($goodsId);
        if ($goods === false) {
            return $this->renderError($model->getError() ?: '商品信息不存在');
        }
        $user = $this->getUser();
        $cartTotalNum = $user ? CartModel::getTotalNumByUser($user['user_id']) : 0;
        return $this->renderSuccess([
            // 商品详情
            'detail' => $goods,
            // 购物车商品总数量（已登录则回填，未登录为 0）
            'cart_total_num' => $cartTotalNum,
        ]);
    }

    /**
     * 生成商品海报（移植自 TP5 goods/poster）
     * @return array
     */
    public function poster()
    {
        $goodsId = (int)$this->request->param('goods_id');
        $goods = Db::name('goods')
            ->where('goods_id', $goodsId)
            ->where('wxapp_id', $this->getWxappId())
            ->where('is_delete', 0)
            ->field('goods_id, goods_name')
            ->find();
        if (empty($goods)) {
            return $this->renderError('商品信息不存在');
        }
        // 最低价（与商品列表 min 价语义一致）
        $goods['price'] = (float)(Db::name('goods_sku')
            ->where('goods_id', $goodsId)->min('goods_price') ?: 0);
        $goods['page'] = 'pages/goods/index';
        $Qrcode = new GoodsPoster($this->getWxappId());
        $qrcode = $Qrcode->make($goods);
        return $this->renderSuccess(['qrcode' => $qrcode]);
    }
}
