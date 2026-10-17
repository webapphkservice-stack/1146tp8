<?php
namespace app\store\controller;

use app\store\controller\Controller;
use app\store\model\Goods as GoodsModel;
use app\store\model\Category as CategoryModel;
use app\store\service\Goods as GoodsService;

/**
 * 商品管理控制器（服务端渲染版，移植自 TP5 store/controller/Goods.php）
 */
class Goods extends Controller
{

    /** 商品列表(通用入口，/store/goods/lists → 出售中商品) */
    public function lists()
    {
        return $this->index();
    }
    /**
     * 商品列表(出售中)
     */
    public function index()
    {
        $model = new GoodsModel;
        $list = $model->getList(array_merge(['status' => -1], $this->request->param()));
        $catgory = (new CategoryModel)->getCacheTree();
        return $this->fetch('index', compact('list', 'catgory'));
    }

    /**
     * 添加商品
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add', GoodsService::getEditData(null, 'add'));
        }
        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', (string)url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 一键复制
     * @param int $goods_id
     */
    public function copy($goods_id)
    {
        $model = GoodsModel::detail($goods_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', array_merge(
                GoodsService::getEditData($model, 'copy'),
                compact('model')
            ));
        }
        $model = new GoodsModel;
        if ($model->add($this->postData('goods'))) {
            return $this->renderSuccess('添加成功', (string)url('goods/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 商品编辑
     * @param int $goods_id
     */
    public function edit($goods_id)
    {
        $model = GoodsModel::detail($goods_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', array_merge(
                GoodsService::getEditData($model),
                compact('model')
            ));
        }
        $data = array_merge($this->postData('goods'), ['goods_id' => $goods_id]);
        if ((new GoodsModel)->edit($data)) {
            return $this->renderSuccess('更新成功', (string)url('goods/index'));
        }
        return $this->renderError((new GoodsModel)->getError() ?: '更新失败');
    }

    /**
     * 修改商品状态
     * @param int $goods_id
     * @param bool $state
     */
    public function state($goods_id, $state)
    {
        if (!(new GoodsModel)->setStatus($goods_id, $state)) {
            return $this->renderError('操作失败');
        }
        return $this->renderSuccess('操作成功');
    }

    /**
     * 删除商品
     * @param int $goods_id
     */
    public function delete($goods_id)
    {
        $model = new GoodsModel;
        if (!$model->setDelete($goods_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
