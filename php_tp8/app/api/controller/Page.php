<?php
namespace app\api\controller;

use app\api\model\WxappPage as WxappPageModel;

/**
 * 页面装修控制器（首页 diy）
 *
 * RN 端调用点：
 *   - HomeScreen：get('page/index', { page_id: 0 })  —— 首页装修数据
 *   - LoginScreen：page/icon                          —— 登录页 logo
 *
 * 注意：这两个接口都**免登录**（首页面向所有访客），但放在 Token 中间件分组内，
 *       因为需要 wxapp_id 来定位装修数据；getUser(false) 表示登录态可选。
 */
class Page extends Controller
{
    /**
     * 页面装修数据
     * page_id 为 0 时取首页（page_type=10）
     */
    public function index()
    {
        $pageId = (int)$this->request->param('page_id', 0);
        // 免登录，但已登录时带上用户信息（后续会员等级价可能用到）
        $user = $this->getUser(false);
        $data = WxappPageModel::getPageData($user, $pageId);
        return $this->renderSuccess($data);
    }

    /**
     * 页面图标（登录页 logo 等）
     */
    public function icon()
    {
        // 只取图标配置，避免走完整的 getPageData（那会把整页装修数据都查出来）
        $iconData = WxappPageModel::getIconData();
        return $this->renderSuccess([
            'login_logo' => $iconData['logo'] ?? '',
        ]);
    }
}
