<?php

namespace app\store\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商用户模型(后台，自包含)
 * Class User
 * @package app\store\model\dealer
 */
class User extends BaseModel
{
    protected $name = 'dealer_user';

    /**
     * 推荐人
     * @return \think\model\relation\BelongsTo
     */
    public function referee()
    {
        return $this->belongsTo('app\store\model\User', 'referee_id', 'user_id');
    }

    /**
     * 关联微信用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 新增分销商用户
     * @param $userId
     * @param $data
     * @return static
     */
    public static function add($userId, $data)
    {
        $model = new static;
        $model->save(array_merge($data, [
            'user_id' => $userId,
            'wxapp_id' => self::getWxappId(),
            'first_num' => 0,
            'second_num' => 0,
            'third_num' => 0,
            'money' => 0,
            'freeze_money' => 0,
            'total_money' => 0,
            'create_time' => time(),
        ]));
        return $model;
    }

    /**
     * 获取列表
     * @param string $search
     * @return \think\Paginator
     */
    public function getList($search = '')
    {
        $this->withoutGlobalScope()
            ->alias('dealer')
            ->field('dealer.*, user.nickName, user.avatarUrl')
            ->join('user', 'user.user_id = dealer.user_id')
            ->where('dealer.wxapp_id', '=', self::getWxappId())
            ->where('dealer.is_delete', '=', 0)
            ->order(['dealer.create_time' => 'desc']);
        !empty($search) && $this->where('user.nickName|dealer.real_name|dealer.mobile', 'like', "%$search%");
        return $this->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 详情
     * @param $dealerId
     * @return null|\think\Model
     */
    public static function detail($dealerId)
    {
        return static::with(['referee'])->find($dealerId);
    }

    /**
     * 编辑
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        return $this->save($data) !== false;
    }

    /**
     * 删除(级联清理推荐关系与团队计数)
     * @return bool
     */
    public function setDelete()
    {
        return $this->transaction(function () {
            $RefereeModel = new RefereeModel;
            $team1Ids = $RefereeModel->getTeamUserIds($this['user_id'], 1);
            if (!empty($team1Ids)) {
                // 一级团队成员归属到平台
                $this->setFromplatform($team1Ids);
                $referee1Id = RefereeModel::getRefereeUserId($this['user_id'], 1, true);
                if ($referee1Id > 0) {
                    $this->setDecTeamNum($referee1Id, 2, count($team1Ids));
                    $team2Ids = $RefereeModel->getTeamUserIds($this['user_id'], 2);
                    !empty($team2Ids) && $this->setDecTeamNum($referee1Id, 3, count($team2Ids));
                    $referee2Id = RefereeModel::getRefereeUserId($this['user_id'], 2, true);
                    $referee2Id > 0 && $this->setDecTeamNum($referee2Id, 3, count($team1Ids));
                    $RefereeModel->onClearTop(array_merge($team1Ids, $team2Ids));
                }
            }
            $RefereeModel->onClearTeam($this['user_id']);
            return $this->delete();
        });
    }

    /**
     * 一级团队成员归属到平台
     * @param $userIds
     * @return false|int
     */
    private function setFromplatform($userIds)
    {
        return $this->isUpdate(true)->save(
            ['referee_id' => 0],
            ['user_id' => ['in', $userIds], 'is_delete' => 0]
        );
    }

    /**
     * 递减分销商团队成员数量
     * @param $dealerId
     * @param $level
     * @param $number
     * @return int|true
     */
    private function setDecTeamNum($dealerId, $level, $number)
    {
        $field = [1 => 'first_num', 2 => 'second_num', 3 => 'third_num'];
        return $this->where('user_id', '=', $dealerId)
            ->where('is_delete', '=', 0)
            ->setDec($field[$level], $number);
    }

    /**
     * 提现打款成功：累积提现佣金
     * @param $user_id
     * @param $money
     * @return false|int
     */
    public static function totalMoney($user_id, $money)
    {
        $model = self::detail($user_id);
        return $model->save([
            'freeze_money' => $model['freeze_money'] - $money,
            'total_money' => $model['total_money'] + $money,
        ]);
    }

    /**
     * 提现驳回：解冻分销商资金
     * @param $user_id
     * @param $money
     * @return false|int
     */
    public static function backFreezeMoney($user_id, $money)
    {
        $model = self::detail($user_id);
        return $model->save([
            'money' => $model['money'] + $money,
            'freeze_money' => $model['freeze_money'] - $money,
        ]);
    }

}
