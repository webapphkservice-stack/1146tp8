<?php

namespace app\index\controller;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * PC 市集（商家门店列表）。
 */
class Shop
{
    public function index()
    {
        BaseModel::setApiWxappId(10001);

        $keyword = trim((string)request()->param('keyword', ''));
        $page = max(1, (int)request()->param('page', 1));
        $pageSize = 12;

        $query = Db::name('store_shop')
            ->alias('s')
            ->leftJoin('upload_file f', 'f.file_id = s.logo_image_id')
            ->where('s.wxapp_id', 10001)
            ->where('s.status', 1)
            ->where('s.is_delete', 0);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('s.shop_name', "%{$keyword}%")
                  ->whereOrLike('s.linkman', "%{$keyword}%")
                  ->whereOrLike('s.address', "%{$keyword}%");
            });
        }

        $total = $query->count();
        $rows = $query->order('s.sort', 'asc')
            ->order('s.shop_id', 'desc')
            ->page($page, $pageSize)
            ->field('s.shop_id, s.shop_name, s.linkman, s.phone, s.shop_hours, s.address, s.summary, s.longitude, s.latitude, f.file_url as logo')
            ->select()
            ->toArray();

        foreach ($rows as &$row) {
            $row['logo'] = (string)($row['logo'] ?? '');
        }
        unset($row);

        $lastPage = max(1, (int)ceil($total / $pageSize));

        return view('shop/index', [
            'active'   => 'shop',
            'keyword'  => $keyword,
            'list'     => [
                'data'         => $rows,
                'total'        => $total,
                'current_page' => $page,
                'last_page'    => $lastPage,
            ],
        ]);
    }
}