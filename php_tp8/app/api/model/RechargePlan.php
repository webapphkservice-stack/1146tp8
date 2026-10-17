<?php
namespace app\api\model;

use app\common\model\User as UserModel;
use think\facade\Db;
use think\Model;

/**
 * 充值套餐模型——php_tp8 轻量版
 */
class RechargePlan extends Model
{
    protected $name = 'recharge_plan';

    public static function getList()
    {
        $wxappId = UserModel::getWxappId();
        return Db::name('recharge_plan')
            ->where('wxapp_id', $wxappId)
            ->where('is_delete', 0)
            ->order('sort', 'asc')
            ->order('plan_id', 'desc')
            ->field('plan_id,plan_name,money,gift_money,sort')
            ->select()
            ->toArray();
    }
}
