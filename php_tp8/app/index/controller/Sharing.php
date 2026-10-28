<?php

namespace app\index\controller;

use app\api\service\sharing\Goods as SharingGoods;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * PC 拼团页面。
 */
class Sharing
{
    public function index()
    {
        BaseModel::setApiWxappId(10001);
        $page = max(1, (int)request()->param('page', 1));
        $categoryId = (int)request()->param('category_id', 0);

        $list = SharingGoods::getList(10001, [
            'page'        => $page,
            'category_id' => $categoryId,
        ]);

        $categoryList = Db::name('sharing_category')
            ->where('wxapp_id', 10001)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return view('sharing/index', [
            'active'       => 'sharing',
            'list'         => $list,
            'categoryList' => $categoryList,
            'categoryId'   => $categoryId,
        ]);
    }

    public function goods()
    {
        BaseModel::setApiWxappId(10001);
        $goodsId = (int)request()->param('goods_id', 0);
        $goods = $goodsId > 0 ? SharingGoods::getDetail($goodsId) : false;
        $activeList = $goods !== false ? SharingGoods::getActiveList($goodsId, 10) : [];

        return view('sharing/goods', [
            'active'     => 'sharing',
            'goods'      => $goods,
            'activeList' => $activeList,
        ]);
    }
}