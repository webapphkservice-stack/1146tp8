<?php
namespace app\api\model;

use app\common\library\helper;
use app\common\model\User as UserModel;
use think\facade\Db;
use think\Model;

/**
 * 用户优惠券模型（我的优惠券 / 领取）——php_tp8 轻量版
 */
class UserCoupon extends Model
{
    protected $name = 'user_coupon';

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

    public static function getCouponIds($userId)
    {
        return Db::name('user_coupon')
            ->where('user_id', $userId)
            
            ->column('coupon_id');
    }

    /**
     * 我的优惠券列表（复刻 php_my UserCoupon::getList）
     * @param string $dataType not_use|is_use|is_expire|all
     */
    public static function getList($userId, $dataType = 'all')
    {
        $filter = [];
        switch ($dataType) {
            case 'not_use':
                $filter[] = ['is_use', '=', 0];
                $filter[] = ['is_expire', '=', 0];
                break;
            case 'is_use':
                $filter[] = ['is_use', '=', 1];
                break;
            case 'is_expire':
                $filter[] = ['is_expire', '=', 1];
                break;
        }
        $rows = Db::name('user_coupon')
            ->where('user_id', $userId)
            
            ->where($filter)
            ->order('is_expire', 'asc')
            ->order('is_use', 'asc')
            ->order('user_coupon_id', 'desc')
            ->select()
            ->toArray();

        $result = [];
        foreach ($rows as $item) {
            $result[] = self::format($item);
        }
        return $result;
    }

    public static function format($item)
    {
        $discount = ($item['coupon_type'] == 20) ? ($item['discount'] / 10) : $item['discount'];
        return [
            'user_coupon_id'   => (int)$item['user_coupon_id'],
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
            'is_expire'        => (int)$item['is_expire'],
            'is_use'           => (int)$item['is_use'],
        ];
    }

    /**
     * 结算可用优惠券列表（移植自 php_my UserCoupon::getUserCouponList）
     *
     * 与「我的优惠券（getList）」的区别：
     *  - 只取未使用、未过期、且已到生效时间的券
     *  - 按订单金额过滤最低消费（min_price <= 订单金额）
     *  - 额外计算每张券在当前订单下的实际抵扣额 reduced_price
     *    （折扣券按折扣率折算，代金券直接取面额）
     *  - 按抵扣额降序，便于前端默认选中最优券
     *
     * @param int   $userId
     * @param float $orderPayPrice 订单商品总金额
     * @return array
     */
    public static function getUserCouponList($userId, $orderPayPrice)
    {
        $rows = Db::name('user_coupon')
            ->where('user_id', $userId)
            ->where('is_use', 0)
            ->where('is_expire', 0)
            ->where('min_price', '<=', $orderPayPrice)
            ->select()->toArray();

        $data = [];
        foreach ($rows as $item) {
            // 未到生效时间的券不可用
            if (!empty($item['start_time']) && $item['start_time'] > time()) {
                continue;
            }
            $coupon = self::format($item);
            // 计算该券在当前订单下的实际抵扣金额
            $reducedPrice = ($coupon['coupon_type']['value'] == 20)
                ? max(0, helper::bcsub($orderPayPrice, helper::bcmul($orderPayPrice, $coupon['discount'])))
                : $coupon['reduce_price'];
            $data[$coupon['user_coupon_id']] = [
                'user_coupon_id'     => $coupon['user_coupon_id'],
                'name'               => $coupon['name'],
                'color'              => $coupon['color'],
                'coupon_type'        => $coupon['coupon_type'],
                'reduce_price'       => $coupon['reduce_price'],
                'discount'           => $coupon['discount'],
                'min_price'          => $coupon['min_price'],
                'expire_type'        => $coupon['expire_type'],
                'start_time'         => $coupon['start_time'],
                'end_time'           => $coupon['end_time'],
                'apply_range'        => $coupon['apply_range'],
                'apply_range_config' => $coupon['apply_range_config'],
                'reduced_price'      => (float)helper::number2($reducedPrice),
            ];
        }
        return array_values(helper::arraySort($data, 'reduced_price', true));
    }

