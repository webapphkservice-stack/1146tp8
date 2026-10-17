<?php
namespace app\api\controller;

use app\api\model\Signin as SigninModel;
use app\api\model\User as UserModel;
use app\common\model\Setting as SettingModel;
use think\facade\Db;

/**
 * 每日签到控制器
 *
 * RN 调用点：
 *   - SigninScreen : signin/index（状态，兼容未登录）
 *   - SigninScreen : signin/submit（执行签到，需登录）
 */
class Signin extends Controller
{
    /**
     * 签到状态
     * 兼容未登录：未登录时返回默认空状态，避免 RN 直接弹错。
     */
    public function index()
    {
        $setting = SettingModel::getItem('points');
        $isOpen = isset($setting['is_signin_open']) ? (int)$setting['is_signin_open'] : 0;
        $points = isset($setting['signin_points']) ? (int)$setting['signin_points'] : 10;

        $user = $this->getUser(false);
        if (!$user) {
            return $this->renderSuccess([
                'is_signin_open' => $isOpen,
                'signin_points'  => $points,
                'is_signin_today' => false,
                'continue_days'   => 0,
                'week_signin'     => SigninModel::emptyWeekSignin(),
                'total_points'    => 0,
            ]);
        }
        $data = (new SigninModel())->getSigninData($user['user_id']);
        return $this->renderSuccess([
            'is_signin_open' => $isOpen,
            'signin_points'  => $points,
            'is_signin_today' => $data['is_signin_today'],
            'continue_days'   => $data['continue_days'],
            'week_signin'     => $data['week_signin'],
            'total_points'    => $user['points'],
        ]);
    }

    /**
     * 执行签到（需登录）
     */
    public function submit()
    {
        $user = $this->getUser(true);
        $model = new SigninModel();
        if ($model->checkSigninToday($user['user_id'])) {
            return $this->renderError('您今天已经签到过了');
        }
        $setting = SettingModel::getItem('points');
        $points = isset($setting['signin_points']) ? (int)$setting['signin_points'] : 10;

        $signinId = $model->doSignin($user['user_id'], $points);
        if (!$signinId) {
            return $this->renderError('签到失败，请重试');
        }
        // 增加用户积分并写入流水（changePoints 内部已包含 points 自增，切勿再手动 inc，
        // 否则积分翻倍——此前就因重复自增导致一次签到加 2 倍积分）。
        UserModel::changePoints(
            $user['user_id'],
            $points,
            '每日签到奖励',
            $this->getWxappId()
        );
        $totalPoints = Db::name('user')->where('user_id', $user['user_id'])->value('points');
        return $this->renderSuccess([
            'points'        => $points,
            'continue_days' => $model->getContinueDays($user['user_id']),
            'total_points'  => $totalPoints,
        ], '签到成功');
    }
}
