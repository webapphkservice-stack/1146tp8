<?php
namespace app\admin\middleware;

use app\admin\model\AdminUser;

/**
 * 总后台鉴权中间件（P0：admin 应用）
 *
 * 登录态取自 Cache 中的 token（AdminUser::getByToken），支持两种传参：
 *  - 请求头 Admin-Token（推荐）
 *  - query/form 参数 token（便于浏览器/命令行调试）
 *
 * 校验通过后把超管信息挂到请求对象上（$request->adminUser），
 * 控制器通过 $this->request->adminUser 读取。
 */
class Auth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Admin-Token') ?: $request->param('token');
        $info  = AdminUser::getByToken((string)$token);
        if (empty($info)) {
            return json(['code' => -1, 'msg' => '请先登录总后台', 'data' => []]);
        }
        // 每次命中续期，避免操作过程中掉线
        AdminUser::renew((string)$token);
        $request->adminUser = $info;
        return $next($request);
    }
}
