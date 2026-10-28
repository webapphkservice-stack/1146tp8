<?php
namespace app\api\model;

use app\api\model\dealer\Referee as RefereeModel;
use app\common\exception\BaseException;
use app\common\library\google\GoogleTokenVerifier;
use app\common\library\wechat\WxUser;
use app\common\model\Setting;
use app\common\model\User as UserModel;
use app\common\model\Wxapp;
use think\facade\Cache;
use think\facade\Db;

/**
 * 用户模型（移植自 php_my api/model/User，TP8 适配）
 */
class User extends UserModel
{
    /**
     * 解析登录态：token -> 用户模型
     * @param string $token
     * @return static|false
     */
    public static function getUser($token)
    {
        if (empty($token)) {
            return false;
        }
        // 兼容两套登录体系：
        //   - api 原生小程序/RN 登录：直接用 token 做 cache key
        //   - h5/PC 登录 (h5/Passport::doLogin)：cache key 前缀为 h5_user_token_{token}
        $session = Cache::get($token);
        if (!$session) {
            $session = Cache::get('h5_user_token_' . $token);
        }
        if (!$session || !isset($session['user_id']) || empty($session['user_id'])) {
            return false;
        }
        // 注意：detail() 会过滤 is_delete=0，用户被删除/不存在时返回 null。
        // 必须统一收敛为 false，否则调用方 static::$user 为 null，
        // 访问 static::$user['user_id'] 会直接 500（用户被删后旧 token 仍有效时会触发）。
        $user = self::detail(['user_id' => $session['user_id']]);
        return $user ?: false;
    }

    /**
     * 微信小程序登录
     */
    public function login($post)
    {
        $session = $this->wxlogin($post['code']);
        if ($session === false) {
            return false;
        }
        if (!$this->checkUser($session)) {
            $this->register($session['open_id'], [], 'wechat', 0);
        }
        $user = self::detail(['open_id' => $session['open_id']]);
        if (!$user) {
            $this->setError('登录失败');
            return false;
        }
        return $this->loginSuccess([
            'user_id'     => $user['user_id'],
            'open_id'     => $session['open_id'],
            'session_key' => $session['session_key'] ?? '',
        ]);
    }

    public function loginByAlipay($post)
    {
        $this->setError('支付宝登录暂未接入 TP8');
        return false;
    }

    public function loginByMobile($data)
    {
        $smsCode = Cache::get("sms_code_{$data['mobile']}");
        if (empty($smsCode) || (string)$data['smsCode'] !== (string)$smsCode) {
            $this->setError('手机验证码错误');
            return false;
        }
        if (!$this->checkUser(['mobile' => $data['mobile']])) {
            $this->registerMobile($data['mobile'], $data['user_info'] ?? []);
        }
        $user = self::detail(['mobile' => $data['mobile']]);
        if (!$user) {
            $this->setError('登录失败');
            return false;
        }
        return $this->loginSuccess([
            'user_id' => $user['user_id'],
            'open_id' => '',
        ]);
    }

    public function loginByWhatsapp($data)
    {
        $mobile = $data['mobile'] ?? '';
        if (empty($mobile)) {
            $this->setError('请填写手机号');
            return false;
        }
        if (!$this->verifyWhatsappCode($mobile, $data['code'] ?? '', $data['event'] ?? 'mobilelogin')) {
            $this->setError('验证码错误或已过期');
            return false;
        }
        if (!$this->checkUser(['whatsapp' => $mobile])) {
            $this->registerWhatsapp($mobile, $data['user_info'] ?? []);
        }
        $user = self::detail(['whatsapp' => $mobile]);
        if (!$user) {
            $this->setError('登录失败');
            return false;
        }
        return $this->loginSuccess([
            'user_id' => $user['user_id'],
            'open_id' => '',
        ]);
    }

    /**
     * 绑定微信（补充 open_id）
     */
    public function bindWechat($user, $code)
    {
        if (empty($code)) {
            $this->setError('请选择微信');
            return false;
        }
        $wxapp = Wxapp::getWxappCache(self::getWxappId());
        if (empty($wxapp)) {
            $this->setError('小程序不存在');
            return false;
        }
        $WxUser = new WxUser($wxapp['app_id'], $wxapp['app_secret']);
        $wxUserInfo = $WxUser->getUserInfo($code);
        if (!$wxUserInfo) {
            $this->setError($WxUser->getError() ?: '微信绑定失败');
            return false;
        }
        if ($this->isWechatUser($wxUserInfo['openid'])) {
            $this->setError('该微信已绑定其他账号');
            return false;
        }
        $user->open_id = $wxUserInfo['openid'];
        if (!$user->save()) {
            $this->setError('微信绑定失败');
            return false;
        }
        return true;
    }

