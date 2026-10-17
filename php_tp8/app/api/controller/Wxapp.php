<?php
namespace app\api\controller;

use think\facade\Db;

/**
 * 帮助中心控制器（免登录）
 *
 * 命名说明：RN 调用 Platform/HelpScreen => wxappApi.help() => GET wxapp/help。
 * 本项目 thinkphp 实际走「默认 URL 解析（控制器/操作）」，而显式路由对 wxapp 这类
 * 首段会被 thinkphp 当作「模块」导致 404，故直接将控制器命名为 Wxapp、方法 help，
 * 使 wxapp/help 经默认解析命中本控制器（与 Help 控制器命中 help/index 同理）。
 */
class Wxapp extends Controller
{
    /**
     * 小程序基础信息（与 TP5 行为一致，返回空结构）
     */
    public function base()
    {
        return $this->renderSuccess([]);
    }

    public function help()
    {
        $list = Db::name('wxapp_help')
            ->where('wxapp_id', $this->getWxappId())
            ->order('sort', 'asc')
            ->order('help_id', 'asc')
            ->field('help_id, title, content')
            ->select()->toArray();
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取所有商户的信息（移植自 TP5 wxapp/getWxappStore）
     * 返回每个商户的坐标 + 3 个在售商品（含封面图 URL）。
     */
    public function getWxappStore()
    {
        $rows = Db::name('wxapp')
            ->where('is_recycle', 0)
            ->where('is_delete', 0)
            ->select()->toArray();
        foreach ($rows as $k => $v) {
            // avatar/coordinate 在 TP5 线上库存在，本地精简库可能缺失；头像降级取 wxapp_icon.logo
            $avatar = $v['avatar'] ?? '';
            $coordinate = $v['coordinate'] ?? '';
            if ($avatar === '') {
                $avatar = (string)(Db::name('wxapp_icon')
                    ->where('wxapp_id', $v['wxapp_id'])->value('logo') ?: '');
            }
            $rows[$k] = [
                'wxapp_id'   => $v['wxapp_id'],
                'avatar'     => $avatar,
                'coordinate' => $coordinate,
            ];
        }
        foreach ($rows as $k => $v) {
            // 商户名与联系电话（applystore_store 表可能不存在，容错处理）
            $storeName = null;
            $phone = null;
            try {
                $store = Db::name('applystore_store')
                    ->where('wxapp_id', $v['wxapp_id'])
                    ->find();
                if ($store) {
                    $storeName = $store['store_name'] ?? null;
                    $phone = $store['legal_phone'] ?? null;
                }
            } catch (\Exception $e) {
                // 表缺失时忽略
            }
            $rows[$k]['storeName'] = $storeName;
            $rows[$k]['phone'] = $phone;

            // 每个商户取 3 个在售商品（含 sku 价格）
            $goodsList = [];
            try {
                $goodsRows = Db::name('goods')
                    ->alias('a')
                    ->join('goods_sku b', 'a.goods_id = b.goods_id')
                    ->where('a.wxapp_id', $v['wxapp_id'])
                    ->where('a.is_delete', 0)
                    ->where('a.goods_status', 10)
                    ->limit(3)
                    ->field('a.goods_id, a.goods_name, b.goods_price, b.goods_sku_id, b.product_id')
                    ->select()->toArray();
                foreach ($goodsRows as $g) {
                    $g['image'] = $this->goodsImage($g['goods_id']);
                    $goodsList[] = $g;
                }
            } catch (\Exception $e) {
                // 查询失败保留空商品列表
            }
            $rows[$k]['goods'] = $goodsList;
        }
        return $this->renderSuccess($rows);
    }

    /** 商品封面图 URL（goods_image 第一张） */
    private function goodsImage($goodsId)
    {
        $imageId = (int)Db::name('goods_image')
            ->where('goods_id', $goodsId)
            ->order('id', 'asc')
            ->value('image_id');
        if ($imageId <= 0) {
            return '';
        }
        return Db::name('upload_file')->where('file_id', $imageId)->value('file_url') ?: '';
    }
}
