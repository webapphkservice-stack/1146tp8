<?php
namespace app\api\controller;

use app\api\model\User as UserModel;
use app\common\controller\Controller as BaseController;
use app\common\exception\BaseException;
use think\exception\HttpResponseException;

/**
 * API 应用控制器基类。
 * 在共享基类之上补充：登录态解析（getUser）、请求数据获取（postData）等。
 */
class Controller extends BaseController
{
    /** @var \app\api\model\User|false 当前登录用户 */
    protected static $user = false;

    protected function initialize()
    {
        parent::initialize();
        // wxapp_id 由 Token 中间件注入；此处仅做兜底
        $wxappId = $this->request->param('wxapp_id');
        if ($wxappId) {
            UserModel::setApiWxappId($wxappId);
        }
    }

    /**
     * 获取当前登录用户
     * @param bool $isForce 是否强制登录（true 时未登录抛 BaseException code=-1）
     * @return \app\api\model\User|false
     * @throws BaseException
     */
    protected function getUser($isForce = false)
    {
        if (!static::$user) {
            $token = $this->request->param('token');
            if ($token) {
                // getUser 在「token 无效」与「用户已删除」两种情况下都返回 false，
                // 这里用 ?: false 再兜一层，确保 static::$user 只会是模型或 false，
                // 避免出现 null 导致后续 static::$user['user_id'] 触发 500。
                static::$user = UserModel::getUser($token) ?: false;
            }
        }
        if (!static::$user && $isForce) {
            throw new BaseException('请您先登录', -1);
        }
        return static::$user;
    }

    /**
     * 获取 POST 原始数据
     * @return array
     */
    protected function postData()
    {
        return $this->request->post();
    }

    /**
     * 抛出业务错误（以 HTTP 响应形式返回，不打断后续中间件）
     * @param string $msg
     * @param int $code
     */
    protected function throwError($msg = 'error', $code = 0)
    {
        throw new HttpResponseException($this->renderError($msg, [], $code));
    }

    /**
     * 获取当前 wxapp_id
     * @return int
     */
    protected function getWxappId()
    {
        return UserModel::getWxappId();
    }
}
