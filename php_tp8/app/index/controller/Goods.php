<?php

namespace app\index\controller;

use app\api\model\Goods as GoodsModel;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * PC 商品页面。
 */
class Goods
{
    public function detail()
    {
        BaseModel::setApiWxappId(10001);
        $goodsId = (int)request()->param('goods_id', 0);
        $goods = null;
        $skuList = [];

        if ($goodsId > 0) {
            $model = new GoodsModel;
            $goods = $model->getDetail($goodsId);
            if ($goods === false) {
                $goods = null;
            } else {
                $skuList = Db::name('goods_sku')->where('goods_id', $goodsId)->select()->toArray();
            }
        }

        return view('goods/detail', [
            'goods'     => $goods,
            'goodsDesc' => $goods ? html_entity_decode((string)($goods['content'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8') : '',
            'skuList'   => $skuList,
        ]);
    }

    public function list()
    {
        $query = request()->param();
        unset($query['s'], $query['_'], $query['route']);
        header('Location: /h5/goods/list.html?' . http_build_query($query), true, 302);
        exit;
    }
}
