<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团订单模型(后台，自包含，订单/退款操作已简化为状态更新)
 * Class Order
 * @package app\store\model\sharing
 */
class Order extends BaseModel
{
    protected $name = 'sharing_order';

    /**
     * 关联拼团活动
     * @return \think\model\relation\BelongsTo
     */
    public function active()
    {
        return $this->belongsTo('app\store\model\sharing\Active', 'active_id', 'active_id');
    }

    /**
     * 关联订单商品
     * @return \think\model\relation\HasMany
     */
    public function goods()
    {
        return $this->hasMany('app\store\model\sharing\OrderGoods', 'order_id', 'order_id');
    }

    /**
     * 关联用户
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('app\store\model\User', 'user_id', 'user_id');
    }

    /**
     * 详情
     * @param $orderId
     * @param array $with
     * @return null|\think\Model
     */
    public static function detail($orderId, $with = [])
    {
        return static::with($with)->find($orderId);
    }

    /**
     * 订单列表
     * @param $dataType
     * @param array $query
     * @return \think\Paginator
     */
    public function getList($dataType, $query = [])
    {
        !empty($query) && $this->setWhere($query);
        // join user 后两表都有 wxapp_id，关闭全局作用域并限定 order 前缀
        return $this->withoutGlobalScope(['wxappId'])
            ->with(['active', 'goods', 'user'])
            ->alias('order')
            ->join('user', 'user.user_id = order.user_id', 'LEFT')
            ->where('order.wxapp_id', self::getWxappId())
            ->where($this->transferDataType($dataType))
            ->where('order.is_delete', '=', 0)
            ->field('order.*')
            ->order(['order.create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 检索条件
     * @param $query
     */
    private function setWhere($query)
    {
        if (!empty($query['order_no'])) {
            $this->where('order_no', 'like', "%{$query['order_no']}%");
        }
        if (!empty($query['start_time'])) {
            $this->where('order.create_time', '>=', strtotime($query['start_time']));
        }
        if (!empty($query['end_time'])) {
            $this->where('order.create_time', '<=', strtotime($query['end_time']) + 86400);
        }
    }

    /**
     * 数据类型筛选
     * @param $dataType
     * @return array
     */
    private function transferDataType($dataType)
    {
        switch ($dataType) {
            case 'payment':
                return [['pay_status', '=', 20], ['order_status', '=', 10]];
            case 'delivery':
                return [['pay_status', '=', 20], ['delivery_status', '=', 10], ['order_status', '=', 10]];
            case 'received':
                return [['pay_status', '=', 20], ['delivery_status', '=', 20], ['order_status', '=', 10]];
            case 'refund':
                return [['order_status', '=', 20]];
            case 'reach':
                return [['pay_status', '=', 20], ['order_status', '=', 30]];
            default:
                return [];
        }
    }

    /**
     * 导出订单(简化：返回空，导出 CSV 依赖额外库，避免致命错误)
     * @param $dataType
     * @param $query
     * @return string
     */
    public function exportList($dataType, $query)
    {
        return '';
    }

    /**
     * 批量发货
     * @param $data
     * @return bool
     */
    public function batchDelivery($data)
    {
        if (empty($data['order_ids'])) {
            $this->error = '请选择订单';
            return false;
        }
        $orderIds = explode(',', $data['order_ids']);
        $updateData = [
            'express_id' => $data['express_id'],
            'express_no' => $data['express_no'],
            'delivery_status' => 20,
            'delivery_time' => time(),
        ];
        $this->where('order_id', 'in', $orderIds)
            ->where('pay_status', '=', 20)
            ->where(function ($query) {
                $query->where('order_status', '=', 10)->whereOr('order_status', '=', 11);
            })
            ->update($updateData);
        return true;
    }

    /**
     * 审核用户取消订单
     * @param $data
     * @return bool
     */
    public function confirmCancel($data)
    {
        if (empty($data['order_id'])) {
            $this->error = '参数错误';
            return false;
        }
        if ($this['order_status'] != 21) {
            $this->error = '当前订单不可审核';
            return false;
        }
        // 简化：直接取消并标记退款(真实退款需接入支付渠道)
        $this->save(['order_status' => 20, 'pay_status' => 10]);
        return true;
    }

    /**
     * 拼团失败手动退款(简化：仅更新状态)
     * @return bool
     */
    public function refund()
    {
        $this->save(['order_status' => 20, 'pay_status' => 10]);
        return true;
    }

    /**
     * 门店自提核销
     * @param $extractClerkId
     * @return bool
     */
    public function verificationOrder($extractClerkId)
    {
        if (!$this->verifyStatus()) {
            return false;
        }
        $this->save([
            'order_status' => 30,
            'delivery_status' => 20,
            'receipt_status' => 20,
            'receipt_time' => time(),
            'extract_clerk_id' => $extractClerkId,
        ]);
        return true;
    }

    /**
     * 核销状态校验
     * @return bool
     */
    private function verifyStatus()
    {
        if ($this['pay_status'] != 20 || $this['delivery_type'] != 20 || $this['order_status'] != 10) {
            $this->error = '当前订单不满足核销条件';
            return false;
        }
        return true;
    }

}
