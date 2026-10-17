<?php
namespace app\api\model;

use app\common\model\User as UserModel;
use think\facade\Db;
use think\Model;

/**
 * 优惠券模型（领券中心）——php_tp8 轻量版
 * 复刻 php_my api/model/Coupon 的输出结构：枚举字段以 {value,text} 返回，保证 RN 兼容。
 */
class Coupon extends Model
{
    protected $name = 'coupon';

    private static $colorEnum       = [10 => 'blue', 20 => 'red', 30 => 'orange', 40 => 'green'];
    private static $couponTypeEnum  = [10 => '代金券', 20 => '折扣券', 30 => '其他'];
    private static $expireTypeEnum  = [10 => '领取后生效', 20 => '固定时间'];
    private static $applyRangeEnum  = [10 => '全场通用', 20 => '指定商品', 30 => '指定分类'];

    private static function enum($map, $v)
    {
        return ['value' => (int)$v, 'text' => isset($map[$v]) ? $map[$v] : ''];
    }

    private static function fmtTime($ts)
    {
        if (empty($ts)) {
            return ['value' => 0, 'text' => ''];
        }
        return ['value' => (int)$ts, 'text' => date('Y-m-d H:i:s', $ts)];
    }

    /**
     * 领券中心列表（复刻 php_my Coupon::getList）
     */
    public static function getList($user)
    {
        $wxappId = UserModel::getWxappId();
        $rows = Db::name('coupon')
            ->where('wxapp_id', $wxappId)
            ->where('is_delete', 0)
            ->order('sort', 'asc')
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        $userId = $user ? $user['user_id'] : 0;
        $receivedIds = $userId ? self::getReceivedIds($userId) : [];
        foreach ($rows as &$item) {
            $item = self::format($item, in_array($item['coupon_id'], $receivedIds, true));
        }
        return $rows;
    }

    private static function getReceivedIds($userId)
    {
        // 注意：yoshop_user_coupon 无 is_delete 列（该表用 is_expire / is_use 表达状态）。
        // 若沿用 coupon 表的 is_delete 过滤会触发 SQL "Unknown column" → 500。
        return Db::name('user_coupon')
            ->where('user_id', $userId)
            ->column('coupon_id');
    }

    /**
     * 格式化单条（对齐 php_my getter 输出：color/coupon_type/expire_type/apply_range 转 {value,text}，
     * 折扣券 discount = 原值/10，start_time/end_time 转 {value,text}）
     */
    public static function format($item, $isReceive = 0)
    {
        $discount = ($item['coupon_type'] == 20) ? ($item['discount'] / 10) : $item['discount'];
        $state = 10;
        $stateText = '正常';
        if ($item['total_num'] > 0 && $item['receive_num'] >= $item['total_num']) {
            $state = 20;
            $stateText = '已抢光';
        } elseif ($isReceive) {
            $state = 30;
            $stateText = '已领取';
        }
        return [
            'coupon_id'        => (int)$item['coupon_id'],
            'name'             => $item['name'],
            'color'            => self::enum(self::$colorEnum, $item['color']),
            'coupon_type'      => self::enum(self::$couponTypeEnum, $item['coupon_type']),
            'reduce_price'     => (float)$item['reduce_price'],
            'discount'         => (float)$discount,
            'min_price'        => (float)$item['min_price'],
            'expire_type'      => self::enum(self::$expireTypeEnum, $item['expire_type']),
            'expire_day'       => (int)$item['expire_day'],
            'start_time'       => self::fmtTime($item['start_time']),
            'end_time'         => self::fmtTime($item['end_time']),
            'apply_range'      => self::enum(self::$applyRangeEnum, $item['apply_range']),
            'apply_range_config' => !empty($item['apply_range_config']) ? json_decode($item['apply_range_config'], true) : [],
            'total_num'        => (int)$item['total_num'],
            'state'            => ['value' => $state, 'text' => $stateText],
            'is_receive'       => $isReceive ? 1 : 0,
        ];
    }
}
