<?php
namespace app\api\controller\balance;

use app\api\controller\Controller;
use app\api\model\user\BalanceLog;

/**
 * 余额流水（第 8 批）
 * 路由：GET /api/balance/log/lists（需登录）
 */
class Log extends Controller
{
    public function lists()
    {
        $user = $this->getUser(true);
        $list = BalanceLog::getList($user['user_id']);
        return $this->renderSuccess(compact('list'));
    }
}
