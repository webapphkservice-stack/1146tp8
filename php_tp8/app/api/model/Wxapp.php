<?php
namespace app\api\model;

use app\common\model\Wxapp as WxappModel;

/**
 * API 应用微信小程序模型
 */
class Wxapp extends WxappModel
{
    /**
     * 隐藏字段（响应中不返回敏感信息）
     * @var array
     */
    protected $hidden = [
        'app_secret',
        'alipay_private_key',
        'alipay_public_key',
    ];
}
