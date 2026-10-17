<?php

namespace app\api\controller\wxapp;

use app\api\controller\Controller;

/**
 * form_id 管理（已废弃：微信模板消息下线，仅保留空实现兼容旧调用）
 * Class Formid
 * @package app\api\controller\wxapp
 */
class Formid extends Controller
{
    public function save()
    {
        return $this->renderSuccess();
    }
}
