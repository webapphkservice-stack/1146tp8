<?php
namespace app\store\controller;

use think\facade\Db;
use think\Collection;
use app\common\model\BaseModel;
use app\store\model\Goods as GoodsModel;
use app\store\model\Category as CategoryModel;
use app\common\model\store\Shop as ShopModel;

/**
 * 数据选择器弹层控制器（选品/选用户/选门店）
 * 对应前端 select.data.js 的 iframe 地址：STORE_URL + '/data.' + uri
 * 实际请求路径形如 /store/data.goods/lists、/store/data.user/lists 等。
 * 视图位于 app/store/view/data/*，本控制器仅负责取数并补齐视图期望的嵌套结构。
 */
class Data extends Controller
{
    /**
     * 商品列表（普通商品）
     */
    public function goods()
    {
        $param = $this->request->param();
        $model = new GoodsModel;
        $list = $model->getList(array_merge(['status' => -1], $param));

        $items = $list->items();
        $goodsIds = array_column($items, 'goods_id');
        $skuMap = [];
        if ($goodsIds) {
            $rows = Db::name('goods_sku')
                ->where('goods_id', 'in', $goodsIds)
                ->order('goods_sku_id', 'asc')
                ->field('goods_id, goods_price, line_price')
                ->select()->toArray();
            foreach ($rows as $r) {
                $skuMap[$r['goods_id']][] = [
                    'goods_price' => $r['goods_price'],
                    'line_price'  => $r['line_price'],
                ];
            }
        }
        $new = [];
        foreach ($items as $it) {
            $it['sku'] = $skuMap[$it['goods_id']] ?? [['goods_price' => '', 'line_price' => '']];
            $new[] = $it;
        }
        $list->setCollection(Collection::make($new));

        $catgory = (new CategoryModel)->getCacheTree();
        return $this->fetch('data/goods/list', compact('list', 'catgory'));
    }

