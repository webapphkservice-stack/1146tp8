<?php
namespace app\api\model\user;

use think\facade\Db;
use think\Model;

/**
 * 用户余额流水模型——php_tp8 轻量版
 */
class BalanceLog extends Model
{
    protected $name = 'user_balance_log';

    private static $sceneEnum = [10 => '充值', 20 => '消费', 30 => '管理员操作', 40 => '退款'];

    /**
     * 余额流水列表（分页，对齐 php_my BalanceLog::getList）
     */
    public static function getList($userId, $listRows = 15)
    {
        $list = Db::name('user_balance_log')
            ->where('user_id', $userId)
            ->order('log_id', 'desc')
            ->paginate($listRows)
            ->toArray();

        $items = $list['data'] ?? [];
        foreach ($items as &$item) {
            $item['scene'] = ['value' => (int)$item['scene'], 'text' => self::$sceneEnum[$item['scene']] ?? ''];
            $item['money'] = (float)$item['money'];
        }
        $list['data'] = $items;
        return $list;
    }
}
