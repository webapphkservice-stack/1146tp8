<?php

namespace app\api\controller\sharing;

use app\api\controller\Controller;
use app\common\model\Setting as SettingModel;

/**
 * 拼团设置（移植自 php api/controller/sharing/Setting，Db 轻量版）
 * Class Setting
 * @package app\api\controller\sharing
 *
 * 小程序调用点：sharing.setting/getAll
 */
class Setting extends Controller
{
    /**
     * 获取拼团设置（基础设置 + 订单订阅消息模板）
     */
    public function getAll()
    {
        $basic = SettingModel::getItem('sharing');
        $submsg = SettingModel::getItem('submsg');
        $orderSubmsg = [];
        if (!empty($submsg['sharing']) && is_array($submsg['sharing'])) {
            foreach ($submsg['sharing'] as $key => $item) {
                $orderSubmsg[$key] = $item['template_id'] ?? '';
            }
        }
        return $this->renderSuccess([
            'setting' => [
                'basic' => $basic ?: [],
                'order_submsg' => $orderSubmsg,
            ],
        ]);
    }
}