    /**
     * 判断优惠券是否适用当前订单（适用范围校验）
     * 10=全部商品 / 20=指定商品 / 30=排除商品
     *
     * @param array $couponList
     * @param array $orderGoodsIds 订单商品 id 集
     * @return array
     */
    public static function couponListApplyRange($couponList, $orderGoodsIds)
    {
        foreach ($couponList as &$item) {
            $isApply = false;
            $config = is_array($item['apply_range_config'])
                ? $item['apply_range_config']
                : (json_decode((string)$item['apply_range_config'], true) ?: []);
            switch ((int)$item['apply_range']['value']) {
                case 10: // 全部商品
                    $isApply = true;
                    break;
                case 20: // 指定商品：订单中需包含至少一个指定商品
                    $applyGoodsIds = array_intersect(
                        array_map('intval', (array)($config['applyGoodsIds'] ?? [])),
                        array_map('intval', $orderGoodsIds)
                    );
                    $isApply = !empty($applyGoodsIds);
                    break;
                case 30: // 排除商品：订单商品不能全部处于排除名单
                    $excludedGoodsIds = array_intersect(
                        array_map('intval', (array)($config['excludedGoodsIds'] ?? [])),
                        array_map('intval', $orderGoodsIds)
                    );
                    $isApply = count($excludedGoodsIds) != count($orderGoodsIds);
                    break;
            }
            $item['is_apply'] = $isApply;
            if (!$isApply) {
                $item['not_apply_info'] = '该优惠券不支持当前商品';
            }
        }
        unset($item);
        return $couponList;
    }

    /**
     * 设置优惠券使用状态（核销 / 退还）
     */
    public static function setIsUse($userCouponId, $isUse = true)
    {
        return Db::name('user_coupon')->where('user_coupon_id', $userCouponId)
            ->update(['is_use' => (int)$isUse, 'update_time' => time()]);
    }

    /**
     * 领取优惠券（复刻 php_my UserCoupon::receive）
     * @return bool|array 成功返回 true，失败返回 ['error'=>...]
     */
    public static function receive($user, $couponId)
    {
        $wxappId = UserModel::getWxappId();
        $coupon = Db::name('coupon')
            ->where('coupon_id', $couponId)
            ->where('wxapp_id', $wxappId)
            
            ->find();
        if (empty($coupon)) {
            return ['error' => '优惠券不存在'];
        }
        if ($coupon['total_num'] > 0 && $coupon['receive_num'] >= $coupon['total_num']) {
            return ['error' => '优惠券已抢光'];
        }
        $exist = Db::name('user_coupon')
            ->where('user_id', $user['user_id'])
            ->where('coupon_id', $couponId)
            
            ->count();
        if ($exist > 0) {
            return ['error' => '您已领取该优惠券'];
        }

        $data = [
            'coupon_id'         => $coupon['coupon_id'],
            'name'              => $coupon['name'],
            'color'             => $coupon['color'],
            'coupon_type'       => $coupon['coupon_type'],
            'reduce_price'      => $coupon['reduce_price'],
            'discount'          => $coupon['discount'],
            'min_price'         => $coupon['min_price'],
            'expire_type'       => $coupon['expire_type'],
            'expire_day'        => $coupon['expire_day'],
            'start_time'        => $coupon['start_time'],
            'end_time'          => $coupon['end_time'],
            'apply_range'       => $coupon['apply_range'],
            'apply_range_config' => $coupon['apply_range_config'],
            'is_expire'         => 0,
            'is_use'            => 0,
            'user_id'           => $user['user_id'],
            'wxapp_id'          => $wxappId,
            'create_time'       => time(),
            'update_time'       => time(),
        ];
        if ($coupon['expire_type'] == 10) {
            $data['start_time'] = time();
            $data['end_time']   = time() + (int)$coupon['expire_day'] * 86400;
        }

        Db::startTrans();
        try {
            Db::name('user_coupon')->insert($data);
            Db::name('coupon')->where('coupon_id', $couponId)->inc('receive_num')->update();
            Db::commit();
            return true;
        } catch (\Throwable $e) {
            Db::rollback();
            return ['error' => '领取失败：' . $e->getMessage()];
        }
    }
}
