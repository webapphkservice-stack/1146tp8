<?php
namespace app\api\controller;

use app\api\model\Category as CategoryModel;

/**
 * 商品分类控制器
 * Class Category
 * @package app\api\controller
 */
class Category extends Controller
{
    /**
     * 分类页面（RN 仅消费 list；templet 在 php_my 中为装修模板，RN 未使用，省略）
     * @return array
     */
    public function index()
    {
        $list = (new CategoryModel)->getTree();
        return $this->renderSuccess(compact('list'));
    }
}
