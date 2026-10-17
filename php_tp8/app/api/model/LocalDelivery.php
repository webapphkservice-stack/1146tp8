<?php
namespace app\api\model;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 本地外送/配送模板模型（轻量版，直查 local_delivery + local_delivery_shop）
 * 对齐 RN delivery.ts lists/detail/goods
 */
class LocalDelivery
{
    /** 启用中的配送模板列表（含封面图 + 归属店铺） */
    public static function getApiList()
    {
        $rows = Db::name('local_delivery')
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('status', 1)
            ->where('is_delete', 0)
            ->order('sort', 'asc')
            ->select()->toArray();
        foreach ($rows as &$r) {
            $r['image'] = self::imageUrl($r['image_id']);
            $shop = Db::name('local_delivery_shop')
                ->where('shop_id', $r['shop_id'])
                ->where('wxapp_id', BaseModel::getWxappId())
                ->find();
            $r['shop'] = $shop ? self::formatShop($shop) : null;
        }
        return $rows;
    }

    /** 配送模板详情 */
    public static function detail($id)
    {
        $row = Db::name('local_delivery')
            ->where('delivery_id', $id)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('is_delete', 0)
            ->find();
        if ($row) {
            $row['image'] = self::imageUrl($row['image_id']);
            $shop = Db::name('local_delivery_shop')
                ->where('shop_id', $row['shop_id'])
                ->where('wxapp_id', BaseModel::getWxappId())
                ->find();
            $row['shop'] = $shop ? self::formatShop($shop) : null;
        }
        return $row;
    }

    /** 某店铺的配送模板（goods 接口） */
    public static function getApiListByShop($shopId)
    {
        $row = Db::name('local_delivery')
            ->where('shop_id', $shopId)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where('status', 1)
            ->where('is_delete', 0)
            ->order('sort', 'asc')
            ->find();
        if ($row) {
            $row['image'] = self::imageUrl($row['image_id']);
            $shop = Db::name('local_delivery_shop')
                ->where('shop_id', $shopId)
                ->where('wxapp_id', BaseModel::getWxappId())
                ->find();
            $row['shop'] = $shop ? self::formatShop($shop) : null;
        }
        return $row;
    }

    private static function formatShop($shop)
    {
        $shop['image'] = self::imageUrl($shop['image_id']);
        unset($shop['wxapp_id'], $shop['create_time'], $shop['update_time']);
        return $shop;
    }

    private static function imageUrl($fileId)
    {
        if (empty($fileId)) {
            return '';
        }
        return Db::name('upload_file')->where('file_id', $fileId)->value('file_url') ?: '';
    }
}
