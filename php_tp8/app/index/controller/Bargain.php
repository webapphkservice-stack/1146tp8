<?php

namespace app\index\controller;

use app\api\service\bargain\Active as BargainActive;
use app\common\model\BaseModel;

/**
 * PC 砍价页面。
 */
class Bargain
{
    public function index()
    {
        BaseModel::setApiWxappId(10001);
        $page = max(1, (int)request()->param('page', 1));
        $list = BargainActive::getHallList(10001, ['page' => $page]);

        return view('bargain/index', [
            'active' => 'bargain',
            'list'   => $list,
        ]);
    }

    public function goods()
    {
        BaseModel::setApiWxappId(10001);
        $activeId = (int)request()->param('active_id', 0);
        $active = $activeId > 0 ? BargainActive::getDetail($activeId) : false;

        return view('bargain/goods', [
            'active' => 'bargain',
            'detail' => $active,
        ]);
    }
}