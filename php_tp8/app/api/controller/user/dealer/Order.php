<?php
namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Order as DealerOrder;
use app\api\model\dealer\Setting as SettingModel;

/**
 * 分销订单（对齐 RN dealer.ts orderList）
 */
class Order extends Controller
{
    public function lists()
    {
        $user = $this->getUser();
        $settled = $this->request->param('settled', -1);
        $list = DealerOrder::getList($user['user_id'], $settled);
        $words = SettingModel::getItem('words');
        return $this->renderSuccess(compact('list', 'words'));
    }
}
