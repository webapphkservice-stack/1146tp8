<?php

namespace app\h5\controller;

use app\api\model\Category as CategoryModel;
use app\api\model\Goods as GoodsModel;

/**
 * 手机网站 - 首页
 */
class Index extends Controller
{
    /**
     * web 端首页（Fresh Market 风格）
     * 访问路径：/h5/index/index、s=/home/（app_map: home => h5）
     */
    public function index()
    {
        // 顶级分类
        $categoryList = [];
        try {
            foreach ((new CategoryModel)->getTree() as $c) {
                $categoryList[] = ['category_id' => (int)$c['category_id'], 'name' => $c['name']];
            }
        } catch (\Throwable $e) {
        }

        // 推荐商品（在售，取 8 件）
        $goodsList = [];
        try {
            $goodsList = (new GoodsModel)->getList(['pagesize' => 8]);
        } catch (\Throwable $e) {
        }

        return $this->fetch('index/index', [
            'categoryList' => $categoryList,
            'goodsList'    => $goodsList,
            'storeName'    => $this->storeName(),
        ]);
    }

    /**
     * 商品列表（兼容旧链接 index.php?s=/home/index/goods）
     * app_map: home => h5 后路径解析为 Index/goods，此处委托给 Goods::list 渲染
     */
    public function goods()
    {
        return (new Goods())->list();
    }
}
