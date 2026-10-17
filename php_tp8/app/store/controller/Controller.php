<?php
namespace app\store\controller;

use app\BaseController;
use app\store\model\Setting;
use app\store\model\store\User as StoreUserModel;
use app\store\service\Auth;
use app\store\service\Menus;
use think\exception\HttpResponseException;
use think\facade\Session;
use think\facade\View;

/**
 * 商家后台「服务端渲染」控制器基类（移植自 TP5 store/controller/Controller.php）
 *
 * TP8 适配要点：
 * 1. TP8 已移除 think\Controller，改为继承 app\BaseController，视图走 think\facade\View。
 * 2. TP5 的 _initialize() 改为 initialize()（由 app\BaseController 构造函数调用）。
 * 3. TP5 的 Session 改为 think\facade\Session；Request::instance() 改为 request()。
 * 4. renderSuccess/renderError 保持 TP5 的 ($msg, $url, $data) 签名，
 *    与 JSON API 基类 Api 的 ($data, $msg, $code) 不同，故两者分开继承。
 * 5. 全局 layout 由 app/store/config/view.php 的 layout_on/layout_name 控制，
 *    登录页等无需布局的页面调用 $this->layout(false)。
 */
class Controller extends BaseController
{
    /** @var array 商家登录信息 */
    protected $store;

    /** @var string 当前控制器名称（下划线风格） */
    protected $controller = '';

    /** @var string 当前方法名称 */
    protected $action = '';

    /** @var string 当前路由 uri */
    protected $routeUri = '';

    /** @var string 当前分组名称 */
    protected $group = '';

    /** @var array 登录验证白名单 */
    protected $allowAllAction = [
        'passport/login',
        'passport/register',
        'index/home',
    ];

    /** @var array 无需全局 layout 的动作 */
    protected $notLayoutAction = [
        'passport/login',
        'passport/register',
    ];

    /**
     * 后台初始化
     */
    protected function initialize() {
        // 商家登录信息
        $this->store = Session::get(StoreUserModel::SESSION_KEY);
        // 当前路由信息
        $this->getRouteinfo();
        // 验证登录状态
        $this->checkLogin();
        // 注入当前商城 id 到模型全局作用域：
        // store 应用的模板（如 layout）强依赖 Setting::getAll()，
        // 而 common\model\BaseModel 的 wxapp_id 需手动设置，否则查询条件为空导致模板取值为 null 而报错。
        \app\common\model\BaseModel::setApiWxappId($this->getWxappId());
        // 验证当前页面权限
        $this->checkPrivilege();
        // 全局 layout（注意：与下方公开的 layout() 开关方法区分，故命名为 assignLayout）
        $this->assignLayout();
    }

    /**
     * 验证当前页面权限
     * @return bool
     */
    private function checkPrivilege()
    {
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        if ($this->routeUri === 'index/index' || $this->routeUri === 'index/home') {
            return true;
        }
        if (!Auth::getInstance()->checkPrivilege($this->routeUri)) {
            $this->error('很抱歉，没有访问权限');
        }
        return true;
    }

    /**
     * 全局 layout 模板输出（initialize 阶段为所有页面注入公共模板变量）
     */
    private function assignLayout()
    {
        if (!in_array($this->routeUri, $this->notLayoutAction)) {
            View::assign([
                'base_url'  => base_url(),                 // 当前域名
                'store_url' => (string)url('/store'),      // 后台模块 url
                'group'     => $this->group,               // 当前控制器分组
                'menus'     => $this->menus(),             // 后台菜单
                'store'     => $this->store,               // 商家登录信息
                'setting'   => Setting::getAll() ?: null,  // 当前商城设置
                'request'   => $this->request,             // Request 对象
                'version'   => get_version(),              // 系统版本号
            ]);
        }
    }

    /**
     * 解析当前路由参数（分组名称、控制器名称、方法名）
     */
    protected function getRouteinfo()
    {
        // 控制器名称（转下划线，与 TP5 一致）
        $this->controller = toUnderScore($this->request->controller());
        // 方法名称
        $this->action = $this->request->action();
        // 控制器分组（用于定义所属模块）
        $groupstr = strstr($this->controller, '.', true);
        $this->group = $groupstr !== false ? $groupstr : $this->controller;
        // 当前 uri — 把 toUnderScore 插入的 `_.` 还原成 `.`，与菜单配置格式一致
        $this->routeUri = str_replace('._', '.', $this->controller) . '/' . $this->action;
    }

