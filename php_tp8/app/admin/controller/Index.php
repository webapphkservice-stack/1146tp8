<?php
namespace app\admin\controller;

use app\admin\model\AdminUser;
use think\facade\Db;
use think\facade\View;
use app\common\controller\Controller;

/**
 * admin 应用首页（总后台入口）
 *
 * 登录成功后由 passport/login 前端跳转至此（携带 ?token=）。
 * 未携带有效 token 时重定向回登录页。
 */
class Index extends Controller
{
    public function index()
    {
        // 桌面页禁用缓存：避免 CSS/JS 改动后浏览器仍显示旧样式
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        $token = $this->request->param('token') ?: $this->request->header('Admin-Token');
        $admin = $token ? AdminUser::getByToken($token) : null;
        if (empty($admin)) {
            return redirect('/admin/passport/login');
        }
        // 网站名称 —— 取 setting 表 site_info.wxapp_id=10001 的 name 字段（系统设置里存的）
        $siteName = '百信商城'; // fallback
        $siteInfo = Db::name('setting')
            ->where('key', 'site_info')
            ->where('wxapp_id', 10001)
            ->find();
        if ($siteInfo && !empty($siteInfo['values'])) {
            $values = is_array($siteInfo['values']) ? $siteInfo['values'] : json_decode($siteInfo['values'], true);
            if (!empty($values['name'])) {
                $siteName = trim($values['name']);
            }
        }

        View::layout(false);
        return View::fetch('index/index', [
            'admin'    => $admin,
            'token'    => $token,
            'siteName' => $siteName,
        ]);
    }

    /**
     * 系统设置页（桌面 Dock「系统」入口）
     */
    public function system()
    {
        $token = $this->request->param('token') ?: $this->request->header('Admin-Token');
        $admin = $token ? AdminUser::getByToken($token) : null;
        if (empty($admin)) {
            return redirect('/admin/passport/login');
        }
        $stat = [
            'php'        => PHP_VERSION,
            'store'      => Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 0)->count(),
            'store_user' => Db::name('store_user')->where('is_delete', 0)->count(),
            'time'       => date('Y-m-d H:i:s'),
            'time_ts'    => time(),
        ];
        // 读取本项目关键库版本（从 public 资源文件注释中提取）
        $getVersion = function ($relativePath, $pattern) {
            $file = public_path() . $relativePath;
            if (!is_file($file)) return 'N/A';
            $head = @file_get_contents($file, false, null, 0, 4096);
            if ($head === false) return 'N/A';
            return preg_match($pattern, $head, $m) ? $m[1] : 'N/A';
        };
        $versions = [
            'php'    => PHP_VERSION,
            'jquery' => $getVersion('/assets/common/js/jquery.min.js',     '/jQuery[ ]+v?(\d+\.\d+\.\d+)/i'),
            'jstree' => $getVersion('/assets/common/js/jstree.min.js',     '/version["\'\s:]+(\d+\.\d+\.\d+)/i'),
            'vue'    => $getVersion('/assets/common/js/vue3.min.js',       '/vue[ ]+v?(\d+\.\d+\.\d+)/i'),
        ];

        View::layout(false);
        return View::fetch('system/index', [
            'admin'    => $admin,
            'token'    => $token,
            'stat'     => $stat,
            'versions' => $versions,
        ]);
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
