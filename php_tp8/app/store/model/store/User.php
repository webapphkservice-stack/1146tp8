<?php
namespace app\store\model\store;

use app\common\model\BaseModel;
use think\facade\Db;
use think\facade\Session;

/**
 * 商家用户模型（服务端渲染版，移植自 TP5 store/model/store/User.php）
 *
 * 与 app\store\model\StoreUser（token 版，供 JSON API 使用）区分：
 * 本模型采用 TP5 同款的 Session 登录态（key: yoshop_store），
 * 供服务端渲染的商家后台页面使用，两者共用同一张 store_user 表与 yoshop_hash 密码。
 *
 * Session 结构（与 TP5 完全一致，供基类 checkLogin 与 layout 模板读取）：
 * [
 *   'user'     => ['store_user_id' => int, 'user_name' => string],
 *   'wxapp'    => [...小程序信息...],
 *   'is_login' => true,
 * ]
 */
class User extends BaseModel
{
    protected $name = 'store_user';
    protected $pk = 'store_user_id';

    /** Session 键名（与 TP5 保持一致，便于两套后台共用同一登录态） */
    const SESSION_KEY = 'yoshop_store';

    /**
     * 商家用户登录（Session 版）
     * @param array $data user_name / password
     * @return bool
     */
    public function login($data)
    {
        if (empty($data) || empty($data['user_name']) || empty($data['password'])) {
            $this->error = '请输入用户名和密码';
            return false;
        }
        // 验证用户名密码是否正确（Db 直查，避开全局 scope，wxapp_id 此时尚未确定）
        $user = Db::name('store_user')
            ->where('user_name', $data['user_name'])
            ->where('password', yoshop_hash($data['password']))
            ->where('is_delete', 0)
            ->find();
        if (empty($user)) {
            $this->error = '登录失败, 用户名或密码错误';
            return false;
        }
        $wxapp = Db::name('wxapp')->where('wxapp_id', $user['wxapp_id'])->find();
        if (empty($wxapp)) {
            $this->error = '登录失败, 未找到小程序信息';
            return false;
        }
        if (!empty($wxapp['is_recycle'])) {
            $this->error = '登录失败, 当前小程序商城已删除';
            return false;
        }
        $user['wxapp'] = $wxapp;
        // 保存登录状态
        $this->loginState($user);
        return true;
    }

    /**
     * 保存登录状态到 Session（移植自 TP5 common/model/store/User::loginState）
     * @param array $user
     */
    private function loginState($user)
    {
        Session::set(self::SESSION_KEY, [
            'user' => [
                'store_user_id' => $user['store_user_id'],
                'user_name'     => $user['user_name'],
            ],
            'wxapp'    => $user['wxapp'],
            'is_login' => true,
        ]);
    }

    /**
     * 退出登录
     */
    public static function logout()
    {
        Session::delete(self::SESSION_KEY);
    }

    /**
     * 当前登录态
     * @return array
     */
    public static function getLoginState()
    {
        return Session::get(self::SESSION_KEY) ?: [];
    }

    /**
     * 商家用户详情
     * @param int $userId
     * @return array
     */
    public static function detail($userId)
    {
        if ($userId <= 0) {
            return [];
        }
        return Db::name('store_user')
            ->where('store_user_id', $userId)
            ->where('is_delete', 0)
            ->find() ?: [];
    }

    /**
     * 用户名是否已存在
     * @param string $userName
     * @return bool
     */
    public static function checkExist($userName)
    {
        return (int)Db::name('store_user')
            ->where('user_name', $userName)
            ->where('is_delete', 0)
            ->count() > 0;
    }

    /* ============ 以下为「商家用户管理」方法（移植自 TP5 store/model/store/User 的管理端逻辑） ============ */

    /**
     * 用户列表（分页）
     */
    public function getList()
    {
        return Db::name('store_user')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->order('create_time', 'desc')
            ->paginate(15);
    }

    /**
     * 新增商家用户
     * @param array $data user_name / password / real_name / is_super / role_ids
     */
    public function add($data)
    {
        if (empty($data['user_name'])) {
            $this->error = '请输入用户名';
            return false;
        }
        if (empty($data['password'])) {
            $this->error = '请输入登录密码';
            return false;
        }
        if (self::checkExist($data['user_name'])) {
            $this->error = '用户名已存在';
            return false;
        }
        Db::startTrans();
        try {
            $userId = Db::name('store_user')->insertGetId([
                'user_name'   => $data['user_name'],
                'password'    => yoshop_hash($data['password']),
                'real_name'   => $data['real_name'] ?? '',
                'is_super'    => (int)($data['is_super'] ?? 0),
                'wxapp_id'    => self::getWxappId(),
                'is_delete'   => 0,
                'create_time' => time(),
                'update_time' => time(),
            ]);
            if (!empty($data['role_ids'])) {
                (new UserRole)->add($userId, $data['role_ids']);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 更新商家用户
     */
    public function edit($data)
    {
        $userId = (int)$data['store_user_id'];
        if (empty($userId)) {
            $this->error = '参数错误';
            return false;
        }
        Db::startTrans();
        try {
            $update = [
                'real_name'   => $data['real_name'] ?? '',
                'is_super'    => (int)($data['is_super'] ?? 0),
                'update_time' => time(),
            ];
            if (!empty($data['password'])) {
                $update['password'] = yoshop_hash($data['password']);
            }
            Db::name('store_user')->where('store_user_id', $userId)->update($update);
            if (isset($data['role_ids'])) {
                (new UserRole)->edit($userId, $data['role_ids']);
            }
            Db::commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            Db::rollback();
            return false;
        }
    }

    /**
     * 软删除商家用户
     */
    public function setDelete($userId)
    {
        return Db::name('store_user')
            ->where('store_user_id', $userId)
            ->update(['is_delete' => 1, 'update_time' => time()]);
    }

    /**
     * 更新当前登录用户信息（个人资料）
     */
    public function renew($data)
    {
        $login = Session::get(self::SESSION_KEY);
        $userId = $login['user']['store_user_id'] ?? 0;
        if (empty($userId)) {
            $this->error = '未登录';
            return false;
        }
        $update = ['update_time' => time()];
        if (!empty($data['real_name'])) {
            $update['real_name'] = $data['real_name'];
        }
        if (!empty($data['password'])) {
            $update['password'] = yoshop_hash($data['password']);
        }
        return Db::name('store_user')->where('store_user_id', $userId)->update($update) !== false;
    }
}
