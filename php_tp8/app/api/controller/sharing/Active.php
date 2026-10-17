<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\api\service\sharing\Goods as GoodsService;
use think\facade\Db;

/**
 * 拼团拼单详情
 */
class Active extends Controller
{
    public function detail($active_id)
    {
        $detail = Db::name('sharing_active')->where('active_id', $active_id)->find();
        if (empty($detail)) {
            return $this->renderError('很抱歉，拼单不存在');
        }
        $goods = GoodsService::getDetail($detail['goods_id']);
        $goodsList = GoodsService::getList($this->getWxappId(), [])['data'] ?? [];
        return $this->renderSuccess(compact('detail', 'goods', 'goodsList'));
    }
}