    /**
     * 后台菜单配置
     * @return array
     */
    protected function menus()
    {
        static $menus = [];
        if (empty($menus)) {
            $menus = Menus::getInstance()->getMenus($this->routeUri, $this->group);
            // 隐藏临时模块（航班/外卖/出租车/Stripe）
            $hide = ['flight', 'delivery', 'taxi', 'stripe'];
            $menus = array_filter($menus, function($key) use ($hide) {
                return !in_array($key, $hide);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $menus;
    }

    /**
     * 验证登录状态
     * @return bool
     */
    private function checkLogin()
    {
        if (in_array($this->routeUri, $this->allowAllAction)) {
            return true;
        }
        if (empty($this->store)
            || (int)($this->store['is_login'] ?? 0) !== 1
            || empty($this->store['wxapp'] ?? null)
        ) {
            throw new HttpResponseException(redirect((string)url('passport/login')));
        }
        return true;
    }

    /**
     * 获取当前 wxapp_id
     * @return int
     */
    protected function getWxappId()
    {
        return (int)($this->store['wxapp']['wxapp_id'] ?? 0);
    }

    /**
     * 渲染模板
     * @param string $template
     * @param array  $vars
     * @return string
     */
    protected function fetch($template = '', $vars = [])
    {
        if (!empty($vars)) {
            View::assign($vars);
        }
        return View::fetch($template);
    }

    /**
     * 模板变量赋值
     * @param string|array $name
     * @param mixed        $value
     */
    protected function assign($name, $value = '')
    {
        View::assign($name, $value);
    }

    /**
     * 关闭 / 开启布局（登录页等无需 layout 的页面调用 layout(false)）
     * @param bool|string $name
     */
    protected function layout($name)
    {
        View::layout($name);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @param int    $code
     * @param string $msg
     * @param string $url
     * @param array  $data
     * @return \think\response\Json
     */
    protected function renderJson($code = 1, $msg = '', $url = '', $data = [])
    {
        return json(compact('code', 'msg', 'url', 'data'));
    }

    /**
     * 返回操作成功 json
     * @param string $msg
     * @param string $url
     * @param array  $data
     * @return \think\response\Json
     */
    protected function renderSuccess($msg = 'success', $url = '', $data = [])
    {
        return $this->renderJson(1, $msg, $url, $data);
    }

    /**
     * 返回操作失败 json（非 Ajax 请求渲染错误提示页）
     * @param string $msg
     * @param string $url
     * @param array  $data
     * @return \think\response\Json
     */
    protected function renderError($msg = 'error', $url = '', $data = [])
    {
        if ($this->request->isAjax()) {
            return $this->renderJson(0, $msg, $url, $data);
        }
        $this->error($msg);
        return $this->renderJson(0, $msg, $url, $data);
    }

    /**
     * 操作错误跳转（渲染错误提示页，替代 TP5 的 $this->error）
     * @param string $msg
     * @param string $url 跳转地址，为空则返回上一页
     * @throws HttpResponseException
     */
    protected function error($msg = 'error', $url = '')
    {
        $url = $url ?: 'javascript:history.back(-1);';
        $html = View::fetch('layouts/error', [
            'msg'      => $msg,
            'url'      => $url,
            'base_url' => base_url(),
            'version'  => get_version(),
        ]);
        throw new HttpResponseException(response($html));
    }

    /**
     * URL 重定向
     * @param string $url
     * @throws HttpResponseException
     */
    protected function redirect($url)
    {
        throw new HttpResponseException(redirect($url));
    }

    /**
     * 获取 post 数据（数组）
     * @param string|null $key
     * @return mixed
     */
    protected function postData($key = null)
    {
        return is_null($key) ? $this->request->post() : $this->request->post($key);
    }

    /**
     * 获取 get 数据
     * @param string|null $key
     * @return mixed
     */
    protected function getData($key = null)
    {
        return is_null($key) ? $this->request->get() : $this->request->get($key);
    }
}
