<?php

namespace app\h5\controller;

use app\common\model\User as UserModel;
use think\facade\Cache;
use think\facade\Cookie;

/**
 * H5 登录/注册（液态玻璃风格）
 *
 * URL: /h5/passport/login     →  login()     渲染登录页
 *      /h5/passport/register →  register()  渲染注册页
 *      /h5/passport/doLogin   →  doLogin()   处理登录
 *      /h5/passport/doRegister→  doRegister()处理注册
 */
class Passport extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Passport 登录/注册/协议页是独立完整 HTML（带自己的 <html>…</html>），
        // 必须跳过全局 layout 包装，否则会被嵌套进 {__CONTENT__} 里并额外渲染 Dock
        $this->layout(false);
    }

    /**
     * 登录页
     */
    public function login()
    {
        $bg = $this->randomBgImage();
        return $this->fetch('passport/login', [
            'storeName' => $this->storeName(),
            'bgImage'   => $bg,
        ]);
    }

    /**
     * 注册页
     */
    public function register()
    {
        $bg = $this->randomBgImage();
        return $this->fetch('passport/register', [
            'storeName' => $this->storeName(),
            'bgImage'   => $bg,
        ]);
    }

    /**
     * 随机挑选一张 login-img 目录中的背景图
     *
     * 规则：public/assets/h5/login-img/*.jpg|*.jpeg|*.webp|*.png
     * 返回：相对 URL 路径（形如 /assets/h5/login-img/xxx.jpg）
     */
    private function randomBgImage(): string
    {
        $dir = public_path() . 'assets/h5/login-img';
        if (!is_dir($dir)) {
            return '';
        }
        $files = glob($dir . '/*.{jpg,jpeg,webp,png}', GLOB_BRACE);
        if (empty($files)) {
            return '';
        }
        $pick = $files[array_rand($files)];
        // 转成相对 URL
        $rel = str_replace(public_path(), '/', str_replace('\\', '/', $pick));
        return $rel;
    }

    /**
     * 用户协议页
     */
    public function agreement()
    {
        return $this->fetch('passport/agreement', [
            'storeName' => $this->storeName(),
        ]);
    }

    /**
     * 隐私政策页
     */
    public function privacy()
    {
        return $this->fetch('passport/privacy', [
            'storeName' => $this->storeName(),
        ]);
    }

    /**
     * 账号登录处理（手机号 + 密码模式；若 user 表无 password 列则按 WeChat OAuth 简化为手机号直登）
     *
     * 登录成功写入 Cache token，返回 /h5/user/index。
     */
    public function doLogin()
    {
        $account  = trim((string)request()->post('account', ''));
        $password = trim((string)request()->post('password', ''));

        if ($account === '') {
            return json(['code' => 0, 'msg' => '请输入手机号']);
        }

        // user 表没有 user_name 列，只按 mobile 查找
        $user = UserModel::where('mobile', $account)->where('wxapp_id', self::$wxapp_id)->find();

        if (!$user) {
            // H5 user 表无 password 字段 → 直接按手机号创建新用户（模拟 WeChat 授权）
            if (!preg_match('/^1\d{10}$/', $account)) {
                return json(['code' => 0, 'msg' => '请输入正确的 11 位手机号']);
            }
            $user = UserModel::create([
                'mobile'    => $account,
                'nickName'  => substr($account, 0, 3) . '****' . substr($account, -4),
                'avatarUrl' => '',
                'gender'    => 0,
                'wxapp_id'  => self::$wxapp_id,
                'is_delete' => 0,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        }

        if ($user['is_delete'] ?? 0) {
            return json(['code' => 0, 'msg' => '账号已被禁用']);
        }

        // 生成 token 写入 Cache（沿用 store 的模式）
        $token = 'h5_' . bin2hex(random_bytes(16));
        $info = [
            'user_id'   => $user['user_id'],
            'user_name' => $user['nickName'],
            'mobile'    => $user['mobile'],
        ];
        Cache::set('h5_user_token_' . $token, $info, 86400 * 7);
        Cookie::set('h5_token', $token, ['expire' => 86400 * 7, 'path' => '/']);

        // 优先用登录页 URL 里带的 redirect 参数（登录前想访问的页面）
        $redirect = $this->request->param('redirect', (string)url('user/index'));

        return json([
            'code' => 1,
            'msg'  => '登录成功',
            'data' => [
                'token' => $token,
                'user_name' => $user['nickName'],
                'redirect' => $redirect,
            ],
        ]);
    }

    /**
     * 用户注册
     *
     * 字段：mobile（11 位）+ nickName（可选）。
     * 注册成功后自动登录（写入 token），直接跳转到用户中心。
     */
    public function doRegister()
    {
        $mobile   = trim((string)request()->post('mobile', ''));
        $nickName = trim((string)request()->post('nickName', ''));

        if (!preg_match('/^1\d{10}$/', $mobile)) {
            return json(['code' => 0, 'msg' => '请输入正确的 11 位手机号']);
        }

        // 手机号是否已注册？
        $exists = UserModel::where('mobile', $mobile)
            ->where('wxapp_id', self::$wxapp_id)
            ->find();
        if ($exists) {
            return json(['code' => 0, 'msg' => '该手机号已注册，请直接登录']);
        }

        if ($nickName === '') {
            $nickName = '用户' . substr($mobile, -4);
        }
        $nickName = mb_substr($nickName, 0, 20);

        try {
            $user = UserModel::create([
                'mobile'      => $mobile,
                'nickName'    => $nickName,
                'avatarUrl'   => '',
                'gender'      => 0,
                'wxapp_id'    => self::$wxapp_id,
                'is_delete'   => 0,
                'create_time' => time(),
                'update_time' => time(),
            ]);
        } catch (\Throwable $e) {
            return json(['code' => 0, 'msg' => '注册失败，请稍后再试']);
        }

        // 注册成功 → 自动登录
        $token = 'h5_' . bin2hex(random_bytes(16));
        $info = [
            'user_id'   => $user['user_id'],
            'user_name' => $user['nickName'],
            'mobile'    => $user['mobile'],
        ];
        Cache::set('h5_user_token_' . $token, $info, 86400 * 7);
        Cookie::set('h5_token', $token, ['expire' => 86400 * 7, 'path' => '/']);

        $redirect = $this->request->param('redirect', (string)url('user/index'));

        return json([
            'code' => 1,
            'msg'  => '注册成功',
            'data' => [
                'token'     => $token,
                'user_name' => $user['nickName'],
                'redirect'  => $redirect,
            ],
        ]);
    }
}
