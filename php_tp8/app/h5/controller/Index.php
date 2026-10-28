<?php

namespace app\h5\controller;

/**
 * 手机网站 - 首页
 */
class Index extends Controller
{
    /**
     * 商品列表（兼容旧链接 index.php?s=/home/index/goods）
     * app_map: home => h5 后路径解析为 Index/goods，此处委托给 Goods::list 渲染
     */
    public function goods()
    {
        return (new Goods())->list();
    }
}
