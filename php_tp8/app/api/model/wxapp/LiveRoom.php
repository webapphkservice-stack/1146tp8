<?php

namespace app\api\model\wxapp;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 微信小程序直播间模型（轻量 Db 实现，移植自 php_my api/model/wxapp/LiveRoom）
 *
 * RN 调用点：liveApi.lists() -> live/room/lists
 */
class LiveRoom extends BaseModel
{
    protected $name = 'wxapp_live_room';

    /**
     * 获取直播间列表（分页）
     */
    public static function getList($page = 1, $pageSize = 15)
    {
        $wxappId = self::getWxappId();
        $total = Db::name('wxapp_live_room')
            ->where('is_delete', 0)
            ->where('live_status', '<>', 107) // 已过期不显示
            ->where('wxapp_id', $wxappId)
            ->count();
        $rows = Db::name('wxapp_live_room')
            ->where('is_delete', 0)
            ->where('live_status', '<>', 107)
            ->where('wxapp_id', $wxappId)
            ->order(['is_top' => 'desc', 'live_status' => 'asc', 'create_time' => 'desc'])
            ->page($page, $pageSize)
            ->select()->toArray();
        $data = array_map([self::class, 'format'], $rows);
        return [
            'data'         => $data,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($total / $pageSize)),
            'total'        => $total,
            'per_page'     => $pageSize,
        ];
    }

    /**
     * 统一字段格式化
     */
    public static function format($item)
    {
        $status = (int)$item['live_status'];
        $text = '';
        if ($status === 101) {
            $text = '直播中';
        } elseif ($status === 102) {
            $text = self::semanticStartTime($item['start_time']) . ' 开播';
        } elseif ($status === 103) {
            $text = '已结束';
        }
        return [
            'id'            => $item['id'],
            'room_id'       => $item['room_id'],
            'room_name'     => $item['room_name'],
            'cover_img'     => BaseModel::getFileUrl($item['cover_img']),
            'share_img'     => BaseModel::getFileUrl($item['share_img']),
            'anchor_name'   => $item['anchor_name'],
            'start_time'    => $item['start_time'],
            'end_time'      => $item['end_time'],
            'live_status'   => $status,
            'live_status_text' => $text,
        ];
    }

    /**
     * 语义化开播时间
     */
    private static function semanticStartTime($startTime)
    {
        if (!$startTime) {
            return '';
        }
        $startDate = date('Ymd', $startTime);
        $today = date('Ymd');
        $tomorrow = date('Ymd', strtotime('+1 day'));
        if ($startDate == $today) {
            return date('今天 H:i', $startTime);
        }
        if ($startDate == $tomorrow) {
            return date('明天 H:i', $startTime);
        }
        return date('m/d H:i', $startTime);
    }
}
