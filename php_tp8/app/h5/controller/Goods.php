<?php

namespace app\h5\controller;

use app\api\model\Goods as GoodsModel;
use app\api\model\Cart as CartModel;
use app\api\model\Category as CategoryModel;
use think\facade\Db;

/**
 * 手机网站 - 商品详情
 */
class Goods extends Controller
{
    public function list()
    {
        $keyword = trim($this->request->param('keyword', ''));
        $categoryId = (int)$this->request->param('category_id', 0);
        $model = new GoodsModel;
        $list = $model->getList([
            'search'       => $keyword,
            'category_id'  => $categoryId,
            'pagesize'     => 20,
        ]);

        // 顶级分类（页面分类药丸筛选）
        $categoryList = [];
        try {
            foreach ((new CategoryModel)->getTree() as $c) {
                $categoryList[] = ['category_id' => (int)$c['category_id'], 'name' => $c['name']];
            }
        } catch (\Throwable $e) {
        }

        // 模板名用完整路径（goods/list）：旧链接 s=/home/index/goods 经 Index::goods
        // 委托进来时，短名 'list' 会按 Index 控制器解析成 view/index/list.php 而找不到
        return $this->fetch('goods/list', [
            'list'         => $list,
            'keyword'      => $keyword,
            'storeName'    => $this->storeName(),
            'categoryList' => $categoryList,
            'categoryId'   => $categoryId,
        ]);
    }

    public function detail($goodsId = 0)
    {
        $goodsId = (int)$goodsId ?: (int)$this->request->param('goods_id', 0);
        $goods = null;
        $cartTotalNum = 0;
        $h5User = $this->h5User();

        if ($goodsId) {
            $model = new GoodsModel;
            $goods = $model->getDetail($goodsId);
            if ($goods === false) $goods = null;
        }

        if ($h5User) {
            $cartTotalNum = CartModel::getTotalNumByUser((int)$h5User['user_id']);
        }

        $skuList = [];
        if ($goods) {
            $skuList = Db::name('goods_sku')->where('goods_id', (int)$goods['goods_id'])->select()->toArray();
        }

        return $this->fetch('detail', [
            'goods'         => $goods,
            'storeName'     => $this->storeName(),
            'h5User'        => $h5User,
            'cartTotalNum'  => $cartTotalNum,
            'skuList'       => $skuList,
        ]);
    }
}
