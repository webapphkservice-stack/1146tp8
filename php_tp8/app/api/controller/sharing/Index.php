<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 拼团首页（分类）
 */
class Index extends Controller
{
    public function index()
    {
        $list = Db::name('sharing_category')
            ->where('wxapp_id', $this->getWxappId())
            ->order('sort', 'asc')
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
        $categoryList = array_map(function ($c) {
            return [
                'category_id' => $c['category_id'],
                'name'        => $c['name'],
                'parent_id'   => $c['parent_id'],
                'image_id'    => $c['image_id'],
                'image'       => BaseModel::getFileUrl($c['image_id']),
                'sort'        => $c['sort'],
                'create_time' => $c['create_time'],
            ];
        }, $list);
        return $this->renderSuccess(compact('categoryList'));
    }
}
