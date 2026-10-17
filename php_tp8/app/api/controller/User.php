<?php
namespace app\api\controller;

use app\api\model\User as UserModel;
use app\api\service\UserMerge;
use app\common\library\sms\Driver;
use app\common\model\Setting;
use think\facade\Cache;
use think\facade\Db;

/**
 * 用户管理（移植自 php_my api/controller/User，12 个方法全量）
 */
class User extends Controller
{
    /**
     * 分销转发：RN dealer.ts 调用斜杠多段 URL（user/dealer/center 等），
     * TP8 默认解析将其落到本方法（user/dealer/* → User::dealer），
     * 此处按 pathinfo 子路径转调 user\Dealer 控制器对应动作。
     */
    public function dealer()
    {
        $dealer = new \app\api\controller\user\Dealer($this->app);
        $pi = trim($this->request->pathinfo(), '/');
        $rest = trim(preg_replace('#^user/dealer/?#', '', $pi), '/');
        $parts = $rest === '' ? [] : explode('/', $rest);
        $action = $parts[0] ?? 'center';
        if (!in_array($action, ['center', 'apply', 'withdraw', 'team', 'order', 'qrcode'], true)) {
            $action = 'center';
        }
        return $dealer->{$action}();
    }

    /**
     * 用户自动登录（手机号+短信 / 支付宝 / 默认微信）
     */
    public function login()
    {
        $model = new UserModel;
        $post = $this->request->post();
        if (!empty($post['mobile']) && !empty($post['code'])) {
            $userId = $model->loginByMobile($post);
        } elseif (isset($post['platform']) && $post['platform'] === 'alipay' && !empty($post['code'])) {
            $userId = $model->loginByAlipay($post);
        } else {
            $userId = $model->login($post);
        }
        if (!$userId) {
            return $this->renderError($model->getError() ?: '登录失败');
        }
        return $this->renderSuccess([
            'user_id' => $userId,
            'token'   => $model->getToken(),
        ]);
    }

    /**
     * 发送短信验证码（手机号登录前置）
     */
    public function sendCode()
    {
        $mobile = $this->request->post('mobile');
        if (!preg_match('/^1\d{10}$/', $mobile)) {
            return $this->renderError('手机号格式不正确');
        }
        $lockKey = "sms_lock_{$mobile}";
        if (Cache::get($lockKey)) {
            return $this->renderError('验证码发送过于频繁，请稍后再试');
        }
        $code = (string)rand(100000, 999999);
        Cache::set("sms_code_{$mobile}", $code, 300);
        Cache::set($lockKey, 1, 60);

        $sent = false;
        try {
            $smsConfig = Setting::getItem('sms');
            $engine = $smsConfig['default'] ?? '';
            $engineConf = $smsConfig['engine'][$engine] ?? [];
            $regConf = $engineConf['register'] ?? [];
            if (!empty($regConf['is_enable'])
                && !empty($engineConf['AccessKeyId'])
                && !empty($regConf['template_code'])) {
                $driver = new Driver($smsConfig);
                $sent = $driver->sendSms('register', ['code' => $code], false, $mobile);
                if (!$sent) {
                    log_write('短信发送失败: ' . $driver->getError());
                }
            } else {
                $missing = [];
                if (empty($regConf['is_enable'])) {
                    $missing[] = '未开启短信提醒(is_enable)';
                }
                if (empty($engineConf['AccessKeyId'])) {
                    $missing[] = 'AccessKeyId';
                }
                if (empty($regConf['template_code'])) {
                    $missing[] = '模板ID(template_code)';
                }
                log_write('短信网关配置不完整，跳过真实下发: ' . implode('、', $missing));
            }
        } catch (\Exception $e) {
            log_write('短信发送异常: ' . $e->getMessage());
        }

        $data = $sent ? [] : ['code' => $code];
        return $this->renderSuccess($data, '验证码已发送');
    }

    /**
     * WhatsApp 验证码登录（国际手机号）
     */
    public function whatsappLogin()
    {
        $model = new UserModel;
        $post = $this->request->post();
        $userId = $model->loginByWhatsapp($post);
        if (!$userId) {
            return $this->renderError($model->getError() ?: '登录失败');
        }
        return $this->renderSuccess([
            'user_id' => $userId,
            'token'   => $model->getToken(),
        ]);
    }

