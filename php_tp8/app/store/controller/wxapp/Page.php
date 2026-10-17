<?php

namespace app\store\controller\wxapp;

use app\store\controller\Controller;
use app\store\model\Category as CategoryModel;
use app\store\model\sharing\Category as SharingCategoryModel;
use app\store\model\article\Category as ArticleCategoryModel;
use app\store\model\WxappPage as WxappPageModel;
use app\store\model\WxappCategory as WxappCategoryModel;
use app\common\model\WxappIcon as WxappIconModel;

/**
 * 小程序页面管理
 * Class Page
 * @package app\store\controller\wxapp
 */
class Page extends Controller
{
    /**
     * 页面列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new WxappPageModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增页面
     * @return array|mixed
     */
    public function add()
    {
        $model = new WxappPageModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', [
                'defaultData' => json_encode($model->getDefaultItems()),
                'jsonData' => json_encode(['page' => $model->getDefaultPage(), 'items' => []]),
                'opts' => json_encode([
                    'catgory' => CategoryModel::getCacheTree(),
                    'sharingCatgory' => SharingCategoryModel::getCacheTree(),
                    'articleCatgory' => ArticleCategoryModel::getALL(),
                ])
            ]);
        }
        // 接收post数据
        $post = $this->request->post('data', null, null);
        if (!$model->add(json_decode($post, true))) {
            return $this->renderError('添加失败');
        }
        return $this->renderSuccess('添加成功', url('wxapp.page/index'));
    }

    /**
     * 编辑页面
     * @param $page_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($page_id)
    {
        $model = WxappPageModel::detail($page_id);
        if (empty($model)) {
            return $this->renderError('页面不存在');
        }
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', [
                'defaultData' => json_encode($model->getDefaultItems()),
                'jsonData' => json_encode($model['page_data']),
                'opts' => json_encode([
                    'catgory' => CategoryModel::getCacheTree(),
                    'sharingCatgory' => SharingCategoryModel::getCacheTree(),
                    'articleCatgory' => ArticleCategoryModel::getALL(),
                ])
            ]);
        }
        // 接收post数据
        $post = $this->request->post('data', null, null);
        if (!$model->edit(json_decode($post, true))) {
            return $this->renderError('更新失败');
        }
        return $this->renderSuccess('更新成功');
    }

    /**
     * 删除页面
     * @param $page_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($page_id)
    {
        $model = WxappPageModel::detail($page_id);
        if (empty($model)) {
            return $this->renderError('页面不存在');
        }
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 设置默认首页
     * @param $page_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function setHome($page_id)
    {
        $model = WxappPageModel::detail($page_id);
        if (empty($model)) {
            return $this->renderError('页面不存在');
        }
        if (!$model->setHome()) {
            return $this->renderError($model->getError() ?: '设置失败');
        }
        return $this->renderSuccess('设置成功');
    }

    /**
     * 商品组:按分类取列表（编辑器实时预览用）
     * @return \think\response\Json
     */
    public function goods()
    {
        $item = [
            'type'   => 'goods',
            'params' => [
                'source' => 'auto',
                'auto'   => [
                    'category'  => (int)$this->request->param('category', 0),
                    'goodsSort' => $this->request->param('goodsSort', 'all'),
                    'showNum'   => (int)$this->request->param('showNum', 10),
                ],
            ],
        ];
        $data = \app\api\model\WxappPage::getGoodsList($item);
        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 文章组:按分类取列表（编辑器实时预览用）
     * @return \think\response\Json
     */
    public function article()
    {
        $item = [
            'type'   => 'article',
            'params' => [
                'source' => 'auto',
                'auto'   => [
                    'category' => (int)$this->request->param('category', 0),
                    'showNum'  => (int)$this->request->param('showNum', 10),
                ],
            ],
        ];
        $data = \app\api\model\WxappPage::getArticleList($item);
        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 分类模板
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function category()
    {
        $model = WxappCategoryModel::detail();
        if ($this->request->isAjax()) {
            if ($model->edit($this->postData('category'))) {
                return $this->renderSuccess('更新成功');
            }
            return $this->renderError($model->getError() ?: '更新失败');
        }
        return $this->fetch('category', compact('model'));
    }

    /**
     * 页面链接
     * @return mixed
     */
    public function links()
    {
        return $this->fetch('links');
    }

    /**
     * 图标管理
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function icon()
    {
        $model = WxappIconModel::detail();
        if ($this->request->isAjax()) {
            $data = $this->postData('icon');
            $iconModel = new WxappIconModel;
            if ($iconModel->edit($data)) {
                return $this->renderSuccess('更新成功');
            }
            return $this->renderError($iconModel->getError() ?: '更新失败');
        }
        return $this->fetch('icon', compact('model'));
    }

}
