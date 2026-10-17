<?php
namespace app\controller\api;

use app\common\controller\Controller;
use app\model\User as UserModel;
use app\common\library\google\GoogleTokenVerifier;

class User extends Controller
{
    protected function initialize()
    {
        parent::initialize();

        // POC：注入当前 wxapp_id（TP5 中由 BaseModel::init 从 Request/Session 读取）
        $wxappId = $this->request->param('wxapp_id', 10001);
        UserModel::setApiWxappId($wxappId);

        // POC 自测验签：从本地文件读取 JWKS（避免内置服务器同端口递归请求死锁）
        // 生产环境应删除此行，使用 Google 官方 JWKS（见 GoogleTokenVerifier 默认地址）
        GoogleTokenVerifier::$jwksFile = runtime_path() . 'jwks.json';
    }

    /**
     * 对应 TP5 的 api/controller/User.php::googleLogin
     */
    public function googleLogin()
    {
        $post = $this->request->post();
        try {
            $user = (new UserModel)->loginByGoogle($post);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
        $token = $user->token($user['user_id']);
        return $this->renderSuccess([
            'user_id' => $user['user_id'],
            'token'   => $token,
        ]);
    }

    /**
     * POC 自测专用：返回本地 JWKS（仅用于演示 id_token 验签链路）
     */
    public function jwks()
    {
        $file = runtime_path() . 'jwks.json';
        return json(json_decode(file_get_contents($file), true));
    }
}
