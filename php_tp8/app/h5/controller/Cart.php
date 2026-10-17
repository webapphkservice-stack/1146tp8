<?php

namespace app\h5\controller;

use app\api\model\Cart as CartModel;

/**
 * 手机网站 - 购物车
 *
 * 数据存 Cache('cart_<user_id>')，与 api\model\Cart 共用。
 */
class Cart extends Controller
{
    /** 购物车列表页（GET） */
    public function index()
    {
        $h5User = $this->h5User();
        if (!$h5User) {
            return redirect((string)url('passport/login', ['redirect' => '/h5/cart/index.html']));
        }
        $cart = new CartModel($h5User);
        $list = $cart->getList();
        $totalNum = $cart->getTotalNum();
        $totalPrice = '0.00';
        foreach ($list as $item) {
            $totalPrice = bcadd($totalPrice, (string)$item['total_price'], 2);
        }
        return $this->fetch('cart/index', [
            'storeName'   => $this->storeName(),
            'list'        => $list,
            'totalNum'    => $totalNum,
            'totalPrice'  => $totalPrice,
        ]);
    }

    /** POST: add / sub / delete */
    public function add()
    {
        return $this->modifyCart('add');
    }

    public function sub()
    {
        return $this->modifyCart('sub');
    }

    public function delete()
    {
        return $this->modifyCart('delete');
    }

    private function modifyCart($action)
    {
        $h5User = $this->h5User();
        if (!$h5User) {
            return json(['code' => 0, 'msg' => '请先登录', 'redirect' => (string)url('passport/login')]);
        }
        $post = $this->request->post();
        $cart = new CartModel($h5User);
        switch ($action) {
            case 'add':
                $goodsId     = (int)($post['goods_id'] ?? 0);
                $goodsSkuId  = (int)($post['goods_sku_id'] ?? 0);
                $goodsNum    = max(1, (int)($post['goods_num'] ?? 1));
                if ($goodsId <= 0 || $goodsSkuId < 0) {
                    return json(['code' => 0, 'msg' => '参数错误']);
                }
                if (!$cart->add($goodsId, $goodsNum, $goodsSkuId)) {
                    return json(['code' => 0, 'msg' => $cart->getError()]);
                }
                break;
            case 'sub':
                $goodsId    = (int)($post['goods_id'] ?? 0);
                $goodsSkuId = (int)($post['goods_sku_id'] ?? 0);
                $cart->sub($goodsId, $goodsSkuId);
                break;
            case 'delete':
                $cartIds = trim((string)($post['cart_ids'] ?? ''));
                $cart->delete($cartIds);
                break;
        }
        $list = $cart->getList();
        $totalPrice = '0.00';
        foreach ($list as $item) {
            $totalPrice = bcadd($totalPrice, (string)$item['total_price'], 2);
        }
        return json([
            'code'       => 1,
            'msg'        => '操作成功',
            'data'       => [
                'list'       => $list,
                'total_num'  => $cart->getTotalNum(),
                'total_price'=> $totalPrice,
            ],
        ]);
    }
}
