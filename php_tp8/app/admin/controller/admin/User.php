<?php
namespace app\admin\controller\admin;

use think\facade\Db;
use app\common\controller\Controller;

/**
 * 超管账号（移植自 php admin/controller/admin/User.php::renew）
 *
 * 纯 JSON：POST /admin/admin.user/renew
 * 参数：user[user_name]、user[password]、user[password_confirm]
 *
 * 说明：TP8 的 admin 登录态是 Cache token（Admin-Token 头），
 * 无法像 TP5 那样按 Session 里的 id 取当前管理员，
 * 因此由前端传入 admin_user_id（或从 Admin-Token 解析后传入）。
 */
class User extends Controller
{
    public function renew()
    {
        $data = $this->request->param('user/a', []);

        // 优先取显式传入的 id；其次尝试从请求头解析（由调用方保证）
        $adminUserId = (int)($data['admin_user_id'] ?? $this->request->param('admin_user_id', 0));
        if ($adminUserId <= 0) {
            return $this->renderError('请先登录');
        }
        $row = Db::name('admin_user')->where('admin_user_id', $adminUserId)->find();
        if (!$row) {
            return $this->renderError('管理员不存在');
        }

        $userName = trim((string)($data['user_name'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['password_confirm'] ?? '');

        if ($userName === '') {
            return $this->renderError('账号不能为空');
        }
        if ($password !== '' && $password !== $confirm) {
            return $this->renderError('确认密码不正确');
        }
        // 账号唯一性（排除自身）
        $exists = Db::name('admin_user')
            ->where('user_name', $userName)
            ->where('admin_user_id', '<>', $adminUserId)
            ->count();
        if ($exists) {
            return $this->renderError('该账号已存在');
        }

        $update = [
            'user_name'   => $userName,
            'update_time' => time(),
        ];
        if ($password !== '') {
            $update['password'] = yoshop_hash($password);
        }
        Db::name('admin_user')->where('admin_user_id', $adminUserId)->update($update);

        return $this->renderSuccess(['user_name' => $userName], '保存成功');
    }
}
