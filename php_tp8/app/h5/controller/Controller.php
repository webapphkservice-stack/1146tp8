<?php
namespace app\h5\controller;

use app\common\controller\Controller as BaseController;
use think\facade\Cache;
use think\facade\View;

/**
 * h5 应用控制器基类（手机网站）。
 * 在共享基类之上补充：ThinkTemplate 渲染（$this->fetch）、wxapp_id 注入、登录态读取。
 */
class Controller extends BaseController
{
    protected static $wxapp_id = 10001;

    public function __construct()
    {
        parent::__construct();
        // h5 端固定 wxapp_id（后续可按域名或 query 参数切换）
        \app\common\model\BaseModel::setApiWxappId(static::$wxapp_id);
    }

    /**
     * 读取 H5 登录 token → 用户信息（未登录返回 null）
     *
     * 前端约定：localStorage['h5_token'] 存 token，
     * 请求时通过 Header `H5-Token` / query `token` / Cookie 发送。
     * 登录成功时由 Passport::doLogin 写入 Cache `h5_user_token_{token}`。
     *
     * @return array|null [user_id, user_name, mobile]
     */
    protected function h5User(): ?array
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
        return is_array($info) ? $info : null;
    }

    /**
     * 要求登录：未登录则跳转登录页（携带 redirect 便于登录后回跳）
     *
     * @return \think\response\Redirect|null 已登录返回 null，否则返回跳转响应
     */
    protected function requireLogin()
    {
        if ($this->h5User()) {
            return null;
        }
        return redirect((string)url('passport/login', [
            'redirect' => $this->request->url(),
        ]));
    }

    /**
     * 渲染模板（think-multi-app 下 fetch() 自动定位到 app/h5/view）
     */
    protected function fetch($template = '', $vars = [])
    {
        return view($template, $vars);
    }

    /**
     * 关闭 / 开启全局布局（登录页等独立完整 HTML 页面调用 layout(false)）
     *
     * TP8 推荐用 View facade 静态方法，禁止直接写 $this->view->xxx（PHP 8.5 dynamic property fatal）。
     *
     * @param string|false $name  false=关闭全局 layout，字符串=指定 layout 文件名（无需 .php 后缀）
     */
    protected function layout($name = false)
    {
        View::layout($name);
    }

    /**
     * 当前商城的商城名称（用于页面标题）
     */
    protected function storeName()
    {
        return \think\facade\Db::name('wxapp')->where('wxapp_id', self::$wxapp_id)->value('app_name') ?: '百信优选';
    }
}