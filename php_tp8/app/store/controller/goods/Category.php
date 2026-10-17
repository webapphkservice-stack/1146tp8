<?php
namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\Category as CategoryModel;

/**
 * 商品分类控制器（服务端渲染版，移植自 TP5 store/controller/goods/Category.php）
 */
class Category extends Controller
{
    /**
     * 商品分类列表
     */
    public function index()
    {
        $model = new CategoryModel;
        $list = $model->getCacheTree();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param int $category_id
     */
    public function delete($category_id)
    {
        $model = new CategoryModel;
        if (!$model->remove($category_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     */
    public function add()
    {
        $model = new CategoryModel;
        if (!$this->request->isAjax()) {
            $list = $model->getCacheTree();
            return $this->fetch('add', compact('list'));
        }
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', (string)url('goods.category/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param int $category_id
     */
    public function edit($category_id)
    {
        $model = new CategoryModel;
        $detail = CategoryModel::detail($category_id, ['image']);
        if (!$this->request->isAjax()) {
            $list = $model->getCacheTree();
            return $this->fetch('edit', ['model' => $detail, 'list' => $list]);
        }
        $data = $this->postData('category');
        $data['category_id'] = $category_id;
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('goods.category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
}
