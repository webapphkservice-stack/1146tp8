<?php
namespace app\admin\model;

use think\facade\Cache;
use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 总后台超管用户（P0：admin 应用）
 *
 * 复用 store 应用的 Cache-token 登录模式（与旧库账号完全兼容）：
 *  - 密码哈希沿用 yoshop_hash()（md5(md5(pwd).salt)）；
 *  - 登录态用 Cache 存储 token（key: admin_token_<token>，有效期 7 天），不新增表。
 *
 * 注意：admin_user 表无 wxapp_id 字段，故所有 DB 查询走 Db::name() 直查，
 * 避免触发 BaseModel 的 wxappId 全局作用域。
 */
class AdminUser extends BaseModel
{
    protected $name = 'admin_user';

    /** token 有效期（秒） */
    const TOKEN_TTL = 86400 * 7;

    /** @var array 当前登录超管信息 */
    private $userInfo = [];

    /**
     * 登录
     * @param array $data user_name / password
     * @return array|false 成功返回 {token, admin_user_id, user_name}
     */
    public function login(array $data)
    {
        if (empty($data['user_name']) || empty($data['password'])) {
            $this->error = '请输入用户名和密码';
            return false;
        }
        $user = Db::name('admin_user')
            ->where('user_name', $data['user_name'])
            ->where('password', yoshop_hash($data['password']))
            ->find();
        if (empty($user)) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        $token = $this->makeToken($user);
        $this->userInfo = [
            'token'         => $token,
            'admin_user_id' => $user['admin_user_id'],
            'user_name'     => $user['user_name'],
        ];
        return $this->userInfo;
    }

    /**
     * 生成并保存登录态
     */
    private function makeToken(array $user)
    {
        $token = md5(uniqid('admin', true) . $user['admin_user_id'] . mt_rand(1000, 9999));
        Cache::set(self::tokenKey($token), [
            'admin_user_id' => $user['admin_user_id'],
            'user_name'     => $user['user_name'],
        ], self::TOKEN_TTL);
        return $token;
    }

    /**
     * 解析 token
     */
    public static function getByToken($token)
    {
        if (empty($token)) {
            return null;
        }
        $info = Cache::get(self::tokenKey($token));
        return is_array($info) ? $info : null;
    }

    /**
     * 退出登录
     */
    public static function logout($token)
    {
        return Cache::delete(self::tokenKey($token));
    }

    /**
     * 续期（每次请求命中时延长有效期）
     */
    public static function renew($token)
    {
        $info = self::getByToken($token);
        if ($info) {
            Cache::set(self::tokenKey($token), $info, self::TOKEN_TTL);
        }
    }

    private static function tokenKey($token)
    {
        return 'admin_token_' . $token;
    }
}
