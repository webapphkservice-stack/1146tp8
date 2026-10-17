<?php

namespace app\store\service\statistics\data;

use think\facade\Db;

/**
 * 用户消费榜 Top10
 *
 * 移植自 TP5，调整：不继承 Basics；用 Db 直查并显式限定 wxapp_id。
 * 注意 user 表字段为驼峰：nickName（不是 nick_name），保持原字段名以便视图直接取值。
 */
class UserExpendRanking
{
    /** @var int */
    private $wxappId;

    public function __construct($wxappId)
    {
        $this->wxappId = (int)$wxappId;
    }

    /**
     * 用户消费榜
     * @return \think\Collection 每项含 user_id/nickName/expend_money
     */
    public function getUserExpendRanking()
    {
        return Db::name('user')
            ->field('user_id, nickName, expend_money')
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxappId)
            ->order('expend_money', 'desc')
            ->limit(10)
            ->select();
    }
}
