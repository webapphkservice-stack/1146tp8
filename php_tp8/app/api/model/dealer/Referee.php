<?php
namespace app\api\model\dealer;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 分销推荐关系模型（轻量版，直查 dealer_referee + user）
 * 团队列表：以当前分销商为 dealer_id 的被推荐人
 */
class Referee
{
    /**
     * 团队列表
     * @param int $userId 分销商用户id（dealer_id）
     * @param int $level -1=全部 1/2/3
     * @return array
     */
    public static function getList($userId, $level = -1)
    {
        $wxappId = BaseModel::getWxappId();
        $q = Db::name('dealer_referee')->alias('r')
            ->join('user u', 'u.user_id = r.user_id')
            ->where('r.dealer_id', $userId)
            ->where('r.wxapp_id', $wxappId);
        if ($level != -1) {
            $q->where('r.level', $level);
        }
        $rows = $q->field('r.level, r.create_time, u.user_id, u.nickName, u.avatarUrl')
            ->order('r.create_time', 'desc')
            ->select()->toArray();
        foreach ($rows as &$r) {
            $r['user_id'] = (int)$r['user_id'];
            $r['level'] = (int)$r['level'];
            $r['create_time'] = (int)$r['create_time'];
            $r['nickName'] = $r['nickName'] ?? '';
            $r['avatarUrl'] = $r['avatarUrl'] ?? '';
        }
        return $rows;
    }
}
