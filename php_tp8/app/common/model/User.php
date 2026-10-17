<?php

namespace app\common\model;

/**
 * 用户模型（TP8 基础表，移植自 php_my common/model/User，最小可用版）。
 *
 * 移植要点：
 * 1. think-orm v4 已移除 Model::get()，统一改用 find()（原版 detail() 用的是 get()）。
 * 2. 原版的 grade / address / points 等关联在 POC 阶段不引入，避免依赖未迁移模块；
 *    详情返回的 with 默认空数组，调用方按需自行补充。
 * 3. 全局作用域 wxappId 由 BaseModel 提供，detail() 无需再手写 wxapp_id 过滤。
 */
class User extends BaseModel
{
    protected $name = 'user';

    /**
     * 获取用户信息（等价于 TP5 的 detail）
     * @param mixed $where user_id 或 条件数组
     * @param array $with  关联（默认空，避免引入未迁移模块）
     * @return static|null
     */
    public static function detail($where, $with = [])
    {
        $filter = ['is_delete' => 0];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['user_id'] = (int)$where;
        }
        return static::where($filter)->with($with)->find();
    }

    /**
     * 头像地址（未设置返回空，由前端兜底默认头像）
     */
    public function getAvatarUrlAttr($value)
    {
        if (empty($value)) {
            return '';
        }
        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }
        return base_url() . $value;
    }

    /**
     * 注册方式（展示用）
     * 微信用户有 open_id；支付宝用户有 alipay_user_id；Google 用户有 google_id；
     * 手机号与 WhatsApp 用户均无上述第三方 ID，仅 mobile 不同：
     * 中国大陆手机号 ^1\d{10}$ 判定为「手机号码」，其余国际号码判定为「whatsapp」。
     */
    public function getRegisterMethodAttr($value, $data)
    {
        if (!empty($data['open_id'])) {
            return '微信授权';
        }
        if (!empty($data['alipay_user_id'])) {
            return '支付宝';
        }
        if (!empty($data['google_id'])) {
            return 'Google';
        }
        if (!empty($data['mobile'])) {
            return preg_match('/^1\d{10}$/', $data['mobile']) ? '手机号码' : 'whatsapp';
        }
        return '未知';
    }
}
