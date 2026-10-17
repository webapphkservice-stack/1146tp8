<?php
namespace app\api\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 每日签到模型（轻量 Db 实现，移植自 php_my api/model/Signin）
 *
 * 签到记录表 yoshop_signin：user_id / signin_date / signin_time / points /
 * continue_days / wxapp_id。continue_days 记录「截至本次」的连续天数。
 */
class Signin extends BaseModel
{
    protected $name = 'signin';

    /**
     * 签到状态汇总
     */
    public function getSigninData($userId)
    {
        return [
            'is_signin_today' => $this->checkSigninToday($userId),
            'continue_days'   => $this->getContinueDays($userId),
            'week_signin'     => $this->getWeekSignin($userId),
        ];
    }

    /**
     * 今天是否已签到
     */
    public function checkSigninToday($userId)
    {
        $today = date('Y-m-d');
        return Db::name('signin')
            ->where('user_id', $userId)
            ->where('signin_date', $today)
            ->count() > 0;
    }

    /**
     * 连续签到天数（含今天；今天没签则看昨天的连续天数）
     */
    public function getContinueDays($userId)
    {
        $today = date('Y-m-d');
        $todayRow = Db::name('signin')
            ->where('user_id', $userId)
            ->where('signin_date', $today)
            ->find();
        if ($todayRow) {
            return (int)$todayRow['continue_days'];
        }
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yRow = Db::name('signin')
            ->where('user_id', $userId)
            ->where('signin_date', $yesterday)
            ->find();
        return $yRow ? (int)$yRow['continue_days'] : 0;
    }

    /**
     * 本周签到日历（周一 ~ 周日）
     */
    public function getWeekSignin($userId)
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $signed = Db::name('signin')
            ->where('user_id', $userId)
            ->where('signin_date', '>=', $weekStart)
            ->where('signin_date', '<=', $weekEnd)
            ->column('signin_date');
        return $this->buildWeek($weekStart, $signed);
    }

    /**
     * 未登录时的空白周历
     */
    public static function emptyWeekSignin()
    {
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        return (new self())->buildWeek($weekStart, []);
    }

    /**
     * 生成本周 7 天数组
     */
    private function buildWeek($weekStart, array $signed)
    {
        $weekDays = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
        $weekSignin = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($weekStart . " +$i days"));
            $weekSignin[] = [
                'day'         => $weekDays[$i],
                'date'        => $date,
                'date_short'  => date('m/d', strtotime($weekStart . " +$i days")),
                'is_signin'   => in_array($date, $signed, true),
            ];
        }
        return $weekSignin;
    }

    /**
     * 执行签到（写入一条签到记录，自动计算连续天数）
     * @return int|false 新插入的 signin_id
     */
    public function doSignin($userId, $points)
    {
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $yRow = Db::name('signin')
            ->where('user_id', $userId)
            ->where('signin_date', $yesterday)
            ->find();
        $continueDays = $yRow ? (int)$yRow['continue_days'] + 1 : 1;
        return Db::name('signin')->insertGetId([
            'user_id'       => $userId,
            'signin_date'   => $today,
            'signin_time'   => time(),
            'points'        => $points,
            'continue_days' => $continueDays,
            'wxapp_id'      => self::getWxappId(),
            'create_time'   => time(),
            'update_time'   => time(),
        ]);
    }
}
