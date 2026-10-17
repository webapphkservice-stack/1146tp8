<?php

namespace app\api\controller\bargain;

use app\api\controller\Controller;
use app\api\service\bargain\Active as ActiveService;

/**
 * 砍价活动：会场列表 / 详情
 */
class Active extends Controller
{
    /**
     * 砍价会场列表
     */
    public function lists()
    {
        $param = $this->request->param();
        $list = ActiveService::getHallList($this->getWxappId(), $param)['data'];
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 砍价活动详情（含当前用户参与状态）
     */
    public function detail($active_id)
    {
        $active = ActiveService::getDetail($active_id);
        if ($active === false) {
            return $this->renderError('很抱歉，该砍价商品不存在或已下架');
        }
        $goods = ActiveService::getGoodsBrief($active['goods_id']);
        $setting = ActiveService::getSetting($this->getWxappId());
        $user = $this->getUser(false);
        $userId = $user ? $user['user_id'] : 0;
        $task_id = $userId ? ActiveService::getWhetherPartake($active_id, $userId) : 0;
        $is_partake = $task_id > 0;
        return $this->renderSuccess(compact('active', 'goods', 'setting', 'is_partake', 'task_id'));
    }

    /**
     * 砍价商品海报（GD 合成：商品图+价格+小程序码）
     */
    public function poster()
    {
        $user = $this->getUser(true);
        $activeId = (int)$this->request->param('active_id', 0);
        $active = \think\facade\Db::name('bargain_active')->where('active_id', $activeId)
            ->where('wxapp_id', $this->getWxappId())->find();
        if (empty($active)) {
            return $this->renderError('砍价活动不存在');
        }
        $goods = \think\facade\Db::name('goods')->where('goods_id', $active['goods_id'])->find();
        $floor = \think\facade\Db::name('bargain_active')->where('active_id', $activeId)->value('floor_price')
            ?: \think\facade\Db::name('bargain_task')->where('active_id', $activeId)->min('floor_price');
        try {
            $poster = (new \app\common\service\qrcode\GoodsPoster($this->getWxappId()))->make([
                'goods_id' => (int)$active['goods_id'],
                'goods_name' => $goods['goods_name'] ?? '',
                'price' => $floor !== null ? $floor : ($goods['goods_min_price'] ?? '0.00'),
                'page' => 'pages/bargain/goods',
            ]);
            return $this->renderSuccess(['poster' => $poster]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