    /**
     * 校验 WhatsApp 验证码
     */
    public function verifyWhatsappCode($mobile, $code, $event = 'mobilelogin')
    {
        $mobile = preg_replace('/[^\d]/', '', (string)$mobile);
        $record = Db::name('chatapp_verify_code')
            ->where(['phone' => $mobile, 'event' => $event, 'used' => 0])
            ->order('id', 'DESC')
            ->find();
        if (empty($record)) {
            $this->setError('验证码不存在或已使用');
            return false;
        }
        if ((string)$record['code'] !== (string)$code) {
            $this->setError('验证码错误');
            return false;
        }
        if (time() - $record['createtime'] > 600) {
            $this->setError('验证码已过期');
            return false;
        }
        Db::name('chatapp_verify_code')->where('id', $record['id'])->update(['used' => 1]);
        return true;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function alipayLogin($post)
    {
        $this->setError('支付宝登录暂未接入 TP8');
        return false;
    }

    /**
     * 用户注册（兼容 google / wechat 第三方 id）
     */
    public function register($googleId, $googleUserInfo, $regType = 'google', $refereeId = 0)
    {
        $data = $googleUserInfo;
        if ($regType === 'wechat') {
            $data['open_id'] = $googleId;
        } else {
            $data['google_id'] = $googleId;
        }
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();

        $user = self::create($data);
        $refereeId = $refereeId ?: ($this->getRefereeId() ?? 0);
        if ($refereeId) {
            (new RefereeModel)->createRelation($user['user_id'], $refereeId);
        }
        return $user['user_id'];
    }

    /**
     * 微信 / 支付宝：判断 code 对应账号是否已注册
     */
    public function isRegister($code)
    {
        $session = $this->wxlogin($code);
        if ($session === false) {
            return false;
        }
        return !$this->checkUser($session);
    }

    public function isRegisterByAlipay($code)
    {
        $this->setError('支付宝登录暂未接入 TP8');
        return false;
    }

    public function getMenus()
    {
        return [];
    }

    /**
     * Google 登录（海外用户）
     */
    public function loginByGoogle($data)
    {
        $googleId = $this->getGoogleIdFromToken($data['id_token'] ?? '');
        if (empty($googleId)) {
            $this->setError('登录失败：无法获取 Google 账户');
            return false;
        }
        $googleUserInfo = $this->getGoogleUserInfo($data['user_info'] ?? []);
        $user = self::detail(['google_id' => $googleId]);
        $refereeId = $data['referee_id'] ?? 0;
        if (!$user) {
            return $this->register($googleId, $googleUserInfo, 'google', $refereeId);
        }
        return $this->loginSuccess([
            'user_id'  => $user['user_id'],
            'open_id'  => '',
            'google_id' => $googleId,
        ]);
    }

    public function loginSuccess($data)
    {
        $session = [
            'wxapp_id'    => self::getWxappId(),
            'user_id'     => $data['user_id'],
            'open_id'     => $data['open_id'] ?? '',
            'google_id'   => $data['google_id'] ?? '',
            'session_key' => $data['session_key'] ?? '', // 微信虚拟支付签名必需
        ];
        // 写入缓存，默认 7 天
        $this->token = $this->token();
        Cache::set($this->token, $session, 86400 * 7);
        return $data['user_id'];
    }

    // ---------------------- 私有辅助 ----------------------

    private function wxlogin($code)
    {
        $wxapp = Wxapp::getWxappCache(self::getWxappId());
        if (empty($wxapp)) {
            $this->setError('登录失败，小程序不存在');
            return false;
        }
        $WxUser = new WxUser($wxapp['app_id'], $wxapp['app_secret']);
        $userInfo = $WxUser->getUserInfo($code);
        if (!$userInfo) {
            $this->setError($WxUser->getError() ?: '微信登录失败');
            return false;
        }
        return [
            'open_id'     => $userInfo['openid'],
            'session_key' => $userInfo['session_key'] ?? '',
            'unionid'     => $userInfo['unionid'] ?? '',
        ];
    }

    private function checkUser($data)
    {
        $cond = [];
        if (!empty($data['mobile'])) {
            $cond[] = ['mobile', '=', $data['mobile']];
        }
        if (!empty($data['open_id'])) {
            $cond[] = ['open_id', '=', $data['open_id']];
        }
        if (!empty($data['alipay_user_id'])) {
            $cond[] = ['alipay_user_id', '=', $data['alipay_user_id']];
        }
        if (!empty($data['google_id'])) {
            $cond[] = ['google_id', '=', $data['google_id']];
        }
        if (!empty($data['whatsapp'])) {
            $cond[] = ['whatsapp', '=', $data['whatsapp']];
        }
        if (empty($cond)) {
            return false;
        }
        return (bool) self::where('is_delete', 0)->whereOr($cond)->find();
    }

    private function registerMobile($mobile, $userInfo = [])
    {
        $data = array_merge($userInfo, [
            'mobile'    => $mobile,
            'wxapp_id'  => self::getWxappId(),
            'create_time' => time(),
        ]);
        $user = self::create($data);
        $refereeId = $this->getRefereeId();
        if ($refereeId) {
            (new RefereeModel)->createRelation($user['user_id'], $refereeId);
        }
        return $user['user_id'];
    }

    private function registerWhatsapp($mobile, $userInfo = [])
    {
        $data = array_merge($userInfo, [
            'whatsapp'  => $mobile,
            'wxapp_id'  => self::getWxappId(),
            'create_time' => time(),
        ]);
        $user = self::create($data);
        $refereeId = $this->getRefereeId();
        if ($refereeId) {
            (new RefereeModel)->createRelation($user['user_id'], $refereeId);
        }
        return $user['user_id'];
    }

    public function isWechatUser($openid)
    {
        return (bool) self::where('open_id', $openid)
            ->where('is_delete', 0)
            ->value('user_id');
    }

    private function token()
    {
        $this->token = md5(uniqid() . mt_rand(0, 9999) . time());
        return $this->token;
    }

    private function getGoogleIdFromToken($idToken)
    {
        $google = Setting::getItem('google');
        $clientId = !empty($google['web_client_id']) ? $google['web_client_id'] : ($google['client_id'] ?? '');
        if (empty($clientId)) {
            $this->setError('未配置 Google 登录 client_id');
            return false;
        }
        try {
            $payload = GoogleTokenVerifier::verify($idToken, $clientId);
        } catch (\Throwable $e) {
            $this->setError($e->getMessage());
            return false;
        }
        return $payload->sub ?? '';
    }

    private function getGoogleUserInfo($userInfo = [])
    {
        return [
            'nickName'  => !empty($userInfo['name']) ? $userInfo['name'] : ($userInfo['fullName'] ?? ''),
            'avatarUrl' => !empty($userInfo['picture']) ? $userInfo['picture'] : ($userInfo['photo'] ?? ''),
            'email'     => !empty($userInfo['email']) ? $userInfo['email'] : '',
        ];
    }

    private function getRefereeId()
    {
        return (int) app('request')->param('referee_id', 0);
    }

    // -------------------- 积分（结算抵扣 / 订单退还） --------------------

    /**
     * 增减用户积分并记录流水（移植自 php_my common/model/User::setIncPoints）
     * @param int    $points   正数为增加，负数为扣减
     * @param string $describe 流水描述
     * @param int    $wxappId  小程序 id，0 时取当前登录态注入的 wxapp_id
     * @return bool
     */
    public static function changePoints($userId, $points, $describe, $wxappId = 0)
    {
        $points = (int)$points;
        if ($points === 0) {
            return true;
        }
        $wxappId = $wxappId ?: UserModel::getWxappId();
        return Db::transaction(function () use ($userId, $points, $describe, $wxappId) {
            // 积分变动明细
            Db::name('user_points_log')->insert([
                'user_id'     => $userId,
                'value'       => $points,
                'describe'    => $describe,
                'remark'      => '',
                'wxapp_id'    => $wxappId,
                'create_time' => time(),
            ]);
            // 更新用户可用积分（扣减时不允许为负，用 GREATEST 兜底避免 unsigned 下溢）
            if ($points > 0) {
                Db::name('user')->where('user_id', $userId)->inc('points', $points)->update();
            } else {
                Db::name('user')->where('user_id', $userId)
                    ->update(['points' => \app\api\service\OrderStateMachine::decFloorRaw('points', abs($points))]);
            }
            return true;
        });
    }
}
