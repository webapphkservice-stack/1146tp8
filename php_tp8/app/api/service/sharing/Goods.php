<?php

namespace app\api\service\sharing;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团商品查询服务（对齐 RN 契约，轻量 Db 直查，不复用 php_my 的嵌套 model 体系）
 *
 * 数据来源：sharing_goods / sharing_goods_sku / sharing_active / sharing_active_users / user
 * 图片：优先 sharing_goods_image，其次 sku.image_id，再次主 goods.image_id
 */
class Goods
{
    /**
     * 拼团商品主图 url
     */
    public static function getImage($goodsId, $sku = null)
    {
        $img = Db::name('sharing_goods_image')->where('goods_id', $goodsId)
            ->order('id', 'asc')->value('image_id');
        if (empty($img) && !empty($sku)) {
            $img = $sku['image_id'] ?? 0;
        }
        if (empty($img)) {
            $img = Db::name('goods_image')->where('goods_id', $goodsId)->order('id', 'asc')->value('image_id');
        }
        return BaseModel::getFileUrl($img ?: 0);
    }

    /**
     * 拼团商品列表（分页）
     * @return array paginate 结构，data 为格式化后的商品数组
     */
    public static function getList($wxappId, $param)
    {
        $page = max(1, (int)($param['page'] ?? 1));
        $query = Db::name('sharing_goods')
            ->where('wxapp_id', $wxappId)
            ->where('goods_status', 10)
            ->where('is_delete', 0);
        if (!empty($param['category_id'])) {
            $query->where('category_id', (int)$param['category_id']);
        }
        $rows = $query->order('goods_sort', 'asc')->order('goods_id', 'desc')
            ->paginate(['page' => $page, 'list_rows' => 15])->toArray();
        $rows['data'] = array_map([self::class, 'formatListItem'], $rows['data']);
        return $rows;
    }

    /**
     * 列表项格式化（RN SharingScreen 消费：goods_id/goods_name/image/goods_price/line_price/sales_actual/people_num）
     * 注意：列表主价显示拼团价（sku.sharing_price），划线为 line_price
     */
    public static function formatListItem($g)
    {
        $sku = Db::name('sharing_goods_sku')->where('goods_id', $g['goods_id'])
            ->order('goods_sku_id', 'asc')->find();
        return [
            'goods_id'     => $g['goods_id'],
            'goods_name'   => $g['goods_name'],
            'image'        => self::getImage($g['goods_id'], $sku),
            'goods_price'  => $sku ? (float)$sku['sharing_price'] : 0,
            'line_price'   => $sku ? (float)$sku['line_price'] : 0,
            'sales_actual' => (int)$g['sales_initial'] + (int)$g['sales_actual'],
            'people_num'   => (int)$g['people'],
        ];
    }

    /**
     * 拼团商品详情（RN SharingDetailScreen 消费：
     *  goods.image/goods_name/goods_price(原价)/sales_actual/sku[].sharing_price(拼团价)/sku[].goods_sku_id
     *  detail.people_num
     *  goodsList[].active_id/nickName/username/remain_people/surplus_people）
     * @param int $goodsId
     * @return array|false
     */
    public static function getDetail($goodsId)
    {
        $g = Db::name('sharing_goods')
            ->where('goods_id', $goodsId)
            ->where('goods_status', 10)
            ->where('is_delete', 0)
            ->find();
        if (empty($g)) {
            return false;
        }
        $skus = Db::name('sharing_goods_sku')->where('goods_id', $goodsId)->select()->toArray();
        $skuArr = array_map(function ($s) {
            return [
                'goods_sku_id'  => $s['goods_sku_id'],
                'spec_sku_id'   => $s['spec_sku_id'],
                'sharing_price' => (float)$s['sharing_price'],
                'goods_price'   => (float)$s['goods_price'],
                'line_price'    => (float)$s['line_price'],
                'stock_num'     => (int)$s['stock_num'],
                'image'         => BaseModel::getFileUrl($s['image_id']),
            ];
        }, $skus);
        $first = $skus[0] ?? null;
        return [
            'goods_id'     => $goodsId,
            'goods_name'   => $g['goods_name'],
            'image'        => self::getImage($goodsId, $first),
            'goods_price'  => $first ? (float)$first['goods_price'] : 0,
            'line_price'   => $first ? (float)$first['line_price'] : 0,
            'sales_actual' => (int)$g['sales_initial'] + (int)$g['sales_actual'],
            'people'       => (int)$g['people'],
            'people_num'   => (int)$g['people'],
            'sku'          => $skuArr,
            'content'      => $g['content'],
        ];
    }

    /**
     * 某商品进行中的拼单列表（RN goodsList 消费）
     */
    public static function getActiveList($goodsId, $limit = 5)
    {
        $rows = Db::name('sharing_active')->alias('a')
            ->join('user u', 'u.user_id = a.creator_id', 'left')
            ->where('a.goods_id', $goodsId)
            ->where('a.status', 10)
            ->field('a.active_id, a.people, a.actual_people, u.nickName')
            ->limit($limit)
            ->select()
            ->toArray();
        return array_map(function ($r) {
            $remain = max(0, (int)$r['people'] - (int)$r['actual_people']);
            return [
                'active_id'     => $r['active_id'],
                // yoshop_user 仅有 nickName（无 username 列），username 兼容 RN fallback 用 nickName
                'nickName'      => $r['nickName'],
                'username'      => $r['nickName'],
                'remain_people' => $remain,
                'surplus_people'=> $remain,
            ];
        }, $rows);
    }
}
