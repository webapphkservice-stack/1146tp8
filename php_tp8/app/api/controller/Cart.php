<?php
namespace app\api\controller;

use app\api\model\Cart as CartModel;

/**
 * 购物车控制器
 * Class Cart
 * @package app\api\controller
 */
class Cart extends Controller
{
    /**
     * 购物车列表
     */
    public function lists()
    {
        $user = $this->getUser(true);
        $model = new CartModel($user);
        $goodsList = $model->getList();
        $totalNum = $model->getTotalNum();
        return $this->renderSuccess([
            'goods_list'      => $goodsList,
            'order_total_num' => $totalNum,
        ]);
    }

    /**
     * 加入购物车 / 增加数量
     */
    public function add()
    {
        $user = $this->getUser(true);
        $model = new CartModel($user);
        $goodsId = (int)$this->request->param('goods_id');
        $goodsNum = (int)$this->request->param('goods_num', 1);
        $goodsSkuId = (string)$this->request->param('goods_sku_id', '0');
        if (!$model->add($goodsId, $goodsNum, $goodsSkuId)) {
            return $this->renderError($model->getError() ?: '加入购物车失败');
        }
        return $this->renderSuccess(['cart_total_num' => $model->getTotalNum()], '加入购物车成功');
    }

    /**
     * 减少数量（减到 1 不再减）
     */
    public function sub()
    {
        $user = $this->getUser(true);
        $model = new CartModel($user);
        $goodsId = (int)$this->request->param('goods_id');
        $goodsSkuId = (string)$this->request->param('goods_sku_id', '0');
        $model->sub($goodsId, $goodsSkuId);
        return $this->renderSuccess();
    }

    /**
     * 删除购物车商品（goods_sku_id 实为购物车索引键 goods_id_goods_sku_id）
     */
    public function delete()
    {
        $user = $this->getUser(true);
        $model = new CartModel($user);
        $cartIds = (string)$this->request->param('goods_sku_id', '');
        $model->delete($cartIds);
        return $this->renderSuccess();
    }
}
