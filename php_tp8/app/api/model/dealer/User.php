<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销商用户模型（轻量版，直查 dealer_user）
 */
class User
{
    /**
     * 分销商详情（is_delete=0）
     * @param int $userId
     * @return array|null
     */
    public static function detail($userId)
    {
        $row = Db::name('dealer_user')
            ->where('user_id', $userId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('is_delete', 0)
            ->find();
        return $row ? self::format($row) : null;
    }

    /** 是否为分销商 */
    public static function isDealerUser($userId)
    {
        return (bool)self::detail($userId);
    }

    /** 推荐人用户id */
    public static function getRefereeId($userId)
    {
        return (int)Db::name('dealer_user')
            ->where('user_id', $userId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('is_delete', 0)
            ->value('referee_id');
    }

    private static function format($row)
    {
        foreach (['money', 'freeze_money', 'total_money'] as $k) {
            $row[$k] = (float)$row[$k];
        }
        foreach (['first_num', 'second_num', 'third_num', 'referee_id', 'user_id'] as $k) {
            $row[$k] = (int)$row[$k];
        }
        unset($row['is_delete'], $row['wxapp_id'], $row['create_time'], $row['update_time']);
        return $row;
    }
}
