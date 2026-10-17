<?php
namespace app\api\controller\sharp;

use app\api\controller\Controller;
use app\api\service\sharp\Active as ActiveService;

/**
 * 整点秒杀-会场首页
 *
 * 路由：GET sharp/index（免登录；wxapp_id 由 Token 中间件注入）
 * 返回：['tabbar' => 场次导航, 'goodsList' => 首场商品]
 */
class Index extends Controller
{
    public function index()
    {
        $service = new ActiveService($this->getWxappId());
        $data = $service->getHallIndex();
        if (empty($data['tabbar'])) {
            return $this->renderError('很抱歉，暂无秒杀活动');
        }
        return $this->renderSuccess($data);
    }
}
