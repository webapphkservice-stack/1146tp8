<?php
namespace app\h5\controller;

use app\api\model\WxappPage as WxappPageModel;
use app\common\library\LinkUrlTransformer;

/**
 * 手机网站 - 页面装修控制器。
 * 复用 API 层 WxappPageModel::getPageData() 取 DIY 装修数据（动态组件已展开），
 * 再由 PHP 模板 + Vue3 渲染成 H5 页面。
 */
class Page extends Controller
{
    /**
     * 首页（默认首页 page_type=10）
     */
    public function index()
    {
        $pageId = (int)$this->request->param('page_id', 0);
        $data = WxappPageModel::getPageData(false, $pageId);
        // H5 端：把 pageData 里所有 pages/* 小程序路径自动转换为 /h5/*.html
        // 这样后台只存一份小程序路径，H5 渲染时自动适配。
        $data = LinkUrlTransformer::transformPageData($data, 'h5');
        return $this->fetch('page/index', [
            'pageData'  => $data,
            'storeName' => $this->storeName(),
        ]);
    }
}