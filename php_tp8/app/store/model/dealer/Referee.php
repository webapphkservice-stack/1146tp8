<?php

namespace app\store\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商推荐关系模型(后台，自包含)
 * Class Referee
 * @package app\store\model\dealer
 */
class Referee extends BaseModel
{
    protected $name = 'dealer_referee';

    /**
     * 推荐用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 分销商
     * @return \think\model\relation\BelongsTo
     */
    public function dealer1()
    {
        return $this->belongsTo('app\store\model\User', 'dealer_id', 'user_id');
    }

    /**
     * 下级粉丝列表
     * @param $userId
     * @param int $level
     * @return \think\Paginator
     */
    public function getList($userId, $level = -1)
    {
        $model = $this->withoutGlobalScope()
            ->alias('m')
            ->field('m.*, user.nickName, user.avatarUrl')
            ->join('user', 'user.user_id = m.user_id')
            ->where('m.wxapp_id', '=', self::getWxappId())
            ->where('m.dealer_id', '=', $userId);
        $level > -1 && $model->where('m.level', '=', $level);
        return $model->order(['m.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 获取下级团队成员ID集
     * @param $dealerId
     * @param int $level
     * @return array
     */
    public function getTeamUserIds($dealerId, $level = -1)
    {
        $model = $this->withoutGlobalScope()
            ->alias('m')
            ->join('user', 'user.user_id = m.user_id')
            ->where('m.dealer_id', '=', $dealerId)
            ->where('user.is_delete', '=', 0);
        $level > -1 && $model->where('m.level', '=', $level);
        return $model->column('m.user_id');
    }

    /**
     * 获取指定用户的推荐人列表
     * @param $userId
     * @return \think\Collection
     */
    public static function getRefereeList($userId)
    {
        return (new static)->with(['dealer1'])->where('user_id', '=', $userId)->select();
    }

    /**
     * 获取指定层级的推荐人ID
     * @param $userId
     * @param $level
     * @param bool $strict
     * @return int
     */
    public static function getRefereeUserId($userId, $level, $strict = false)
    {
        $model = (new static)->where('user_id', '=', $userId)->where('level', '=', $level);
        $row = $model->find();
        return $row ? (int)$row['dealer_id'] : 0;
    }

    /**
     * 清空下级成员推荐关系
     * @param $dealerId
     * @param int $level
     * @return int
     */
    public function onClearTeam($dealerId, $level = -1)
    {
        $level > -1 && $this->where('level', '=', $level);
        return $this->where('dealer_id', '=', $dealerId)->delete();
    }

    /**
     * 清空上级推荐关系
     * @param $userId
     * @param int $level
     * @return int
     */
    public function onClearReferee($userId, $level = -1)
    {
        $level > -1 && $this->where('level', '=', $level);
        return $this->where('user_id', '=', $userId)->delete();
    }

    /**
     * 清空2-3级推荐人的关系记录
     * @param $teamIds
     * @return int
     */
    public function onClearTop($teamIds)
    {
        return $this->where('user_id', 'in', $teamIds)
            ->where('level', 'in', [2, 3])
            ->delete();
    }

}
