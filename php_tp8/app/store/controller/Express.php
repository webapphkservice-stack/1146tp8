<?php
namespace app\store\controller;

use think\facade\Db;

/**
 * 物流公司（P0：补 store 应用，供发货时选择快递公司）
 * 路由：GET /store/express/lists
 */
class Express extends Api
{
    /**
     * 物流公司列表
     * @return \think\response\Json
     */
    public function lists()
    {
        $list = Db::name('express')
            ->where('wxapp_id', $this->getWxappId())
            ->field('express_id, express_name, express_code, sort')
            ->order(['sort' => 'asc', 'express_id' => 'asc'])
            ->select();
        return $this->renderSuccess(['list' => $list]);
    }
}
