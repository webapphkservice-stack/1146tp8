<?php

namespace app\api\controller\points;

use app\api\controller\PointsLog as BasePointsLog;

/**
 * 积分明细（多级路由兼容层）
 *
 * 小程序端请求 URL 为 points.log/index（对齐老后端 points/Log.php 结构），
 * TP8 将其解析为 app\api\controller\points\Log；逻辑复用单层 PointsLog 控制器。
 * Class Log
 * @package app\api\controller\points
 */
class Log extends BasePointsLog
{
}
