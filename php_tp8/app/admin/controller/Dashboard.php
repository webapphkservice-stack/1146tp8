<?php
namespace app\admin\controller;

use think\facade\Db;
use app\common\controller\Controller;
use app\admin\model\AdminUser;
use think\facade\View;

/**
 * 总后台仪表盘（P0：平台运营概览）
 *
 * 平台级聚合统计，直接用 Db::name() 直查绕过 BaseModel 的 wxappId 全局作用域。
 */
class Dashboard extends Controller
{
    public function index()
    {
        // 小程序商城
        $wxappTotal   = Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 0)->count();
        $wxappRecycle = Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 1)->count();
        // 订单
        $orderTotal    = Db::name('order')->count();
        $orderPaid     = Db::name('order')->where('pay_status', 20)->count();
        $orderUnpaid   = Db::name('order')->where('pay_status', 10)->count();
        $orderDelivery = Db::name('order')->where('pay_status', 20)->where('delivery_status', 10)->count();
        $orderDone     = Db::name('order')->where('order_status', 30)->count();
        // 用户
        $userTotal = Db::name('user')->where('is_delete', 0)->count();
        // 商品
        $goodsTotal = Db::name('goods')->where('is_delete', 0)->count();

        $data = [
            'wxapp' => [
                'total'   => $wxappTotal,
                'recycle' => $wxappRecycle,
            ],
            'order' => [
                'total'    => $orderTotal,
                'paid'     => $orderPaid,
                'unpaid'   => $orderUnpaid,
                'delivery' => $orderDelivery,
                'done'     => $orderDone,
            ],
            'user'  => [
                'total' => $userTotal,
            ],
            'goods' => [
                'total' => $goodsTotal,
            ],
        ];
        return $this->renderSuccess($data, 'ok');
    }

    /**
     * 数据看板页（桌面 Dock「看板」入口）
     */
    public function dashboard()
    {
        $token = $this->request->param('token') ?: $this->request->header('Admin-Token');
        $admin = $token ? AdminUser::getByToken($token) : null;
        if (empty($admin)) {
            return redirect('/admin/passport/login');
        }
        // 三大核心指标
        $storeCount = Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 0)->count();
        $salesTotal = (float) Db::name('order')->where('pay_status', 20)->sum('pay_price');
        $userCount  = Db::name('user')->where('is_delete', 0)->count();
        // 最近 7 天每日销售额（用于 ECharts 趋势图）
        $days = [];
        $sales7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $date  = date('Y-m-d', strtotime("-$i day"));
            $days[] = date('m-d', strtotime($date));
            $start = strtotime($date . ' 00:00:00');
            $end   = strtotime($date . ' 23:59:59');
            $amount = (float) Db::name('order')
                ->where('pay_status', 20)
                ->where('pay_time', 'between', [$start, $end])
                ->sum('pay_price');
            $sales7[] = round($amount, 2);
        }
        View::layout(false);
        return View::fetch('dashboard/view', [
            'admin' => $admin,
            'token' => $token,
            'data'  => [
                'store'  => $storeCount,
                'sales'  => round($salesTotal, 2),
                'user'   => $userCount,
                'days'   => $days,
                'sales7' => $sales7,
            ],
        ]);
    }
}
