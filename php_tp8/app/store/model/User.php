<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;
use think\Collection;

/**
 * 用户（会员）模型（服务端渲染版，移植自 TP5 store/model/User.php）
 */
class User extends BaseModel
{
    protected $name = 'user';
    protected $pk = 'user_id';
    protected $updateTime = false;

    /**
     * 用户列表（昵称/性别/等级筛选）
     * @param string $nickName
     * @param int|null $gender
     * @param int|null $grade
     * @return \think\Paginator
     */
    public function getList($nickName = '', $gender = null, $grade = null)
    {
        $query = Db::name('user')
            ->where('is_delete', 0)
            ->where('wxapp_id', self::getWxappId());
        if ($nickName !== '' && $nickName !== null) {
            $query->whereLike('nickName', '%' . trim($nickName) . '%');
        }
        if ($gender !== null && $gender !== '' && (int)$gender >= 0) {
            $query->where('gender', (int)$gender);
        }
        if ($grade !== null && $grade !== '' && (int)$grade > 0) {
            $query->where('grade_id', (int)$grade);
        }
        $list = $query->order('user_id', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $grades = Db::name('user_grade')->where('wxapp_id', self::getWxappId())
            ->column('name', 'grade_id');
        foreach ($items as &$item) {
            // 会员等级（视图取 $item['grade']['name']）
            $item['grade'] = ['name' => $grades[$item['grade_id']] ?? ''];
            // 注册方式（派生字段，user 表无此列）
            $item['register_method'] = $this->registerMethod($item);
        }
        $list->setCollection(Collection::make($items));
        return $list;
    }

    /**
     * 注册方式（对齐 TP5 common/model/User::getRegisterMethodAttr）
     */
    private function registerMethod(array $row)
    {
        if (!empty($row['open_id'])) {
            return '微信授权';
        }
        if (!empty($row['alipay_user_id'])) {
            return '支付宝';
        }
        if (!empty($row['mobile'])) {
            return preg_match('/^1\d{10}$/', $row['mobile']) ? '手机号码' : 'whatsapp';
        }
        return '未知';
    }

    /**
     * 用户详情
     */
    public static function detail($userId)
    {
        return Db::name('user')->where('user_id', $userId)->find() ?: [];
    }

    /**
     * 软删除
     */
    public function setDelete($userId)
    {
        return Db::name('user')->where('user_id', $userId)
            ->update(['is_delete' => 1]) !== false;
    }
}
