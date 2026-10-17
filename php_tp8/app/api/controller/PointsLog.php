<?php

namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\user\PointsLog as PointsLogModel;

/**
 * 积分明细
 *
 * 路由：GET /api/points/log/index（需登录）
 * 对齐 RN pointsApi.log() 与 PointsScreen.tsx 契约。
 *
 * 返回 { list: [...] }，list 为纯数组（RN 直接 .map）。
 * 注：此处采用单层控制器（与 Balance / Coupon 一致），避免 points/Log 子目录写法
 *     在本项目路由解析下出现 404。
 */
class PointsLog extends Controller
{
    public function index()
    {
        $user = $this->getUser(true);
        $list = PointsLogModel::getList($user['user_id']);
        return $this->renderSuccess(compact('list'));
    }
}
