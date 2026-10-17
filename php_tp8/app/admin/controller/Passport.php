<?php
namespace app\admin\controller;

use app\admin\model\AdminUser;
use app\common\controller\Controller;
use think\facade\Db;

/**
 * 总后台认证（P0：admin 应用）
 *
 * 登录（免鉴权）生成 token；退出（需鉴权）清除 token。
 */
class Passport extends Controller
{
    /**
     * 总后台登录（免鉴权）
     */
    public function login()
    {
        // GET 请求：渲染总后台液态玻璃登录页（服务端渲染）
        if ($this->request->isGet()) {
            \think\facade\View::layout(false);
            // 网站名称 —— 系统设置里存的 site_info.name
            $siteName = '百信商城';
            $siteInfo = Db::name('setting')->where('key', 'site_info')->where('wxapp_id', 10001)->find();
            if ($siteInfo && !empty($siteInfo['values'])) {
                $v = is_array($siteInfo['values']) ? $siteInfo['values'] : json_decode($siteInfo['values'], true);
                if (!empty($v['name'])) $siteName = trim($v['name']);
            }
            return \think\facade\View::fetch('passport/login', ['siteName' => $siteName]);
        }

        $post = $this->request->post();
        // 兼容前端以 User/user 嵌套数组提交
        if (!empty($post['User']) && is_array($post['User'])) {
            $data = $post['User'];
        } elseif (!empty($post['user']) && is_array($post['user'])) {
            $data = $post['user'];
        } else {
            $data = $post;
        }

        $model = new AdminUser;
        if ($info = $model->login($data)) {
            return $this->renderSuccess($info, '登录成功');
        }
        return $this->renderError($model->getError() ?: '登录失败');
    }

    /**
     * 退出登录（需鉴权）
     */
    public function logout()
    {
        $token = $this->request->header('Admin-Token') ?: $this->request->param('token');
        AdminUser::logout((string)$token);
        return $this->renderSuccess([], '退出成功');
    }
}
