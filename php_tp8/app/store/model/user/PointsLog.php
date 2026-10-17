<?php

namespace app\store\model\user;

use app\common\model\user\PointsLog as PointsLogModel;

/**
 * 用户积分变动明细模型（门店后台）
 * Class PointsLog
 * @package app\store\model\user
 */
class PointsLog extends PointsLogModel
{
    /**
     * 获取积分明细列表
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($query = [])
    {
        // 设置查询条件
        !empty($query) && $this->setQueryWhere($query);
        // 获取列表数据
        // 注意：全局作用域会注入裸列 `wxapp_id`，而本查询 join 了 user 表（同样含 wxapp_id），
        // 会导致 SQL 列歧义，故关闭该作用域并显式限定 log.wxapp_id。
        return $this->withoutGlobalScope(['wxappId'])
            ->with(['user'])
            ->alias('log')
            ->field('log.*')
            ->join('user', 'user.user_id = log.user_id')
            ->where('log.wxapp_id', self::getWxappId())
            ->order(['log.create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     * @param $query
     */
    private function setQueryWhere($query)
    {
        // 设置默认的检索数据
        $params = array_merge([
            'user_id'    => 0,
            'search'     => '',
            'start_time' => '',
            'end_time'   => '',
        ], $query);
        // 用户ID
        $params['user_id'] > 0 && $this->where('log.user_id', '=', $params['user_id']);
        // 用户昵称
        !empty($params['search']) && $this->where('user.nickName', 'like', "%{$params['search']}%");
        // 起始时间
        !empty($params['start_time']) && $this->where('log.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('log.create_time', '<', strtotime($params['end_time']) + 86400);
    }

}
