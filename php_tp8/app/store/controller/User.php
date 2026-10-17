<?php
namespace app\store\controller;

use think\facade\Db;
use app\store\controller\Controller;
use app\store\model\User as UserModel;

/**
 * 用户管理（服务端渲染版，移植自 TP5 store/controller/User.php）
 * 菜单：用户管理 -> 用户列表（user/index）
 *
 * 注：TP5 的 recharge（后台充值）/grade（改等级）涉及余额与等级流水写入，
 *     依赖 common\service\user\... 等 TP8 尚无的服务，本次未迁移。
 */
class User extends Controller
{
    /**
     * 用户列表
     * @param string $nickName 昵称
     * @param int|null $gender 性别
     * @param int|null $grade 会员等级
     */
    public function index($nickName = '', $gender = null, $grade = null)
    {
        $model = new UserModel;
        $list = $model->getList($nickName, $gender, $grade);
        // 会员等级列表（筛选用）
        $gradeList = Db::name('user_grade')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId())
            ->field('grade_id, name')
            ->order('weight', 'asc')->select()->toArray();
        return $this->fetch('index', compact('list', 'gradeList'));
    }

    /**
     * 删除用户
     * @param int $user_id
     */
    public function delete($user_id)
    {
        $model = new UserModel;
        if (!$model->setDelete($user_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
