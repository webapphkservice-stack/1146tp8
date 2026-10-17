<?php

namespace app\store\controller\content\article;

use app\store\controller\Controller;
use app\store\model\article\Category as CategoryModel;

/**
 * 文章分类管理
 * Class Category
 * @package app\store\controller\content\article
 */
class Category extends Controller
{
    /**
     * 分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new CategoryModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加分类
     * @return array|mixed
     */
    public function add()
    {
        $model = new CategoryModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', url('content.article.category/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑分类
     * @param $category_id
     * @return array|mixed
     */
    public function edit($category_id)
    {
        // 分类详情
        $model = CategoryModel::detail($category_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('category'))) {
            return $this->renderSuccess('更新成功', url('content.article.category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除分类
     * @param $category_id
     * @return array
     */
    public function delete($category_id)
    {
        // 分类详情
        $model = CategoryModel::detail($category_id);
        if (!$model->remove($category_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}