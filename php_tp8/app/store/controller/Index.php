<?php
namespace app\store\controller;

use app\store\model\Setting;
use app\store\model\Store as StoreModel;

/**
 * 后台首页（移植自 TP5 store/controller/Index.php）
 */
class Index extends Controller
{
    /**
     * 后台首页：跳转到当前用户可访问的第一个菜单
     */
    public function index()
    {
        $menus = $this->menus();
        $first = current(array_values($menus));
        $url = $first['index'] ?? '';
        if (!empty($url) && $url !== 'index/index') {
            $this->redirect((string)url($url));
        }
        $model = new StoreModel($this->getWxappId());
        return $this->fetch('index', ['data' => $model->getHomeData()]);
    }

    /**
     * 新版首页（扁平化设计）
     */
    public function home()
    {
        $model = new StoreModel($this->getWxappId());
        return $this->fetch('home', [
            'data'    => $model->getHomeData(),
            'setting' => Setting::getAll() ?: null,
            'store'   => $this->store,
        ]);
    }
}
