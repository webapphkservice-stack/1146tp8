<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Cache;
use think\facade\Db;

/**
 * 购物车模型（精简版，沿用 php_my 的缓存方案，缓存 key = cart_<user_id>）
 * - 购物车数据存缓存（非数据库表，yoshop_cart 表不存在）
 * - 商品/规格信息用 Db 直查，规避 TP8 模型差异
 * - 对齐 RN CartScreen：列表返回 goods_list[] + order_total_num
 */
class Cart
{
    /** @var string 错误信息 */
    public $error = '';

    /** @var int 用户id */
    private $userId;

    /** @var array 购物车数据 */
    private $cart = [];

    public function __construct($user)
    {
        $this->userId = is_array($user) ? $user['user_id'] : $user->user_id;
        $this->cart = Cache::get('cart_' . $this->userId) ?: [];
    }

    /** 静态：取某用户购物车总件数（供 goods/detail 回填角标） */
    public static function getTotalNumByUser($userId)
    {
        $cart = Cache::get('cart_' . $userId) ?: [];
        $sum = 0;
        foreach ($cart as $v) {
            $sum += (int)$v['goods_num'];
        }
        return $sum;
    }

    /**
     * 购物车列表（含商品信息）
     * @param string|null $cartIds 索引集（null=全部）
     */
    public function getList($cartIds = null)
    {
        $cartList = $this->getCartList($cartIds);
        $goodsList = [];
        foreach ($cartList as $key => $item) {
            $goods = $this->getGoodsInfo($item['goods_id']);
            if (!$goods) {
                $this->delete($key);
                continue;
            }
            $sku = $this->getSku($item['goods_id'], $item['goods_sku_id']);
            if (!$sku) {
                $this->delete($key);
                continue;
            }
            $price = $sku['goods_price'];
            $goodsList[] = [
                'goods_id'     => $item['goods_id'],
                'goods_sku_id' => $item['goods_sku_id'],
                'spec_sku_id'  => $sku['spec_sku_id'],
                'goods_name'   => $goods['goods_name'],
                'goods_price'  => $price,
                'goods_image'  => $goods['goods_image'],
                'total_num'    => $item['goods_num'],
                'total_price'  => bcmul((string)$price, (string)$item['goods_num'], 2),
            ];
        }
        return $goodsList;
    }

    public function getCartList($cartIds = null)
    {
        if (empty($cartIds)) {
            return $this->cart;
        }
        $cartList = [];
        $indexArr = strpos($cartIds, ',') !== false ? explode(',', $cartIds) : [$cartIds];
        foreach ($indexArr as $index) {
            isset($this->cart[$index]) && $cartList[$index] = $this->cart[$index];
        }
        return $cartList;
    }

    private function getGoodsInfo($goodsId)
    {
        $goods = Db::name('goods')->where('goods_id', $goodsId)
            ->where('goods_status', 10)
            ->where('is_delete', 0)
            ->field('goods_id,goods_name')
            ->find();
        if (!$goods) {
            return false;
        }
        $imgId = Db::name('goods_image')->where('goods_id', $goodsId)
            ->order('id', 'asc')->value('image_id');
        $goods['goods_image'] = BaseModel::getFileUrl($imgId);
        return $goods;
    }

    private function getSku($goodsId, $goodsSkuId)
    {
        return Db::name('goods_sku')->where('goods_id', $goodsId)
            ->where('spec_sku_id', $goodsSkuId)
            ->find();
    }

    /**
     * 加入购物车
     */
    public function add($goodsId, $goodsNum, $goodsSkuId)
    {
        $index = "{$goodsId}_{$goodsSkuId}";
        $cartGoodsNum = $goodsNum + (isset($this->cart[$index]) ? (int)$this->cart[$index]['goods_num'] : 0);
        $goods = $this->getGoodsInfo($goodsId);
        if (!$goods) {
            $this->error = '很抱歉，商品信息不存在或已下架';
            return false;
        }
        $sku = $this->getSku($goodsId, $goodsSkuId);
        if (!$sku) {
            $this->error = '很抱歉，该商品规格不存在';
            return false;
        }
        if ($cartGoodsNum > (int)$sku['stock_num']) {
            $this->error = '很抱歉，商品库存不足';
            return false;
        }
        $this->cart[$index] = [
            'goods_id'     => $goodsId,
            'goods_num'    => $cartGoodsNum,
            'goods_sku_id' => $goodsSkuId,
            'create_time'  => time(),
        ];
        return true;
    }

    /** 减少数量（减到 1 不再减） */
    public function sub($goodsId, $goodsSkuId)
    {
        $index = "{$goodsId}_{$goodsSkuId}";
        if (isset($this->cart[$index]) && $this->cart[$index]['goods_num'] > 1) {
            $this->cart[$index]['goods_num']--;
        }
    }

    /** 删除（支持逗号分隔索引集；为空则清空整个购物车） */
    public function delete($cartIds)
    {
        if (empty($cartIds)) {
            $this->cart = [];
            return;
        }
        $indexArr = strpos($cartIds, ',') !== false ? explode(',', $cartIds) : [$cartIds];
        foreach ($indexArr as $index) {
            if (isset($this->cart[$index])) {
                unset($this->cart[$index]);
            }
        }
    }

    public function getTotalNum()
    {
        $sum = 0;
        foreach ($this->cart as $v) {
            $sum += (int)$v['goods_num'];
        }
        return $sum;
    }

    public function __destruct()
    {
        Cache::set('cart_' . $this->userId, $this->cart, 86400 * 15);
    }

    public function getError()
    {
        return $this->error;
    }
}