    /**
     * 用户列表
     */
    public function user()
    {
        $param = $this->request->param();
        $grade = $param['grade'] ?? '';
        $gender = $param['gender'] ?? '';
        $nickName = $param['nickName'] ?? '';
        $filter = [];
        if ($grade !== '') {
            $filter[] = ['grade_id', '=', $grade];
        }
        if ($gender !== '') {
            $filter[] = ['gender', '=', $gender];
        }
        if ($nickName !== '') {
            $filter[] = ['nick_name', 'like', '%' . trim($nickName) . '%'];
        }
        $list = Db::name('user')
            ->where('is_delete', 0)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->where($filter)
            ->order('user_id', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $gradeIds = array_unique(array_column($items, 'grade_id'));
        $gradeMap = [];
        if ($gradeIds) {
            $rows = Db::name('user_grade')
                ->where('grade_id', 'in', $gradeIds)
                ->field('grade_id, name')->select()->toArray();
            foreach ($rows as $r) {
                $gradeMap[$r['grade_id']] = $r['name'];
            }
        }
        $new = [];
        foreach ($items as $it) {
            $avatar = $it['avatar_url'] ?? ($it['avatarUrl'] ?? '');
            if (is_numeric($avatar)) {
                $avatar = BaseModel::getFileUrl($avatar);
            }
            $it['nickName'] = $it['nick_name'] ?? '';
            $it['avatarUrl'] = $avatar ?: '';
            $it['grade'] = ['name' => $gradeMap[$it['grade_id']] ?? ''];
            $new[] = $it;
        }
        $list->setCollection(Collection::make($new));

        $gradeList = Db::name('user_grade')
            ->where('wxapp_id', BaseModel::getWxappId())
            ->field('grade_id, name')->order('grade_id')->select()->toArray();
        return $this->fetch('data/user/list', compact('list', 'gradeList'));
    }

    /**
     * 门店列表
     */
    public function shop()
    {
        $param = $this->request->param();
        $isCheck = $param['is_check'] ?? '';
        $search = $param['search'] ?? '';
        $model = new ShopModel;
        $query = $model->with(['logo']);
        if ($isCheck !== '') {
            $query->where('is_check', $isCheck);
        }
        if ($search !== '') {
            $query->where('shop_name|contact_name|phone', 'like', '%' . trim($search) . '%');
        }
        $list = $query->where('is_delete', 0)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->order('shop_id', 'desc')
            ->paginate(15, false, ['query' => request()->param()]);
        return $this->fetch('data/shop/list', compact('list'));
    }

    /**
     * 秒杀商品列表
     */
    public function sharpGoods()
    {
        $param = $this->request->param();
        $search = $param['search'] ?? '';
        $list = Db::name('sharp_goods')->alias('sg')
            ->join('goods g', 'g.goods_id = sg.goods_id')
            ->where('sg.is_delete', 0)
            ->where('sg.wxapp_id', BaseModel::getWxappId())
            ->when($search !== '', function ($q) use ($search) {
                $q->where('g.goods_name', 'like', '%' . trim($search) . '%');
            })
            ->order(['sg.sort' => 'asc', 'sg.create_time' => 'desc'])
            ->field('sg.*, g.goods_name')
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $sharpIds = array_column($items, 'sharp_goods_id');
        $goodsIds = array_unique(array_column($items, 'goods_id'));
        $imgMap = $this->goodsImageMap($goodsIds);
        $skuMap = [];
        if ($sharpIds) {
            $rows = Db::name('sharp_goods_sku')
                ->where('sharp_goods_id', 'in', $sharpIds)
                ->order('sharp_goods_sku_id', 'asc')
                ->field('sharp_goods_id, seckill_price, original_price')
                ->select()->toArray();
            foreach ($rows as $r) {
                $skuMap[$r['sharp_goods_id']][] = [
                    'seckill_price' => $r['seckill_price'],
                    'original_price' => $r['original_price'],
                ];
            }
        }
        $new = [];
        foreach ($items as $it) {
            $it['goods'] = [
                'goods_name'   => $it['goods_name'],
                'goods_image'  => $imgMap[$it['goods_id']] ?? '',
            ];
            $it['goods_sku'] = $skuMap[$it['sharp_goods_id']][0]
                ?? ['seckill_price' => '', 'original_price' => ''];
            $new[] = $it;
        }
        $list->setCollection(Collection::make($new));
        return $this->fetch('data/sharp/goods/list', compact('list'));
    }

    /**
     * 拼团商品列表
     */
    public function sharingGoods()
    {
        $param = $this->request->param();
        $status = $param['status'] ?? -1;
        $categoryId = $param['category_id'] ?? 0;
        $search = $param['search'] ?? '';
        $model = new \app\store\model\sharing\Goods;
        if ($status > -1) {
            $model->where('goods_status', '=', $status);
        }
        if ($categoryId > 0) {
            $model->where('category_id', '=', $categoryId);
        }
        if ($search !== '') {
            $model->where('goods_name', 'like', '%' . trim($search) . '%');
        }
        $list = $model->where('is_delete', 0)
            ->where('wxapp_id', BaseModel::getWxappId())
            ->order(['goods_sort' => 'asc', 'create_time' => 'desc'])
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $goodsIds = array_column($items, 'goods_id');
        $imgMap = $this->sharingImageMap($goodsIds);
        $skuMap = [];
        if ($goodsIds) {
            $rows = Db::name('sharing_goods_sku')
                ->where('goods_id', 'in', $goodsIds)
                ->order('goods_sku_id', 'asc')
                ->field('goods_id, sharing_price, line_price')
                ->select()->toArray();
            foreach ($rows as $r) {
                $skuMap[$r['goods_id']][] = [
                    'sharing_price' => $r['sharing_price'],
                    'line_price'    => $r['line_price'],
                ];
            }
        }
        $catIds = array_unique(array_column($items, 'category_id'));
        $catMap = [];
        if ($catIds) {
            $rows = Db::name('category')
                ->where('category_id', 'in', $catIds)
                ->field('category_id, name')->select()->toArray();
            foreach ($rows as $r) {
                $catMap[$r['category_id']] = ['name' => $r['name']];
            }
        }
        $new = [];
        foreach ($items as $it) {
            $it['image'] = $imgMap[$it['goods_id']] ?? [];
            $it['sku'] = $skuMap[$it['goods_id']] ?? [['sharing_price' => '', 'line_price' => '']];
            $it['category'] = $catMap[$it['category_id']] ?? ['name' => ''];
            $new[] = $it;
        }
        $list->setCollection(Collection::make($new));

        $catgory = (new CategoryModel)->getCacheTree();
        return $this->fetch('data/sharing/goods/list', compact('list', 'catgory'));
    }

    /**
     * 砍价活动列表
     */
    public function bargainGoods()
    {
        $param = $this->request->param();
        $search = $param['search'] ?? '';
        $list = Db::name('bargain_active')->alias('ba')
            ->withoutGlobalScope(['wxappId'])
            ->join('goods g', 'g.goods_id = ba.goods_id')
            ->where('ba.is_delete', 0)
            ->where('ba.wxapp_id', BaseModel::getWxappId())
            ->when($search !== '', function ($q) use ($search) {
                $q->where('g.goods_name', 'like', '%' . trim($search) . '%');
            })
            ->order(['ba.sort' => 'asc', 'ba.create_time' => 'desc'])
            ->field('ba.*, g.goods_name')
            ->paginate(15, false, ['query' => request()->param()]);

        $items = $list->items();
        $goodsIds = array_unique(array_column($items, 'goods_id'));
        $imgMap = $this->goodsImageMap($goodsIds);
        $skuMap = [];
        if ($goodsIds) {
            $rows = Db::name('goods_sku')
                ->where('goods_id', 'in', $goodsIds)
                ->order('goods_sku_id', 'asc')
                ->field('goods_id, goods_price')
                ->select()->toArray();
            foreach ($rows as $r) {
                $skuMap[$r['goods_id']][] = ['goods_price' => $r['goods_price']];
            }
        }
        $new = [];
        foreach ($items as $it) {
            $it['goods'] = [
                'goods_name'  => $it['goods_name'],
                'goods_image' => $imgMap[$it['goods_id']] ?? '',
                'goods_sku'   => $skuMap[$it['goods_id']][0] ?? ['goods_price' => ''],
            ];
            $new[] = $it;
        }
        $list->setCollection(Collection::make($new));
        return $this->fetch('data/bargain/goods/list', compact('list'));
    }

    /**
     * 普通商品主图 map：goods_id => 第一张图 file_path
     */
    private function goodsImageMap(array $goodsIds)
    {
        $map = [];
        if (!$goodsIds) {
            return $map;
        }
        $rows = Db::name('goods_image')->alias('gi')
            ->where('gi.goods_id', 'in', $goodsIds)
            ->where('gi.wxapp_id', BaseModel::getWxappId())
            ->order('gi.id', 'asc')
            ->field('gi.goods_id, gi.image_id')
            ->select()->toArray();
        foreach ($rows as $r) {
            if (!isset($map[$r['goods_id']])) {
                $map[$r['goods_id']] = BaseModel::getFileUrl($r['image_id']);
            }
        }
        return $map;
    }

    /**
     * 拼团商品主图 map：goods_id => [ {file_path}, ... ]
     */
    private function sharingImageMap(array $goodsIds)
    {
        $map = [];
        if (!$goodsIds) {
            return $map;
        }
        $rows = Db::name('sharing_goods_image')->alias('gi')
            ->where('gi.goods_id', 'in', $goodsIds)
            ->order('gi.id', 'asc')
            ->field('gi.goods_id, gi.image_id')
            ->select()->toArray();
        foreach ($rows as $r) {
            $map[$r['goods_id']][] = ['file_path' => BaseModel::getFileUrl($r['image_id'])];
        }
        return $map;
    }
}
