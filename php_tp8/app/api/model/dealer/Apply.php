<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销申请模型（轻量版，直查 dealer_apply）
 */
class Apply
{
    /** 是否正在申请（待审核） */
    public static function isApplying($userId)
    {
        return (bool)Db::name('dealer_apply')
            ->where('user_id', $userId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('apply_status', 10)
            ->value('apply_id');
    }

    /**
     * 提交申请
     * @param array  $user  [user_id => ...]
     * @param string $name
     * @param string $mobile
     * @return bool
     */
    public static function submit($user, $name, $mobile)
    {
        $wxappId = BaseModel::getWxappId();
        $refereeId = (int)Db::name('dealer_referee')
            ->where('user_id', $user['user_id'])
            ->where('wxapp_id', $wxappId)
            ->value('dealer_id');
        $data = [
            'user_id'      => $user['user_id'],
            'real_name'    => $name,
            'mobile'       => $mobile,
            'referee_id'   => $refereeId,
            'apply_type'   => 10,
            'apply_time'   => time(),
            'apply_status' => 10,
            'wxapp_id'     => $wxappId,
            'create_time'  => time(),
            'update_time'  => time(),
        ];
        return (bool)Db::name('dealer_apply')->insertGetId($data);
    }
}
