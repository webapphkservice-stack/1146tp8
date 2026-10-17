<?php

namespace app\store\controller;

use app\store\model\Setting as SettingModel;
use think\facade\Cache;

/**
 * Stripe 支付配置
 * 菜单入口：stripe/setting
 *
 * 与 TP5 实现的差异：
 *  - 原实现直接操作 app\common\model\Setting 并手工处理「无自增主键时的更新条件」。
 *    这里统一走 store Setting 模型的 getItem/edit（内部已按 wxapp_id 正确 upsert）。
 *  - 缓存清理改用 think\facade\Cache（TP8 门面），避免 TP5 的 think\Cache 不可用。
 */
class Stripe extends Controller
{
    public function setting()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('setting', [
                'values' => $this->settingValues(),
            ]);
        }
        $model = new SettingModel;
        if ($model->edit('stripe', $this->postData('stripe'))) {
            // 清缓存，否则刷新后仍读到旧值
            Cache::delete('setting_' . self::getWxappId());
            return $this->renderSuccess('保存成功');
        }
        return $this->renderError($model->getError() ?: '保存失败');
    }

    /**
     * 读取配置并与默认值合并（避免视图未定义索引）
     * @return array
     */
    private function settingValues()
    {
        $defaults = [
            'enable'          => 0,
            'secret_key'      => '',
            'publishable_key' => '',
            'webhook_secret'  => '',
            // 结算币种默认 MYR，与 config/pay.php 的 stripe.currency 一致
            'currency'        => 'myr',
        ];
        $values = SettingModel::getItem('stripe');
        return array_replace_recursive($defaults, is_array($values) ? $values : []);
    }
}
