<?php

namespace app\index\controller;

use app\api\model\dealer\Setting as SettingModel;
use app\api\model\dealer\User as DealerUser;
use app\common\model\BaseModel;
use think\facade\Cache;
use think\facade\Db;

/**
 * PC 分销中心。
 */
class Dealer
{
    public function index()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) {
            return redirect('/index/passport/login.html?redirect=' . urlencode((string)request()->url()));
        }

        $dealer = DealerUser::detail($user['user_id']);
        $refereeName = $dealer ? $this->refereeName((int)$dealer['referee_id']) : '';

        return view('dealer/index', [
            'active'      => 'dealer',
            'user'        => $user,
            'isDealer'    => !empty($dealer),
            'dealer'      => $dealer,
            'refereeName' => $refereeName,
            'words'       => SettingModel::getItem('words'),
            'background'  => (string)(SettingModel::getItem('background')['index'] ?? ''),
        ]);
    }

    private function currentUser(): ?array
    {
        $request = request();
        $token = $request->header('h5-token', '')
              ?: $request->header('h5_token', '')
              ?: $request->param('token', '')
              ?: $request->cookie('h5_token', '');
        if ($token === '') {
            return null;
        }
        $info = Cache::get('h5_user_token_' . $token);
        if (!is_array($info)) {
            return null;
        }
        $row = Db::name('user')->where('user_id', (int)$info['user_id'])->find();
        return [
            'user_id'   => (int)$info['user_id'],
            'nickName'  => $row['nickName'] ?? ($info['user_name'] ?? ''),
            'avatarUrl' => $this->avatarUrl($row['avatarUrl'] ?? ''),
            'mobile'    => $row['mobile'] ?? ($info['mobile'] ?? ''),
        ];
    }

    private function refereeName(int $refereeId): string
    {
        if ($refereeId <= 0) {
            return '平台';
        }
        $nick = Db::name('user')->where('user_id', $refereeId)->value('nickName');
        return $nick ?: ('用户' . $refereeId);
    }

    private function avatarUrl(string $path): string
    {
        if ($path === '' || stripos($path, 'http') === 0) {
            return $path;
        }
        return '/' . ltrim($path, '/');
    }
}