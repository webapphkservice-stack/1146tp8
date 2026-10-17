<?php
namespace app\store\controller;

use app\common\controller\Controller as BaseController;
use think\exception\HttpResponseException;

/**
 * store 应用 JSON API 控制器基类（原 app\store\controller\Controller）
 *
 * 背景：商家后台「服务端渲染」改造后，app\store\controller\Controller 需改用 TP5 的
 * renderSuccess($msg, $url, $data) 签名，与 JSON API 的 renderSuccess($data, $msg) 不兼容
 * （PHP 不允许子类覆盖方法时改变签名）。因此将原 API 基类独立为 Api：
 *  - 需要 JSON API 的控制器（Passport/Order/Express/user.Grade 等）继承 Api
 *  - 服务端渲染的页面控制器继承 Controller（与 TP5 同名，便于整文件迁移）
 */
class Api extends BaseController
{
    /**
     * 当前登录商家信息（由 Auth 中间件注入）
     * @return array{store_user_id:int,wxapp_id:int,user_name:string}
     */
    protected function storeUser()
    {
        return $this->request->storeUser ?: [];
    }

    /**
     * 当前商家所属小程序 id
     * @return int
     */
    protected function getWxappId()
    {
        $user = $this->storeUser();
        return (int)($user['wxapp_id'] ?? 0);
    }

    /**
     * 抛出业务错误（以 HTTP 响应形式返回，不打断后续中间件）
     * @param string $msg
     * @param int    $code
     */
    protected function throwError($msg = 'error', $code = 0)
    {
        throw new HttpResponseException($this->renderError($msg, [], $code));
    }

    /**
     * 获取 POST 数据
     * @return array
     */
    protected function postData()
    {
        return $this->request->post();
    }
}
