<?php
namespace app\common\model;

/**
 * 用户水票账户 (送水)
 * 预购水票，下单桶装水时可抵扣件数(1票=1桶按桶计押金的水)
 * 移植自 TP5：application/common/model/UserWaterTicket.php
 */
class UserWaterTicket extends BaseModel
{
    protected $name = 'user_water_ticket';
    protected $pk = 'user_id';

    /**
     * 解析归属的小程序ID
     * 未显式传入(0/null)时回落到当前请求绑定的 wxapp_id，
     * 避免调用方漏传导致读写到 wxapp_id=0 的脏数据(用户侧查不到票)
     * @param int $wxapp_id
     * @return int
     */
    private static function resolveWxappId($wxapp_id = 0)
    {
        $wxapp_id = (int)$wxapp_id;
        return $wxapp_id > 0 ? $wxapp_id : (int)static::$wxapp_id;
    }

    /**
     * 获取用户当前可用水票数
     * @param int $user_id
     * @param int $wxapp_id
     * @return int
     */
    public static function getBalance($user_id, $wxapp_id = 0)
    {
        $wxapp_id = self::resolveWxappId($wxapp_id);
        $row = static::where('user_id', '=', (int)$user_id)
            ->where('wxapp_id', '=', $wxapp_id)
            ->find();
        return $row ? (int)$row['water_ticket_num'] : 0;
    }

    /**
     * 发放/调整用户水票 (正数发放，负数扣减)
     * @param int $user_id 用户ID
     * @param int $num 调整数量 (正增负减)
     * @param int $wxapp_id 小程序ID
     * @param int $scene 变动场景(见 UserWaterTicketLog 的 SCENE_* 常量)，0 表示不写流水
     * @param string $remark 流水补充说明(如订单号)
     * @return bool 失败返回 false
     */
    public static function adjust($user_id, $num, $wxapp_id = 0, $scene = 0, $remark = '')
    {
        $user_id  = (int)$user_id;
        $num      = (int)$num;
        $wxapp_id = self::resolveWxappId($wxapp_id);
        if ($num == 0) {
            return true;
        }
        static::startTrans();
        try {
            $row = static::where('user_id', '=', $user_id)
                ->where('wxapp_id', '=', $wxapp_id)
                ->lock(true)
                ->find();
            $current = $row ? (int)$row['water_ticket_num'] : 0;
            $result  = $current + $num;
            if ($result < 0) {
                throw new \Exception('水票不足');
            }
            if ($row) {
                static::where('user_id', '=', $user_id)
                    ->where('wxapp_id', '=', $wxapp_id)
                    ->update([
                        'water_ticket_num' => $result,
                        'update_time'      => time(),
                    ]);
            } else {
                static::create([
                    'user_id'          => $user_id,
                    'water_ticket_num' => $result,
                    'wxapp_id'         => $wxapp_id,
                    'update_time'      => time(),
                ]);
            }
            // 记录水票流水(与余额变更同事务，保证账实一致)
            if ($scene > 0 && !UserWaterTicketLog::add($user_id, $num, $result, $scene, $wxapp_id, $remark)) {
                throw new \Exception('水票流水写入失败');
            }
            static::commit();
            return true;
        } catch (\Exception $e) {
            static::rollback();
            return false;
        }
    }
}
