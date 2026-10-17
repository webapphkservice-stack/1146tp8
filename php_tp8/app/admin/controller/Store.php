<?php
namespace app\admin\controller;

use think\facade\Db;
use think\facade\Session;
use app\common\controller\Controller;
use app\common\model\Setting;
use app\admin\model\AdminUser;
use think\facade\View;

/**
 * 小程序商城管理（P0：多租户核心）
 *
 * 平台超管对入驻的小程序商城进行列表、详情、回收、恢复、删除等操作。
 * 不涉及“进入商城”（旧版 web 跳转到 store 后台），该能力后续按需补充。
 *
 * 平台级操作，直接用 Db::name() 直查绕过 BaseModel 的 wxappId 全局作用域。
 */
class Store extends Controller
{
    /**
     * 商城列表（mode=active 在售 / mode=recycle 回收站）
     */
    public function index()
    {
        $token = $this->request->param('token') ?: $this->request->header('Admin-Token');
        $admin = $token ? AdminUser::getByToken($token) : null;
        if (empty($admin)) {
            return redirect('/admin/passport/login');
        }
        $mode = $this->request->param('mode', 'active');
        $isRecycle = $mode === 'recycle';

        $list = Db::name('wxapp')
            ->where('is_delete', 0)
            ->where('is_recycle', $isRecycle ? 1 : 0)
            ->order('wxapp_id', 'desc')
            ->paginate(10);

        // 批量附加商城管理员账号信息（独立 map 传给模板，Paginator items 不可直接修改）
        $storeUserMap = [];
        $wxappIds = array_column($list->items(), 'wxapp_id');
        if (!empty($wxappIds)) {
            $storeUserMap = Db::name('store_user')
                ->whereIn('wxapp_id', $wxappIds)
                ->where('is_delete', 0)
                ->order('is_super', 'desc')
                ->column('user_name', 'wxapp_id');
            // column 返回 [wxapp_id => user_name]，一个 wxapp 多个管理员时取第一条（is_super 优先）
        }
        unset($wxappIds);
        $stat = [
            'active'     => Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 0)->count(),
            'recycle'    => Db::name('wxapp')->where('is_delete', 0)->where('is_recycle', 1)->count(),
            'store_user' => Db::name('store_user')->where('is_delete', 0)->count(),
        ];
        View::layout(false);
        return View::fetch('store/index', [
            'admin'        => $admin,
            'token'        => $token,
            'list'         => $list,
            'stat'         => $stat,
            'mode'         => $mode,
            'storeUserMap' => $storeUserMap,
        ]);
    }

    /**
     * 回收站列表
     */
    public function recycle()
    {
        $list = Db::name('wxapp')
            ->where('is_delete', 0)
            ->where('is_recycle', 1)
            ->order('wxapp_id', 'desc')
            ->paginate(10);
        return $this->renderSuccess(['list' => $list], 'ok');
    }

    /**
     * 商城详情
     */
    public function detail()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        $detail  = Db::name('wxapp')->where('wxapp_id', $wxappId)->find();
        if (!$detail) {
            return $this->renderError('小程序不存在');
        }
        return $this->renderSuccess(['detail' => $detail], 'ok');
    }

    /**
     * 移入回收站
     */
    public function recycleWxapp()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        $res = Db::name('wxapp')->where('wxapp_id', $wxappId)->update(['is_recycle' => 1]);
        return $res ? $this->renderSuccess([], '操作成功') : $this->renderError('操作失败');
    }

    /**
     * 移出回收站
     */
    public function recoveryWxapp()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        $res = Db::name('wxapp')->where('wxapp_id', $wxappId)->update(['is_recycle' => 0]);
        return $res ? $this->renderSuccess([], '操作成功') : $this->renderError('操作失败');
    }

    /**
     * 删除（软删除）
     */
    public function delete()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        $res = Db::name('wxapp')->where('wxapp_id', $wxappId)->update(['is_delete' => 1]);
        return $res ? $this->renderSuccess([], '操作成功') : $this->renderError('操作失败');
    }

    /**
     * 重置商城管理员密码
     *
     * 随机生成 8 位密码（字母+数字），md5 后写入 store_user，
     * 明文返回前端仅展示一次（列表页永远只显示掩码）。
     */
    public function resetPassword()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        if ($wxappId <= 0) {
            return $this->renderError('参数错误');
        }
        $user = Db::name('store_user')
            ->where('wxapp_id', $wxappId)
            ->where('is_delete', 0)
            ->order('is_super', 'desc')
            ->find();
        if (empty($user)) {
            return $this->renderError('该商城暂无管理员账号');
        }
        // 生成 8 位随机密码
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $plain = '';
        for ($i = 0; $i < 8; $i++) {
            $plain .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $res = Db::name('store_user')
            ->where('store_user_id', $user['store_user_id'])
            ->update(['password' => md5($plain), 'update_time' => time()]);
        if (!$res) {
            return $this->renderError('密码重置失败');
        }
        return $this->renderSuccess(['password' => $plain, 'user_name' => $user['user_name']], '密码已重置');
    }

    /**
     * 新增小程序商城
     *
     * 移植自 php admin/controller/Store.php::add()，事务内完成：
     *   1) 写 wxapp 主记录
     *   2) 写 setting 默认配置（store）
     *   3) 写 store_user（该商城的超级管理员）
     *   4) 写默认帮助 / 页面 / 分类
     */
    public function add()
    {
        $data = $this->request->param('store/a', []);
        $storeName = trim((string)($data['store_name'] ?? ''));
        $userName  = trim((string)($data['user_name'] ?? ''));
        $password  = (string)($data['password'] ?? '');
        $confirm   = (string)($data['password_confirm'] ?? '');

        if ($storeName === '' || $userName === '' || $password === '') {
            return $this->renderError('商城名称、管理员账号、密码不能为空');
        }
        if ($password !== $confirm) {
            return $this->renderError('确认密码不正确');
        }
        if (Db::name('store_user')->where('user_name', $userName)->where('is_delete', 0)->count()) {
            return $this->renderError('该管理员账号已存在');
        }

        $now = time();
        Db::startTrans();
        try {
            $wxappId = Db::name('wxapp')->insertGetId([
                'app_name'    => $storeName,
                'app_id'      => trim((string)($data['app_id'] ?? '')),
                'app_secret'  => trim((string)($data['app_secret'] ?? '')),
                'is_recycle'  => 0,
                'is_delete'   => 0,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            // 默认设置（vip / store / trade / storage / sms / printer / full_free /
            // recharge / points / submsg / delivery_notify / taxi / virtual_payment）
            Setting::insertDefault($wxappId, $storeName);

            // 该商城的超级管理员
            Db::name('store_user')->insert([
                'user_name'   => $userName,
                'password'    => yoshop_hash($password),
                'real_name'   => '',
                'is_super'    => 1,
                'wxapp_id'    => $wxappId,
                'is_delete'   => 0,
                'create_time' => $now,
                'update_time' => $now,
            ]);

            // 默认帮助
            Db::name('wxapp_help')->insert([
                'title'    => '关于小程序',
                'content'  => '小程序本身无需下载，无需注册，不占用手机内存，可以跨平台使用，响应迅速，体验接近原生APP。',
                'sort'     => 100,
                'wxapp_id' => $wxappId,
            ]);
            // 默认分类
            Db::name('wxapp_category')->insert([
                'wxapp_id'       => $wxappId,
                'category_style' => '11',
                'share_title'    => '',
            ]);
            // 默认首页（简化页面数据）
            Db::name('wxapp_page')->insert([
                'page_type'  => 10,
                'page_name'  => '小程序首页',
                'page_data'  => json_encode([
                    'page' => [
                        'type'   => 'page',
                        'name'   => '页面设置',
                        'params' => ['name' => '页面标题', 'title' => $storeName, 'share_title' => $storeName],
                        'style'  => ['titleTextColor' => 'black', 'titleBackgroundColor' => '#ffffff'],
                    ],
                    'items' => [],
                ], JSON_UNESCAPED_UNICODE),
                'wxapp_id'   => $wxappId,
                'create_time'=> $now,
                'update_time'=> $now,
            ]);

            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return $this->renderError('添加失败：' . $e->getMessage());
        }
        return $this->renderSuccess(['wxapp_id' => $wxappId], '添加成功');
    }

    /**
     * 进入商城（切换到该小程序商城的后台）
     *
     * 移植自 php admin/controller/Store.php::enter()。
     * 原实现为服务端 redirect 到 store 模块；纯 JSON 版改为：
     * 写入 store 应用的登录态（Session key 与 store 应用一致），
     * 并返回前端应跳转的地址。
     */
    public function enter()
    {
        $wxappId = (int)$this->request->param('wxapp_id', 0);
        $wxapp   = Db::name('wxapp')->where('wxapp_id', $wxappId)->where('is_delete', 0)->find();
        if (!$wxapp) {
            return $this->renderError('小程序不存在');
        }
        $storeUser = Db::name('store_user')
            ->where('wxapp_id', $wxappId)
            ->where('is_super', 1)
            ->where('is_delete', 0)
            ->find();
        if (!$storeUser) {
            return $this->renderError('超级管理员用户信息不存在');
        }

        // 与 store 应用保持一致的 Session key
        Session::set('yoshop_store', [
            'user' => [
                'store_user_id' => $storeUser['store_user_id'],
                'user_name'     => $storeUser['user_name'],
            ],
            'wxapp'    => $wxapp,
            'is_login' => true,
        ]);

        return $this->renderSuccess([
            'wxapp_id' => $wxappId,
            'redirect' => 'store/index/index',
        ], 'ok');
    }
}
