<?php

namespace app\h5\controller;

use app\api\model\Category as CategoryModel;

/**
 * 手机网站 - 商品分类
 */
class Category extends Controller
{
    /**
     * 分类页（两级分类）
     * @return mixed
     */
    public function index()
    {
        $list = (new CategoryModel)->getTree();
        return $this->fetch('category/index', [
            'list'      => $list,
            'storeName' => $this->storeName(),
        ]);
    }
}