<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 商品 SKU / 规格关系模型（服务端渲染版）
 * 对应 TP5 common/model/GoodsSku 中 Goods 模型依赖的规格方法。
 */
class GoodsSku extends BaseModel
{
    protected $name = 'goods_sku';
    protected $pk = 'goods_sku_id';
    protected $updateTime = false;

    /**
     * 删除指定商品的所有 SKU 与规格关系
     */
    public function removeAll($goodsId)
    {
        Db::name('goods_sku')->where('goods_id', $goodsId)->delete();
        Db::name('goods_spec_rel')->where('goods_id', $goodsId)->delete();
        return true;
    }

    /**
     * 添加商品与规格关系
     */
    public function addGoodsSpecRel($goodsId, $specAttr)
    {
        $data = [];
        foreach ($specAttr as $attr) {
            foreach ($attr['spec_items'] as $item) {
                $data[] = [
                    'goods_id'      => $goodsId,
                    'spec_id'       => $attr['group_id'],
                    'spec_value_id' => $item['item_id'],
                    'wxapp_id'      => self::getWxappId(),
                    'create_time'   => time(),
                ];
            }
        }
        if ($data) {
            Db::name('goods_spec_rel')->insertAll($data);
        }
        return true;
    }

    /**
     * 添加商品 SKU 列表
     */
    public function addSkuList($goodsId, $specList)
    {
        $data = [];
        foreach ($specList as $item) {
            $form = $item['form'];
            $data[] = [
                'goods_id'     => $goodsId,
                'spec_sku_id'  => $item['spec_sku_id'],
                'image_id'     => (int)($form['image_id'] ?? 0),
                'goods_no'     => $form['goods_no'] ?? '',
                'goods_price'  => (float)($form['goods_price'] ?? 0),
                'line_price'   => (float)($form['line_price'] ?? 0),
                'stock_num'    => (int)($form['stock_num'] ?? 0),
                'goods_weight' => (float)($form['goods_weight'] ?? 0),
                'wxapp_id'     => self::getWxappId(),
                'create_time'  => time(),
                'update_time'  => time(),
            ];
        }
        if ($data) {
            Db::name('goods_sku')->insertAll($data);
        }
        return true;
    }

    /**
     * 组装多规格数据（供编辑页 Vue 组件使用）
     */
    public function getManySpecData($specRel, $skuData)
    {
        $specAttrData = [];
        foreach ($specRel as $item) {
            if (!isset($specAttrData[$item['spec_id']])) {
                $specAttrData[$item['spec_id']] = [
                    'group_id'   => $item['spec_id'],
                    'group_name' => $item['spec_name'],
                    'spec_items' => [],
                ];
            }
            $specAttrData[$item['spec_id']]['spec_items'][] = [
                'item_id'    => $item['spec_value_id'],
                'spec_value' => $item['spec_value'],
            ];
        }
        $specListData = [];
        foreach ($skuData as $item) {
            $specListData[] = [
                'goods_sku_id' => $item['goods_sku_id'],
                'spec_sku_id'  => $item['spec_sku_id'],
                'rows'         => [],
                'form'         => [
                    'image_id'    => $item['image_id'],
                    'image_path'  => '',
                    'goods_no'     => $item['goods_no'],
                    'goods_price'  => $item['goods_price'],
                    'goods_weight' => $item['goods_weight'],
                    'line_price'   => $item['line_price'],
                    'stock_num'    => $item['stock_num'],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }
}
