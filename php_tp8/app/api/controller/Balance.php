<?php
namespace app\api\controller;

use app\api\controller\Controller;
use app\api\model\user\BalanceLog;

/**
 * 余额流水（第 8 批）
 * 路由：GET /api/balance/log/lists（需登录）
 * 说明：为匹配 RN 端斜杠 URL（balance/log/lists）并保持 TP8 路由规则有效，
 *       采用单层控制器 Balance（TP8 会将含 / 的路由左边校验单层控制器是否存在）。
 */
class Balance extends Controller
{
    public function log()
    {
        $user = $this->getUser(true);
        $list = BalanceLog::getList($user['user_id']);
        return $this->renderSuccess(compact('list'));
    }
}
