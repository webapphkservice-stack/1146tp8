<?php
namespace app\store\controller\user;

use app\store\controller\Controller;
use app\store\model\user\BalanceLog as BalanceLogModel;

/**
 * 余额明细（服务端渲染版，移植自 TP5 store/controller/user/Balance.php）
 * 菜单：用户管理 -> 余额记录 -> 余额明细（user.balance/log）
 */
class Balance extends Controller
{
    /**
     * 余额明细
     */
    public function log()
    {
        $model = new BalanceLogModel;
        return $this->fetch('log', [
            // 余额明细列表
            'list' => $model->getList($this->request->param()),
            // 属性集
            'attributes' => BalanceLogModel::getAttributes(),
        ]);
    }
}
