<?php
namespace app\store\model;

use think\facade\Cache;
use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 商家后台用户（P0：补 store 应用，支撑订单发货）
 *
 * 对应 php_my store/model/store/User.php，仅保留登录与登录态所需能力：
 *  - 密码哈希沿用 yoshop_hash()（md5(md5(pwd).salt)），与旧库账号完全兼容；
 *  - 登录态用 Cache 存储 token（key: store_token_<token>，有效期 7 天），
 *    不新增表，避免与旧库 Schema 产生差异。
 */
class StoreUser extends BaseModel
{
    protected $name = 'store_user';

    /** token 有效期（秒） */
    const TOKEN_TTL = 86400 * 7;

    /** @var array 当前登录商家信息 */
    private $userInfo = [];

    /**
     * 登录
     * @param array $data user_name / password
     * @return array|false 成功返回 {token, store_user_id, user_name, real_name, wxapp_id}
     */
    public function login(array $data)
    {
        if (empty($data['user_name']) || empty($data['password'])) {
            $this->error = '请输入用户名和密码';
            return false;
        }
        $user = Db::name('store_user')
            ->where('user_name', $data['user_name'])
            ->where('password', yoshop_hash($data['password']))
            ->where('is_delete', 0)
            ->find();
        if (empty($user)) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        // 校验所属小程序状态
        $wxapp = Db::name('wxapp')->where('wxapp_id', $user['wxapp_id'])->find();
        if (empty($wxapp)) {
            $this->error = '登录失败, 未找到小程序信息';
            return false;
        }
        if (!empty($wxapp['is_recycle'])) {
            $this->error = '登录失败, 当前小程序商城已删除';
            return false;
        }

        $token = $this->makeToken($user);
        $this->userInfo = [
            'token'         => $token,
            'store_user_id' => $user['store_user_id'],
            'user_name'     => $user['user_name'],
            'real_name'     => $user['real_name'],
            'is_super'      => $user['is_super'],
            'wxapp_id'      => $user['wxapp_id'],
        ];
        return $this->userInfo;
    }

    /**
     * 生成并保存登录态
     * @param array $user
     * @return string
     */
    private function makeToken(array $user)
    {
        $token = md5(uniqid('store', true) . $user['store_user_id'] . mt_rand(1000, 9999));
        Cache::set(self::tokenKey($token), [
            'store_user_id' => $user['store_user_id'],
            'wxapp_id'      => $user['wxapp_id'],
            'user_name'     => $user['user_name'],
        ], self::TOKEN_TTL);
        return $token;
    }

    /**
     * 解析 token
     * @param string $token
     * @return array|null
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
     * @param string $token
     * @return bool
     */
    public static function logout($token)
    {
        return Cache::delete(self::tokenKey($token));
    }

    /**
     * 续期（每次请求命中时延长有效期）
     * @param string $token
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
        return 'store_token_' . $token;
    }
}
