<?php

namespace app\index\controller;

use app\api\model\dealer\Setting as SettingModel;
use app\api\model\dealer\User as DealerUser;
use app\api\model\dealer\Apply as DealerApply;
use app\api\model\dealer\Withdraw as DealerWithdraw;
use app\api\model\dealer\Referee as DealerReferee;
use app\api\model\dealer\Order as DealerOrder;
use app\common\model\BaseModel;
use think\facade\Cache;
use think\facade\Db;

/**
 * PC 分销中心（含 6 个子页）。
 */
class Dealer
{
    public function index()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) {
            return $this->needLogin();
        }
        $dealer = DealerUser::detail($user['user_id']);

        return view('dealer/index', [
            'active'      => 'dealer',
            'user'        => $user,
            'isDealer'    => !empty($dealer),
            'dealer'      => $dealer,
            'refereeName' => $dealer ? $this->refereeName((int)$dealer['referee_id']) : '',
            'words'       => SettingModel::getItem('words'),
            'background'  => (string)(SettingModel::getItem('background')['index'] ?? ''),
        ]);
    }

    public function withdraw()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        $dealer = DealerUser::detail($user['user_id']);
        $status = (int)request()->param('status', -1);
        return view('dealer/withdraw', [
            'active'   => 'dealer',
            'user'     => $user,
            'isDealer' => !empty($dealer),
            'dealer'   => $dealer,
            'status'   => $status,
            'list'     => $dealer ? DealerWithdraw::getList($user['user_id'], $status) : [],
            'words'    => SettingModel::getItem('words'),
        ]);
    }

    public function withdrawApply()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        $dealer = DealerUser::detail($user['user_id']);
        return view('dealer/withdrawApply', [
            'active'     => 'dealer',
            'user'       => $user,
            'isDealer'   => !empty($dealer),
            'dealer'     => $dealer,
            'setting'    => SettingModel::getItem('settlement'),
            'words'      => SettingModel::getItem('words'),
            'background' => (string)(SettingModel::getItem('background')['withdraw_apply'] ?? ''),
        ]);
    }

    public function order()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        $dealer = DealerUser::detail($user['user_id']);
        $settled = (int)request()->param('settled', -1);
        return view('dealer/order', [
            'active'   => 'dealer',
            'user'     => $user,
            'isDealer' => !empty($dealer),
            'settled'  => $settled,
            'myId'     => (int)$user['user_id'],
            'list'     => $dealer ? DealerOrder::getList($user['user_id'], $settled) : [],
            'words'    => SettingModel::getItem('words'),
        ]);
    }

    public function team()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        $dealer = DealerUser::detail($user['user_id']);
        $level = (int)request()->param('level', -1);
        return view('dealer/team', [
            'active'   => 'dealer',
            'user'     => $user,
            'isDealer' => !empty($dealer),
            'dealer'   => $dealer,
            'level'    => $level,
            'list'     => $dealer ? DealerReferee::getList($user['user_id'], $level) : [],
            'words'    => SettingModel::getItem('words'),
        ]);
    }

    public function qrcode()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        return view('dealer/qrcode', [
            'active'   => 'dealer',
            'user'     => $user,
            'shareUrl' => '/index/dealer/apply.html?referee_id=' . (int)$user['user_id'],
            'words'    => SettingModel::getItem('words'),
        ]);
    }

    public function apply()
    {
        BaseModel::setApiWxappId(10001);
        $user = $this->currentUser();
        if ($user === null) return $this->needLogin();
        $dealer = DealerUser::detail($user['user_id']);
        $refereeId = (int)request()->param('referee_id', 0);
        if ($refereeId <= 0) {
            $refereeId = (int)Db::name('dealer_referee')
                ->where('user_id', $user['user_id'])
                ->where('wxapp_id', 10001)
                ->value('dealer_id');
        }
        return view('dealer/apply', [
            'active'      => 'dealer',
            'user'        => $user,
            'isDealer'    => !empty($dealer),
            'isApplying'  => DealerApply::isApplying($user['user_id']),
            'refereeName' => $this->refereeName($refereeId),
            'refereeId'   => $refereeId,
            'words'       => SettingModel::getItem('words'),
            'license'     => (string)(SettingModel::getItem('license')['license'] ?? ''),
            'background'  => (string)(SettingModel::getItem('background')['apply'] ?? ''),
        ]);
    }

    // ==================== 内部工具 ====================

    private function currentUser(): ?array
    {
        $request = request();
        $token = $request->header('h5-token', '')
              ?: $request->header('h5_token', '')
              ?: $request->param('token', '')
              ?: $request->cookie('h5_token', '');
        if ($token === '') return null;
        $info = Cache::get('h5_user_token_' . $token);
        if (!is_array($info)) return null;
        $row = Db::name('user')->where('user_id', (int)$info['user_id'])->find();
        return [
            'user_id'   => (int)$info['user_id'],
            'nickName'  => $row['nickName'] ?? ($info['user_name'] ?? ''),
            'avatarUrl' => $this->avatarUrl($row['avatarUrl'] ?? ''),
            'mobile'    => $row['mobile'] ?? ($info['mobile'] ?? ''),
        ];
    }

    private function needLogin()
    {
        return redirect('/index/passport/login.html?redirect=' . urlencode((string)request()->url()));
    }

    private function refereeName(int $refereeId): string
    {
        if ($refereeId <= 0) return '平台';
        $nick = Db::name('user')->where('user_id', $refereeId)->value('nickName');
        return $nick ?: ('用户' . $refereeId);
    }

    private function avatarUrl(string $path): string
    {
        if ($path === '' || stripos($path, 'http') === 0) return $path;
        return '/' . ltrim($path, '/');
    }
}