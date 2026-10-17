<?php
namespace app\admin\controller;

use think\facade\Db;
use app\common\controller\Controller;
use app\admin\model\AdminUser;
use think\facade\View;

/**
 * 基础设置（P0：商城配置读写）
 *
 * setting 表以 (key, wxapp_id) 唯一，values 为 JSON 文本。
 * 平台级操作，直接用 Db::name() 直查绕过 BaseModel 的 wxappId 全局作用域。
 */
class Setting extends Controller
{
    /**
     * 读取设置项
     */
    public function get()
    {
        $key     = (string)$this->request->param('key', '');
        $wxappId = (int)$this->request->param('wxapp_id', 10001);
        if ($key === '') {
            return $this->renderError('请指定设置项 key');
        }
        $row    = Db::name('setting')->where('key', $key)->where('wxapp_id', $wxappId)->find();
        $values = $row ? json_decode($row['values'], true) : [];
        return $this->renderSuccess(['key' => $key, 'values' => $values], 'ok');
    }

    /**
     * 保存设置项
     */
    public function save()
    {
        $key     = (string)$this->request->param('key', '');
        $wxappId = (int)$this->request->param('wxapp_id', 10001);
        $values  = $this->request->param('values/a', []);
        if ($key === '') {
            return $this->renderError('请指定设置项 key');
        }
        $json = is_array($values) ? json_encode($values, JSON_UNESCAPED_UNICODE) : (string)$values;
        $row  = Db::name('setting')->where('key', $key)->where('wxapp_id', $wxappId)->find();
        if ($row) {
            Db::name('setting')->where('key', $key)->where('wxapp_id', $wxappId)
                ->update(['values' => $json, 'update_time' => time()]);
        } else {
            Db::name('setting')->insert([
                'key'         => $key,
                'describe'    => $key,
                'values'      => $json,
                'wxapp_id'    => $wxappId,
                'update_time' => time(),
            ]);
        }
        return $this->renderSuccess([], '保存成功');
    }

    /**
     * 系统设置页（桌面 Dock「系统」入口）
     */
    public function index()
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
        ];
        View::layout(false);
        return View::fetch('system/index', [
            'admin' => $admin,
            'token' => $token,
            'stat'  => $stat,
        ]);
    }
}
