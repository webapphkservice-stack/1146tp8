<?php
namespace app\api\model;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 本地外送店铺模型（轻量版，直查 local_delivery_shop）
 * 对齐 RN delivery.ts shop / goods
 */
class LocalDeliveryShop
{
    /** 启用中的店铺列表 */
    public static function getApiEnabledShops()
    {
        $rows = Db::name('local_delivery_shop')
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()->toArray();
        foreach ($rows as &$r) {
            $r['image'] = self::imageUrl($r['image_id']);
            unset($r['wxapp_id'], $r['create_time'], $r['update_time']);
        }
        return $rows;
    }

    /** 店铺详情 */
    public static function detail($shopId)
    {
        $row = Db::name('local_delivery_shop')
            ->where('shop_id', $shopId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->find();
        if ($row) {
            $row['image'] = self::imageUrl($row['image_id']);
            unset($row['wxapp_id'], $row['create_time'], $row['update_time']);
        }
        return $row;
    }

    private static function imageUrl($fileId)
    {
        if (empty($fileId)) {
            return '';
        }
        return Db::name('upload_file')->where('file_id', $fileId)->value('file_url') ?: '';
    }
}
