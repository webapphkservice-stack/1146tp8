<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\user\Grade as GradeModel;

/**
 * 用户端 - 会员等级（B3，API）
 *
 * 路由（api 应用，位于 Token 中间件分组内）：
 *  GET user.grade/lists  —— 会员等级列表（商品页/会员页展示各等级折扣）
 *  GET user.grade/my     —— 我的会员等级与升级进度
 */
class Grade extends Controller
{
    /**
     * 会员等级列表（可用等级，含各等级折扣率）
     */
    public function lists()
    {
        $list = GradeModel::getUsableList();
        return $this->renderSuccess(['list' => $list], 'ok');
    }

    /**
     * 我的会员等级 + 升级进度
     * 路由（api 应用）：GET user.grade/my —— URL 末段 "my" 即方法名（与全站约定一致）
     */
    public function my()
    {
        $user = $this->getUser(true);
        $gradeId = (int)($user['grade_id'] ?? 0);

        $current = $gradeId ? GradeModel::detail($gradeId) : null;
        // 有等级则取下一级，无等级则取最低等级作为升级目标
        $next = $gradeId
            ? GradeModel::getNextGrade($gradeId)
            : GradeModel::getUsableList()->first();

        $data = [
            'grade_id'   => $gradeId,
            'current'    => $current,
            'next'       => $next,
            'pay_money'  => (float)($user['pay_money'] ?? 0),
            'grade_list' => GradeModel::getUsableList(),
        ];
        return $this->renderSuccess($data, 'ok');
    }
}
