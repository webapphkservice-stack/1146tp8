<?php
namespace app\store\service\goods;

/**
 * 商品活动(审核)服务（服务端渲染版，简化实现）
 * 对应 TP5 store/service/goods/Apply，活动限制在此返回允许。
 */
class Apply
{
    /**
     * 验证商品是否允许删除
     */
    public static function checkIsAllowDelete($goodsId)
    {
        return true;
    }

    /**
     * 商品规格是否锁定（活动中不允许修改规格）
     */
    public static function checkSpecLocked($goodsId)
    {
        return false;
    }
}
