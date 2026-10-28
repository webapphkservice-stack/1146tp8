<?php

namespace app\index\controller;

use app\api\model\Category as CategoryModel;
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
        BaseModel::setApiWxappId(10001);
        $keyword = trim((string)request()->param('keyword', ''));
        $categoryId = (int)request()->param('category_id', 0);
        $page = max(1, (int)request()->param('page', 1));

        $list = (new GoodsModel)->getList([
            'search'      => $keyword,
            'category_id' => $categoryId,
            'page'        => $page,
        ]);

        $categoryList = [];
        try {
            foreach ((new CategoryModel)->getTree() as $category) {
                $categoryList[] = [
                    'category_id' => (int)$category['category_id'],
                    'name'        => $category['name'],
                ];
            }
        } catch (\Throwable $e) {
        }

        return view('goods/list', [
            'list'         => $list,
            'keyword'      => $keyword,
            'categoryId'   => $categoryId,
            'categoryList' => $categoryList,
        ]);
    }
}
