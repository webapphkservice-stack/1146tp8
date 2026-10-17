<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\common\model\UserWaterTicket;
use app\common\model\UserWaterTicketLog;

/**
 * 我的水票(送水)：余额与变动流水
 * 移植自 TP5：application/api/controller/user/WaterTicket.php
 * 路由：GET /api/user.water_ticket/lists（需登录）
 */
class WaterTicket extends Controller
{
    /**
     * 我的水票：当前余额 + 变动流水(分页)
     * @return \think\response\Json
     * @throws \app\common\exception\BaseException
     */
    public function lists()
    {
        $user    = $this->getUser(true);
        $userId  = (int)$user['user_id'];
        $wxappId = (int)$this->getWxappId();
        $page    = (int)$this->request->param('page', 1);
        // 当前可用水票数
        $balance = UserWaterTicket::getBalance($userId, $wxappId);
        // 变动流水
        $list = UserWaterTicketLog::getList($userId, $wxappId, $page);
        return $this->renderSuccess(compact('balance', 'list'));
    }
}
