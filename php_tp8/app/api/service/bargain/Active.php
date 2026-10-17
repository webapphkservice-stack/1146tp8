<?php

namespace app\api\service\bargain;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 砍价活动查询服务（对齐 RN 契约，轻量 Db 直查）
 *
 * 注意契约修正（php_my 原返回缺下列字段，TP8 自行补齐）：
 *  - active.original_price：php_my 无此列，TP8 取默认 sku 的 goods_price 作为 RN 划线原价
 *  - active.image：由 goods.image 提供
 *  - task.cut_rate：php_my 叫 bargain_rate 且已是百分数，TP8 直接输出 cut_rate
 */
class Active
{
    public static function getHallList($wxappId, $param = [])
    {
        $page = max(1, (int)($param['page'] ?? 1));
        $now = time();
        $rows = Db::name('bargain_active')
            ->where('wxapp_id', $wxappId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->order('sort', 'asc')->order('active_id', 'desc')
            ->paginate(['page' => $page, 'list_rows' => 15])->toArray();
        $rows['data'] = array_map([self::class, 'formatHallItem'], $rows['data']);
        return $rows;
    }

    public static function formatHallItem($a)
    {
        $goods = self::getGoodsBrief($a['goods_id']);
        return [
            'active_id'      => $a['active_id'],
            'goods_id'       => $a['goods_id'],
            'floor_price'    => (float)$a['floor_price'],
            'original_price'=> $goods ? (float)($goods['goods_price'] ?? 0) : 0,
            'peoples'        => (int)$a['peoples'],
            'status'         => (int)$a['status'],
            'start_time'     => $a['start_time'],
            'end_time'       => $a['end_time'],
            'share_title'    => $a['share_title'],
            'prompt_words'   => $a['prompt_words'],
            'active_sales'   => (int)$a['actual_sales'] + (int)$a['initial_sales'],
            'image'          => $goods ? $goods['image'] : '',
            'goods_name'     => $goods ? $goods['goods_name'] : '',
        ];
    }

    public static function getDetail($activeId)
    {
        $a = Db::name('bargain_active')
            ->where('active_id', $activeId)
            ->where('is_delete', 0)
            ->find();
        if (empty($a)) {
            return false;
        }
        $goods = self::getGoodsBrief($a['goods_id']);
        return [
            'active_id'      => $a['active_id'],
            'goods_id'       => $a['goods_id'],
            'floor_price'    => (float)$a['floor_price'],
            'original_price'=> $goods ? (float)($goods['goods_price'] ?? 0) : 0,
            'peoples'        => (int)$a['peoples'],
            'status'         => (int)$a['status'],
            'start_time'     => $a['start_time'],
            'end_time'       => $a['end_time'],
            'expiryt_time'   => (int)$a['expiryt_time'],
            'share_title'    => $a['share_title'],
            'prompt_words'   => $a['prompt_words'],
            'is_self_cut'    => (int)$a['is_self_cut'],
            'is_floor_buy'   => (int)$a['is_floor_buy'],
            'active_sales'   => (int)$a['actual_sales'] + (int)$a['initial_sales'],
            'image'          => $goods ? $goods['image'] : '',
            'goods_name'     => $goods ? $goods['goods_name'] : '',
        ];
    }

    /** 当前用户是否已参与该活动（返回 task_id 或 0） */
    public static function getWhetherPartake($activeId, $userId)
    {
        $task = Db::name('bargain_task')
            ->where('active_id', $activeId)
            ->where('user_id', $userId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->order('task_id', 'desc')
            ->find();
        return $task ? (int)$task['task_id'] : 0;
    }

    /** 砍价规则配置（bargain_setting：key/values） */
    public static function getSetting($wxappId)
    {
        $rows = Db::name('bargain_setting')->where('wxapp_id', $wxappId)->select()->toArray();
        $setting = [];
        foreach ($rows as $r) {
            $setting[$r['key']] = $r['values'];
        }
        return $setting;
    }

    /** 商品简要（图/名/默认价），供 active 补充 image/original_price；原价取自 goods_sku 默认 sku */
    public static function getGoodsBrief($goodsId)
    {
        $g = Db::name('goods')->where('goods_id', $goodsId)
            ->where('goods_status', 10)->where('is_delete', 0)
            ->field('goods_id,goods_name')->find();
        if (empty($g)) {
            return null;
        }
        $sku = Db::name('goods_sku')->where('goods_id', $goodsId)
            ->order('goods_sku_id', 'asc')->find();
        $image = Db::name('goods_image')->where('goods_id', $goodsId)->order('id', 'asc')->value('image_id');
        return [
            'goods_id'    => $g['goods_id'],
            'goods_name'  => $g['goods_name'],
            'goods_price' => $sku ? (float)$sku['goods_price'] : 0,
            'image'       => BaseModel::getFileUrl($image ?: 0),
        ];
    }
}
