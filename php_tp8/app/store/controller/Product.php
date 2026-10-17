<?php

namespace app\store\controller;

/**
 * 商品管理别名控制器
 * 用于兼容外部访问 /store/product/* 的请求
 * 实际功能由 Goods 控制器提供
 */
class Product extends Controller
{
    /**
     * 商品列表
     * 重定向到 goods/index
     */
    public function lists()
    {
        return $this->redirect('goods/index');
    }

    /**
     * 默认操作
     * 重定向到商品列表
     */
    public function _empty($name)
    {
        return $this->redirect('goods/index');
    }
}
