<?php
namespace app\store\service;

use app\store\model\store\Access;
use app\store\model\store\User;
use app\store\model\store\UserRole;
use app\store\model\store\RoleAccess;
use think\facade\Session;

/**
 * 商家后台权限业务（移植自 TP5 store/service/Auth.php）
 *
 * 与 TP5 的差异：think\Session -> think\facade\Session；模型查询改为静态方法。
 */
class Auth
{
    /** @var self 单例 */
    public static $instance;

    /** @var array 商家登录信息 */
    private $store;

    /** @var array 商家用户信息 */
    private $user;

    /** @var array 权限验证白名单 */
    protected $allowAllAction = [
        'index/test',
        'passport/login',
        'passport/logout',
        'store.user/renew',
        'upload.library/*',
        'upload/image',
        'data/*',
        'goods.spec/*',
        'order.operate/deliverytpl',
        'setting.express/company',
        'setting.help/*',
        'shop/getpoint',
        'statistics.data/survey',
        'flight/*',
        'delivery/*',
        'stripe/*',
    ];

    /** @var array 商家用户权限 url */
    private $accessUrls = [];

    /**
     * 获取实例
     * @return Auth
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof Auth)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
        // 商家登录信息
        $this->store = Session::get(User::SESSION_KEY);
        // 当前用户信息
        if (!empty($this->store)) {
            $this->user = User::detail($this->store['user']['store_user_id'] ?? 0);
        }
    }

    private function __clone()
    {
    }

    /**
     * 验证指定 url 是否有访问权限
     * @param string|array $url
     * @param bool $strict 严格模式（必须全部通过才返回 true）
     * @return bool
     */
    public function checkPrivilege($url, $strict = true)
    {
        if (!is_array($url)) {
            return $this->checkAccess($url);
        }
        foreach ($url as $val) {
            if ($strict && !$this->checkAccess($val)) {
                return false;
            }
            if (!$strict && $this->checkAccess($val)) {
                return true;
            }
        }
        return true;
    }

    /**
     * 验证 url 的权限
     * @param string $url
     * @return bool
     */
    private function checkAccess($url)
    {
        // 超级管理员无需验证
        if (!empty($this->user) && !empty($this->user['is_super'])) {
            return true;
        }
        // 白名单
        if (in_array($url, $this->allowAllAction)) {
            return true;
        }
        // 通配符支持
        foreach ($this->allowAllAction as $action) {
            if (strpos($action, '*') !== false
                && preg_match('/^' . str_replace('/', '\/', $action) . '/', $url)
            ) {
                return true;
            }
        }
        // 当前用户的权限 url 列表
        return in_array($url, $this->getAccessUrls());
    }

    /**
     * 获取当前用户的权限 url 列表
     * @return array
     */
    private function getAccessUrls()
    {
        if (empty($this->accessUrls) && !empty($this->user)) {
            $roleIds = UserRole::getRoleIds($this->user['store_user_id']);
            $accessIds = RoleAccess::getAccessIds($roleIds);
            $this->accessUrls = Access::getAccessUrls($accessIds);
        }
        return $this->accessUrls;
    }
}
