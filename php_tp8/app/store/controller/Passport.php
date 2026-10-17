<?php
namespace app\store\controller;

use app\store\model\store\User as StoreUserModel;

/**
 * 商户认证（服务端渲染版，移植自 TP5 store/controller/Passport.php）
 *
 * 登录页：GET /store/passport/login（无 layout）
 * 登录提交：POST /store/passport/login（Ajax，返回 json，登录态写 Session）
 * 退出：GET /store/passport/logout
 *
 * 说明：旧 token 版 JSON API（app\store\model\StoreUser）仍保留，
 * 供可能的外部客户端使用；本控制器仅处理后台页面的 Session 登录态。
 */
class Passport extends Controller
{
    /**
     * 商户后台登录
     */
    public function login()
    {
        if ($this->request->isPost()) {
            $data = $this->postData('User') ?: $this->postData('user') ?: $this->postData();
            $model = new StoreUserModel;
            try {
                if ($model->login($data)) {
                    return $this->renderSuccess('登录成功', (string)url('index/index'));
                }
                return $this->renderJson(0, $model->getError() ?: '登录失败');
            } catch (\Throwable $e) {
                \think\facade\Log::warning('Passport::login 异常：' . $e->getMessage());
                return $this->renderJson(0, '系统繁忙，请稍后再试');
            }
        }
        // 登录页不套用全局 layout
        $this->layout(false);
        // 网站名称（与 admin 应用统一，site_info JSON 里读 name 字段）
        $siteName = '百信商城';
        try {
            $siteInfo = \think\facade\Db::name('setting')
                ->where('key', 'site_info')
                ->where('wxapp_id', 10001)
                ->value('values');
            if ($siteInfo) {
                $arr = json_decode($siteInfo, true);
                if (!empty($arr['name'])) $siteName = $arr['name'];
            }
        } catch (\Exception $e) {}
        return $this->fetch('login', [
            'version'  => get_version(),
            'siteName' => $siteName,
        ]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        StoreUserModel::logout();
        $this->redirect((string)url('passport/login'));
    }
}
