<?php

namespace app\api\controller\wxapp;

use app\api\controller\Controller;
use app\common\model\Setting as SettingModel;

/**
 * 微信小程序订阅消息（移植自 php api/controller/wxapp/Submsg）
 * Class Submsg
 * @package app\api\controller\wxapp
 *
 * 小程序调用点：wxapp.submsg/setting
 */
class Submsg extends Controller
{
    /**
     * 获取订阅消息配置
     */
    public function setting()
    {
        $setting = SettingModel::getItem('submsg');
        return $this->renderSuccess(compact('setting'));
    }
}
