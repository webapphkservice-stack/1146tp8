<?php

namespace app\api\controller\live;

use app\api\controller\Controller;
use app\api\model\wxapp\LiveRoom as LiveRoomModel;

/**
 * 微信小程序直播列表
 * Class Room
 * @package app\api\controller\live
 */
class Room extends Controller
{
    /**
     * 获取直播间列表
     */
    public function lists()
    {
        $page = (int)$this->request->param('page', 1);
        $list = LiveRoomModel::getList($page);
        return $this->renderSuccess($list);
    }
}
