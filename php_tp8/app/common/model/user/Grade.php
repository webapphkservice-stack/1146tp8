<?php
namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户会员等级模型（B3 会员等级折扣 - 核心）
 *
 * 数据层：yoshop_user_grade
 *  - equity（等级权益）：JSON，含折扣率 discount（0-10，9.5 表示 9.5 折）
 *  - upgrade（升级条件）：JSON（如消费金额阈值）
 *
 * JSON 字段通过 think-orm v4 获取器（getXxxAttr）自动解码，避免手动遍历赋值触发 Attribute 冲突。
 *
 * 计算语义（详见 app/api/service/order/Checkout::setOrderGoodsGradeMoney）：
 *  discountRatio = (商品单独折扣 alone_grade_equity[grade_id] 或 等级默认折扣 equity.discount) / 10
 *  会员价 = 原价 × discountRatio
 */
class Grade extends BaseModel
{
    protected $name = 'user_grade';

    /**
     * 升级条件 JSON 自动解码（获取器）
     */
    public function getUpgradeAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 等级权益（折扣率）JSON 自动解码（获取器）
     */
    public function getEquityAttr($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * 可用的会员等级列表（启用且未删除，按权重升序）
     */
    public static function getUsableList($wxappId = null, $order = ['weight' => 'asc'])
    {
        $wxappId = $wxappId ?: static::$wxapp_id;
        return (new static)
            ->where('status', '=', 1)
            ->where('is_delete', '=', 0)
            ->where('wxapp_id', '=', $wxappId)
            ->order($order)
            ->select();
    }

    /**
     * 等级详情（TP8 用 find 替代 TP5 的 get；获取器自动解码 JSON 字段）
     */
    public static function detail($gradeId, $with = [])
    {
        return static::find($gradeId);
    }

    /**
     * 取等级折扣率（0-10，9.5=9.5折）；无等级/无折扣返回 0
     * 注意：value() 返回标量不走获取器，需手动 json_decode。
     */
    public static function getDiscountRatio($gradeId)
    {
        if (empty($gradeId)) {
            return 0;
        }
        $equity = static::where('grade_id', $gradeId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->value('equity');
        if (!$equity) {
            return 0;
        }
        $equity = json_decode($equity, true);
        $discount = isset($equity['discount']) ? (float)$equity['discount'] : 0;
        return $discount > 0 ? $discount : 0;
    }

    /**
     * 验证等级权重是否已存在
     */
    public static function checkExistByWeight($weight, $gradeId = 0)
    {
        $query = static::where('weight', '=', (int)$weight)
            ->where('is_delete', '=', 0);
        $gradeId > 0 && $query->where('grade_id', '<>', (int)$gradeId);
        return $query->value('grade_id');
    }

    /**
     * 取下一级（权重比当前大的最小权重等级），用于升级进度展示
     */
    public static function getNextGrade($gradeId, $wxappId = null)
    {
        $wxappId = $wxappId ?: static::$wxapp_id;
        $current = static::where('grade_id', $gradeId)
            ->where('is_delete', 0)
            ->value('weight');
        if ($current === null) {
            return null;
        }
        return (new static)
            ->where('weight', '>', $current)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->where('wxapp_id', $wxappId)
            ->order(['weight' => 'asc'])
            ->find();
    }
}