    /**
     * 绑定微信
     */
    public function bindWechat()
    {
        $user = $this->getUser(true);
        $code = $this->request->post('code');
        if (empty($code)) {
            return $this->renderError('缺少参数：code');
        }
        $model = new UserModel;
        if (!$model->bindWechat($user, $code)) {
            return $this->renderError($model->error ?: '微信绑定失败');
        }
        return $this->renderSuccess([], '微信绑定成功');
    }

    /**
     * 发送 WhatsApp 验证码
     */
    public function whatsappSendCode()
    {
        $phone = $this->request->post('phone');
        $event = $this->request->post('event', 'mobilelogin');
        $channelType = $this->request->post('channelType', 'whatsapp');

        $phone = preg_replace('/[^\d]/', '', (string)$phone);
        if (!preg_match('/^\d{8,16}$/', $phone)) {
            return $this->renderError('手机号格式不正确（需为 8-16 位国际号码）');
        }

        $smsConfig = Setting::getItem('sms');
        $chatapp = !empty($smsConfig['chatapp']) ? $smsConfig['chatapp'] : [];

        if (empty($chatapp['accessKeyId']) || empty($chatapp['accessKeySecret'])) {
            return $this->renderError('尚未配置阿里云 ChatApp 密钥，请在后台“短信通知”中设置');
        }
        if (empty($chatapp['verifyTemplateCode'])) {
            return $this->renderError('尚未配置 ChatApp 验证码模板编码');
        }

        $interval = !empty($chatapp['verifyInterval']) ? (int)$chatapp['verifyInterval'] : 60;
        try {
            $last = Db::name('chatapp_verify_code')
                ->where(['phone' => $phone, 'event' => $event])
                ->order('id', 'DESC')
                ->find();
        } catch (\Exception $e) {
            $last = null;
        }
        if ($last && time() - $last['createtime'] < $interval) {
            return $this->renderError('验证码发送过于频繁，请 ' . ($interval - (time() - $last['createtime'])) . ' 秒后再试');
        }

        $code = (string)rand(100000, 999999);

        $service = new \app\common\service\ChatAppService($chatapp);
        $result = $service->sendVerificationCode($phone, $code, $event, $channelType);

        if (empty($result) || $result['code'] != 1) {
            $msg = isset($result['msg']) ? $result['msg'] : '未知错误';
            return $this->renderError('发送失败：' . $msg);
        }

        try {
            Db::name('chatapp_verify_code')->insert([
                'event'        => $event,
                'phone'        => $phone,
                'code'         => $code,
                'channel_type' => $channelType,
                'times'        => 0,
                'ip'           => $this->request->ip(),
                'used'         => 0,
                'msg_id'       => isset($result['data']['MessageId']) ? $result['data']['MessageId'] : '',
                'result'       => json_encode($result, JSON_UNESCAPED_UNICODE),
                'createtime'   => time(),
                'updatetime'   => time(),
            ]);
        } catch (\Exception $e) {
            \think\facade\Log::record('ChatApp 验证码写入失败：' . $e->getMessage(), 'error');
        }

        return $this->renderSuccess([], '验证码已通过 WhatsApp 发送');
    }

    /**
     * 判断当前用户是否已注册
     */
    public function isRegister()
    {
        $model = new UserModel;
        $post = $this->request->post();
        if (isset($post['platform']) && $post['platform'] === 'alipay' && !empty($post['code'])) {
            $isRegister = $model->isRegisterByAlipay($post['code']);
        } else {
            $isRegister = $model->isRegister($post['code']);
        }
        return $this->renderSuccess([
            'is_register' => $isRegister ? 1 : 0,
        ]);
    }

    /**
     * 当前用户详情
     */
    public function detail()
    {
        $user = $this->getUser(true);
        $userInfo = $user->toArray();
        $userInfo['is_bind_wechat'] = !empty($user['open_id']);
        $userInfo['open_id'] = $user['open_id'];
        $userInfo['is_bind_mobile'] = !empty($user['mobile']) ? 1 : 0;
        $isThirdParty = !empty($user['open_id']) || !empty($user['alipay_user_id']);
        $userInfo['need_bind_mobile'] = ($isThirdParty && empty($user['mobile'])) ? 1 : 0;
        return $this->renderSuccess(compact('userInfo'));
    }

