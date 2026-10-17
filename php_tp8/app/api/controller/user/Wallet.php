<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\User as UserModel;
use app\common\model\Setting;

/**
 * 用户钱包（第 8 批）
 * 路由：GET /api/user.wallet/index（需登录）
 */
class Wallet extends Controller
{
    public function index()
    {
        $user = $this->getUser(true);
        $setting = Setting::getItem('recharge');
        return $this->renderSuccess([
            'balance' => (float)$user['balance'],
            'is_open' => !empty($setting['is_open']),
        ]);
    }
}
