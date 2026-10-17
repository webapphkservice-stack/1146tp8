<?php

namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Cache;

/**
 * 小程序模型
 *
 * 移植自 php/source/application/store/model/Wxapp.php，针对 php_tp8 的调整：
 *  1. 原实现继承 app\common\model\Wxapp（未迁移），改为直接操作 wxapp 表。
 *  2. 去掉 writeCertPemFiles()：原逻辑把微信支付 v2 证书写死到
 *     APP_PATH.'common/library/wechat/cert/{wxapp_id}/'。现支付已切到 v3，
 *     证书路径由 config/pay.php 的 PAY_WECHAT_V3_* 配置，不再需要落盘到固定目录。
 *     cert_pem / key_pem 仍会随表单写入 wxapp 表，仅作留存。
 *  3. allowField(true) 在 think-orm v4 会抛 TypeError，改为按表字段过滤。
 */
class Wxapp extends BaseModel
{
    protected $name = 'wxapp';

    /** wxapp 表可写字段（与 wxapp/setting 视图表单字段一致） */
    private const EDITABLE_FIELDS = [
        'app_id', 'app_secret', 'mchid', 'apikey', 'cert_pem', 'key_pem',
        'alipay_app_id', 'alipay_sign_type', 'alipay_private_key', 'alipay_public_key',
    ];

    /**
     * 当前小程序信息
     * @param int|null $wxappId 不传则取当前登录后台的 wxapp_id
     * @return static|null
     */
    public static function detail($wxappId = null)
    {
        $wxappId = $wxappId ?: self::getWxappId();
        return static::where('wxapp_id', $wxappId)->find();
    }

    /**
     * 更新小程序设置
     * @param array $data
     * @return bool
     */
    public function edit(array $data)
    {
        self::deleteCache();
        return $this->save(array_intersect_key($data, array_flip(self::EDITABLE_FIELDS))) !== false;
    }

    /**
     * 清除小程序缓存
     * @return bool
     */
    public static function deleteCache()
    {
        return Cache::delete('wxapp_' . self::getWxappId());
    }
}