    /**
     * 绑定手机号（微信/支付宝登录用户补充 mobile）
     */
    public function bindMobile()
    {
        $user = $this->getUser(true);
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        if (!preg_match('/^1\d{10}$/', $mobile)) {
            return $this->renderError('手机号格式不正确');
        }
        $cacheCode = Cache::get("sms_code_{$mobile}");
        if (empty($cacheCode) || (string)$cacheCode !== (string)$code) {
            return $this->renderError('验证码错误或已过期');
        }
        $token = $this->request->param('token');

        $rival = UserMerge::findRivalByMobile($mobile, $user['user_id']);
        if ($rival) {
            $merge = new UserMerge;
            if (!$merge->merge($user['user_id'], $rival['user_id'])) {
                return $this->renderError($merge->getError());
            }
            Cache::delete("sms_code_{$mobile}");
            if ($token) {
                $cache = Cache::get($token) ?: [];
                $cache['user_id'] = $rival['user_id'];
                $cache['mobile'] = $mobile;
                Cache::set($token, $cache, 86400 * 7);
            }
            return $this->renderSuccess([
                'mobile'    => $mobile,
                'is_merged' => 1,
                'user_id'  => $rival['user_id'],
            ], '账号关联成功，您的微信与支付宝账号数据已合并');
        }

        Db::name('user')->where('user_id', $user['user_id'])->update(['mobile' => $mobile]);
        Cache::delete("sms_code_{$mobile}");
        if ($token) {
            $cache = Cache::get($token);
            if ($cache) {
                $cache['mobile'] = $mobile;
                Cache::set($token, $cache, 86400 * 7);
            }
        }
        return $this->renderSuccess([
            'mobile'    => $mobile,
            'is_merged' => 0,
        ], '手机号绑定成功');
    }

    /**
     * 绑定 WhatsApp
     */
    public function bindWhatsapp()
    {
        $user = $this->getUser(true);
        $phone = $this->request->post('phone');
        $code = $this->request->post('code');
        $phone = preg_replace('/[^\d]/', '', (string)$phone);
        if (!preg_match('/^\d{8,16}$/', $phone)) {
            return $this->renderError('请输入正确的 WhatsApp 号码（8-16 位国际号码）');
        }
        $model = new UserModel;
        if (!$model->verifyWhatsappCode($phone, $code)) {
            return $this->renderError('验证码错误或已过期');
        }
        $exists = Db::name('user')->where('whatsapp', $phone)
            ->where('user_id', '<>', $user['user_id'])
            ->where('is_delete', 0)
            ->find();
        if ($exists) {
            return $this->renderError('该 WhatsApp 已绑定其他账号');
        }
        Db::name('user')->where('user_id', $user['user_id'])->update(['whatsapp' => $phone]);
        $token = $this->request->param('token');
        if ($token) {
            $cache = Cache::get($token);
            if ($cache) {
                $cache['whatsapp'] = $phone;
                Cache::set($token, $cache, 86400 * 7);
            }
        }
        return $this->renderSuccess(['whatsapp' => $phone], 'WhatsApp 绑定成功');
    }

    /**
     * 修改头像
     */
    public function update_avatarurl()
    {
        $userInfo = $this->getUser(true);
        $avatarUrl = $this->request->param('avatarUrl');
        if (empty($avatarUrl)) {
            return $this->renderError('头像地址不能为空');
        }
        $avatarUrl = preg_replace('/^https?:\/\/[^\/]+\//i', '', $avatarUrl);
        $result = Db::name('user')->where('user_id', $userInfo['user_id'])->update(['avatarUrl' => $avatarUrl]);
        if ($result === false) {
            return $this->renderError('修改失败');
        }
        $fullUrl = base_url() . $avatarUrl;
        return $this->renderSuccess(['avatarUrl' => $fullUrl], '修改成功');
    }

    /**
     * 修改昵称
     */
    public function update_nickname()
    {
        $userInfo = $this->getUser(true);
        $nickname = $this->request->param('nickname');
        if (empty($nickname)) {
            return $this->renderError('昵称不能为空');
        }
        $result = Db::name('user')->where('user_id', $userInfo['user_id'])->update(['nickName' => $nickname]);
        if ($result === false) {
            return $this->renderError('修改失败');
        }
        return $this->renderSuccess(['nickName' => $nickname], '修改成功');
    }

    /**
     * Google 登录（海外用户）
     */
    public function googleLogin()
    {
        $model = new UserModel;
        $post = $this->request->post();
        try {
            $userId = $model->loginByGoogle($post);
        } catch (\Throwable $e) {
            return $this->renderError($e->getMessage());
        }
        if (!$userId) {
            return $this->renderError($model->getError() ?: '登录失败');
        }
        return $this->renderSuccess([
            'user_id' => $userId,
            'token'   => $model->getToken(),
        ]);
    }
}
