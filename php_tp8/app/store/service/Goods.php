<?php
namespace app\store\service;

use app\common\model\BaseModel;
use think\facade\Db;
use app\store\model\Category as CategoryModel;
use app\store\model\Delivery;
use app\store\model\GoodsSku;
use app\store\service\goods\Apply as GoodsApplyService;

/**
 * 商品服务类（服务端渲染版，移植自 TP5 store/service/Goods.php）
 * 用 Db 直查自包含实现，不依赖缺失的 common 服务层。
 */
class Goods extends BaseModel
{
    /**
     * 商品管理公共数据（编辑/新增页）
     */
    public static function getEditData($model = null, $handle = 'edit')
    {
        $catgory = (new CategoryModel)->getCacheTree();
        $delivery = Delivery::getAll();
        $gradeList = Db::name('user_grade')->where('is_delete', 0)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->order('weight', 'asc')->select()->toArray();
        $specData = json_encode(static::getSpecData($model), JSON_UNESCAPED_UNICODE);
        $isSpecLocked = static::checkSpecLocked($model, $handle);
        return compact('catgory', 'delivery', 'gradeList', 'specData', 'isSpecLocked');
    }

    /**
     * 验证商品是否允许删除
     */
    public static function checkIsAllowDelete($goodsId)
    {
        return GoodsApplyService::checkIsAllowDelete($goodsId);
    }

    private static function checkSpecLocked($model = null, $handle = 'edit')
    {
        if ($model == null || $handle == 'copy') {
            return false;
        }
        return GoodsApplyService::checkSpecLocked($model['goods_id']);
    }

    /**
     * 组装商品规格数据（供编辑页 Vue 组件）
     */
    public static function getSpecData($model)
    {
        if ($model === null) {
            return ['spec_attr' => [], 'spec_list' => []];
        }
        $specRel = Db::name('goods_spec_rel')->alias('gsr')
            ->join('spec s', 's.spec_id = gsr.spec_id')
            ->join('spec_value sv', 'sv.spec_value_id = gsr.spec_value_id')
            ->where('gsr.goods_id', $model['goods_id'])
            ->field('gsr.spec_id, gsr.spec_value_id, s.spec_name, sv.spec_value')
            ->order('gsr.id', 'asc')->select()->toArray();
        $skuData = Db::name('goods_sku')->where('goods_id', $model['goods_id'])
            ->order('goods_sku_id', 'asc')->select()->toArray();
        return (new GoodsSku)->getManySpecData($specRel, $skuData);
    }
}
