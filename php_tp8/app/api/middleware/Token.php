<?php
namespace app\api\middleware;

use app\api\model\User as UserModel;
use app\api\model\Wxapp;
use think\facade\Cache;

/**
 * API 鉴权中间件（第 1 批基建）。
 *
 * 职责（只做公共部分，登录态校验下放到控制器 getUser($isForce)）：
 *  1. 校验并注入 wxapp_id（所有 api 请求都必须带 wxapp_id）
 *  2. 校验 wxapp 是否存在（已删除/回收的微信小程序拒绝服务）
 *
 * 登录态（token -> user）由控制器 `getUser(true)` 解析，缺 token 时返回 code=-1。
 */
class Token
{
    public function handle($request, \Closure $next)
    {
        // 支付异步回调（微信/支付宝/Stripe）由网关直连，不携带 wxapp_id / token。
        // 其安全性由网关自身的签名验签保证（见 Notify/NotifyAlipay/NotifyStripe 控制器），
        // 故在此直接放行，不强制 wxapp_id。
        $path = strtolower(ltrim($request->pathinfo(), '/'));
        if (strpos($path, 'notify/') === 0) {
            return $next($request);
        }

        $wxappId = $request->param('wxapp_id');
        if (empty($wxappId)) {
            return json(['code' => 0, 'msg' => '缺少必要的参数：wxapp_id', 'data' => []]);
        }

        try {
            $wxapp = Wxapp::detail($wxappId);
        } catch (\Throwable $e) {
            $wxapp = null;
        }
        if (empty($wxapp) || !empty($wxapp['is_recycle']) || !empty($wxapp['is_delete'])) {
            return json(['code' => 0, 'msg' => '当前小程序信息不存在或已删除', 'data' => []]);
        }

        // 注入全局作用域所需的 wxapp_id
        UserModel::setApiWxappId($wxappId);

        return $next($request);
    }
}
