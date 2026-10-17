<?php

namespace app\api\controller;

use app\api\controller\Controller;

/**
 * 积分转发控制器
 *
 * RN pointsApi.log() 调用斜杠 3 段 URL（points/log/index），TP8 默认解析
 * 将其落到 Points::log()；实际逻辑在单层 PointsLog 控制器，此处仅做转发。
 */
class Points extends Controller
{
    public function log()
    {
        return (new PointsLog($this->app))->index();
    }
}
