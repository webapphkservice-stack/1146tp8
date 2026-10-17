<?php
namespace app\store\middleware;

use app\store\model\StoreUser;

/**
 * 商家后台鉴权中间件（P0：补 store 应用）
 *
 * 登录态取自 Cache 中的 token（StoreUser::getByToken），支持两种传参：
 *  - 请求头 Store-Token（推荐）
 *  - query/form 参数 token（便于浏览器直接调试）
 *
 * 校验通过后把商家信息挂到请求对象上（$request->storeUser），
 * 控制器通过 $this->storeUser() 读取，并据此过滤 wxapp_id，防止跨商城越权。
 */
class Auth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('Store-Token') ?: $request->param('token');
        $info  = StoreUser::getByToken((string)$token);
        if (empty($info)) {
            // fallback: 支持 Session 登录态（store 后台页面走 Session）
            // 兼容两套登录方式：JSON API 用 Token，服务端渲染页面用 Session
            $session = \think\facade\Session::get(\app\store\model\store\User::SESSION_KEY);
            if (!empty($session) && !empty($session['is_login']) && (int)$session['is_login'] === 1) {
                // 把 Session 信息转成 Auth 中间件期望的结构
                $info = [
                    'store_user_id' => (int)($session['store_user_id'] ?? 0),
                    'user_name'     => $session['user_name'] ?? '',
                    'wxapp_id'      => (int)($session['wxapp']['wxapp_id'] ?? 0),
                ];
                $request->storeUser = $info;
                return $next($request);
            }
            return json(['code' => -1, 'msg' => '请先登录商家后台', 'data' => []]);
        }
        // 每次命中续期，避免操作过程中掉线
        StoreUser::renew((string)$token);
        $request->storeUser = $info;
        return $next($request);
    }
}
