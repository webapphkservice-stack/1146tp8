<?php
namespace app\common\model;

/**
 * 用户水票流水 (送水)
 * 记录水票的每一次变动，供C端"我的水票"展示
 * 移植自 TP5：application/common/model/UserWaterTicketLog.php
 */
class UserWaterTicketLog extends BaseModel
{
    // 变动场景：下单抵扣
    const SCENE_ORDER = 10;

    // 变动场景：后台发放
    const SCENE_ADMIN_GRANT = 20;

    // 变动场景：后台扣减
    const SCENE_ADMIN_DEDUCT = 30;

    // 变动场景：取消订单退回
    const SCENE_REFUND = 40;

    protected $name = 'user_water_ticket_log';

    /**
     * 场景名称
     * @return array
     */
    public static function sceneList()
    {
        return [
            self::SCENE_ORDER        => '下单抵扣',
            self::SCENE_ADMIN_GRANT  => '后台发放',
            self::SCENE_ADMIN_DEDUCT => '后台扣减',
            self::SCENE_REFUND       => '取消退回',
        ];
    }

    /**
     * 获取场景名称
     * @param int $scene
     * @return string
     */
    public static function getSceneName($scene)
    {
        $list = self::sceneList();
        return isset($list[$scene]) ? $list[$scene] : '其他';
    }

    /**
     * 写入一条水票流水
     * @param int $user_id 用户ID
     * @param int $num 变动数量(正增负减)
     * @param int $balance 变动后余额
     * @param int $scene 场景
     * @param int $wxapp_id 小程序ID
     * @param string $remark 补充说明(订单号等)
     * @return bool
     */
    public static function add($user_id, $num, $balance, $scene, $wxapp_id = 0, $remark = '')
    {
        $sceneName = self::getSceneName($scene);
        $describe  = $sceneName;
        $remark    = trim($remark);
        $remark !== '' && $describe .= "：{$remark}";
        return (new static)->save([
            'user_id'     => (int)$user_id,
            'wxapp_id'    => (int)$wxapp_id,
            'scene'       => (int)$scene,
            'num'         => (int)$num,
            'balance'     => (int)$balance,
            'describe'    => $describe,
            'create_time' => time(),
        ]) !== false;
    }

    /**
     * 用户水票流水列表(分页)
     * @param int $user_id 用户ID
     * @param int $wxapp_id 小程序ID
     * @param int $page 页码
     * @return \think\Paginator
     */
    public static function getList($user_id, $wxapp_id = 0, $page = 1)
    {
        $model = (new static)
            ->where('user_id', '=', (int)$user_id)
            ->order(['create_time' => 'desc', 'id' => 'desc']);
        $wxapp_id > 0 && $model = $model->where('wxapp_id', '=', (int)$wxapp_id);
        $list = $model->paginate(15, false, ['page' => (int)$page]);
        // 格式化时间，避免客户端再做处理
        // 注：不用 Paginator::each()，以兼容不同 think-orm 版本
        foreach ($list as $item) {
            $item['create_time'] = date('Y-m-d H:i', $item['create_time']);
        }
        return $list;
    }
}
