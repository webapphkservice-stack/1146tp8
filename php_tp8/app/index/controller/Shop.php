<?php

namespace app\index\controller;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * PC 市集（商家列表）。
 *
 * 数据源与后台「商城列表」对齐：读取 wxapp 表（排除平台主商城 10001），
 * 展示每个商城的商家信息与在售商品。
 */
class Shop
{
    const MAIN_WXAPP_ID = 10001;

    public function index()
    {
        BaseModel::setApiWxappId(self::MAIN_WXAPP_ID);

        $keyword = trim((string)request()->param('keyword', ''));
        $page = max(1, (int)request()->param('page', 1));
        $pageSize = 12;

        // 1. 商家（商城）列表，排除主商城
        $malls = Db::name('wxapp')
            ->where('is_delete', 0)
            ->where('is_recycle', 0)
            ->where('wxapp_id', '<>', self::MAIN_WXAPP_ID)
            ->order('wxapp_id', 'desc')
            ->select()
            ->toArray();

        $wxappIds = array_column($malls, 'wxapp_id');

        // 2. 每个商城的在售商品
        $goodsByWxapp = $this->buildGoodsMap($wxappIds);

        // 3. 组装商家卡片
        $rows = [];
        foreach ($malls as $m) {
            $wid = (int)$m['wxapp_id'];
            $name = trim((string)($m['app_name'] ?: $m['store_name'] ?: ('商城 ' . $wid)));
            $storeName = trim((string)$m['store_name']);
            $siteName = trim((string)$m['site_name']);

            if ($keyword !== '') {
                $haystack = $name . ' ' . $storeName . ' ' . $siteName;
                if (mb_stripos($haystack, $keyword, 0, 'UTF-8') === false) {
                    continue;
                }
            }

            $goods = $goodsByWxapp[$wid] ?? [];

            $subParts = [];
            if ($storeName !== '' && $storeName !== $name) {
                $subParts[] = $storeName;
            }
            if ($siteName !== '' && $siteName !== $name && $siteName !== $storeName) {
                $subParts[] = $siteName;
            }

            $rows[] = [
                'wxapp_id'    => $wid,
                'name'        => $name,
                'sub'         => implode(' · ', $subParts),
                'goods'       => $goods,
                'goods_count' => count($goods),
            ];
        }

        // 4. 分页
        $total = count($rows);
        $lastPage = max(1, (int)ceil($total / $pageSize));
        $page = min($page, $lastPage);
        $pageRows = array_slice($rows, ($page - 1) * $pageSize, $pageSize);

        return view('shop/index', [
            'active'  => 'shop',
            'keyword' => $keyword,
            'list'    => [
                'data'         => $pageRows,
                'total'        => $total,
                'current_page' => $page,
                'last_page'    => $lastPage,
            ],
        ]);
    }

    /**
     * 商品数据：按 wxapp_id 分组，每项含名称 / 最低售价 / 封面图。
     */
    protected function buildGoodsMap(array $wxappIds)
    {
        $map = [];
        if (empty($wxappIds)) {
            return $map;
        }

        $goodsRows = Db::name('goods')
            ->whereIn('wxapp_id', $wxappIds)
            ->where('is_delete', 0)
            ->where('goods_status', 10)
            ->order('goods_sort', 'asc')
            ->order('goods_id', 'desc')
            ->select()
            ->toArray();

        if (empty($goodsRows)) {
            return $map;
        }

        $goodsIds = array_column($goodsRows, 'goods_id');

        // 最低售价（多 SKU 取 MIN）
        $priceMap = [];
        foreach (Db::name('goods_sku')
            ->whereIn('goods_id', $goodsIds)
            ->field('goods_id, MIN(goods_price) as price')
            ->group('goods_id')
            ->select()
            ->toArray() as $s) {
            $priceMap[(int)$s['goods_id']] = $s['price'];
        }

        // 封面图（goods_image 第一张）
        $imageIdMap = [];
        foreach (Db::name('goods_image')
            ->whereIn('goods_id', $goodsIds)
            ->order('id', 'asc')
            ->field('goods_id, image_id')
            ->select()
            ->toArray() as $r) {
            $gid = (int)$r['goods_id'];
            if (!isset($imageIdMap[$gid])) {
                $imageIdMap[$gid] = (int)$r['image_id'];
            }
        }

        foreach ($goodsRows as $g) {
            $gid = (int)$g['goods_id'];
            $wid = (int)$g['wxapp_id'];
            $imageId = $imageIdMap[$gid] ?? 0;
            $map[$wid][] = [
                'goods_id'   => $gid,
                'goods_name' => (string)$g['goods_name'],
                'price'      => isset($priceMap[$gid]) ? (string)$priceMap[$gid] : '0.00',
                'image'      => $imageId ? BaseModel::getFileUrl($imageId) : '',
            ];
        }

        return $map;
    }
}