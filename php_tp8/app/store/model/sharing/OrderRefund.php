<?php

namespace app\store\model\sharing;

use app\common\model\BaseModel;

/**
 * 拼团售后单模型(后台，自包含，退款操作已简化为状态更新)
 * Class OrderRefund
 * @package app\store\model\sharing
 */
class OrderRefund extends BaseModel
{
    protected $name = 'sharing_order_refund';

    /**
     * 关联订单
     * @return \think\model\relation\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('app\store\model\sharing\Order', 'order_id', 'order_id');
    }

    /**
     * 关联订单商品
     * @return \think\model\relation\BelongsTo
     */
    public function orderGoods()
    {
        return $this->belongsTo('app\store\model\sharing\OrderGoods', 'order_goods_id', 'order_goods_id');
    }

    /**
     * 关联退货地址
     * @return \think\model\relation\BelongsTo
     */
    public function address()
    {
        return $this->belongsTo('app\store\model\sharing\OrderRefundAddress', 'order_refund_id', 'order_refund_id');
    }

    /**
     * 详情
     * @param $id
     * @return null|\think\Model
     */
    public static function detail($id)
    {
        return static::with(['order', 'orderGoods', 'address'])->find($id);
    }

    /**
     * 获取售后列表
     * @param $data
     * @return \think\Paginator
     */
    public function getList($data)
    {
        $model = $this;
        if (isset($data['type']) && $data['type'] > 0) {
            $model->where('type', '=', $data['type']);
        }
        if (isset($data['is_agree']) && $data['is_agree'] > -1) {
            $model->where('is_agree', '=', $data['is_agree']);
        }
        if (isset($data['status']) && $data['status'] > -1) {
            $model->where('status', '=', $data['status']);
        }
        if (!empty($data['search'])) {
            $model->where('order_goods_id', 'in', function ($query) use ($data) {
                $query->name('sharing_order_goods')
                    ->where('goods_name', 'like', "%{$data['search']}%")
                    ->field('order_goods_id');
            });
        }
        return $model->with(['order', 'orderGoods'])
            ->order(['create_time' => 'desc'])
            ->paginate(10, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 商家审核
     * @param $data
     * @return bool
     */
    public function audit($data)
    {
        if ($data['is_agree'] == 20 && empty($data['refund_money'])) {
            $this->error = '请填写退款金额';
            return false;
        }
        if ($this['is_agree'] != 0) {
            $this->error = '当前售后单不可审核';
            return false;
        }
        $this->startTrans();
        try {
            // 简化：仅更新审核状态，真实退款需接入支付渠道
            $this->save([
                'is_agree' => $data['is_agree'],
                'refund_money' => $data['refund_money'] ?? $this['refund_money'],
            ]);
            $this->order->orderGoods->backGoodsStock(
                $this['goods_id'], $this['goods_sku_id'], $this['goods_num']
            );
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 确认收货并退款
     * @param $data
     * @return bool
     */
    public function receipt($data)
    {
        $this->startTrans();
        try {
            if ($this['type'] == 10) {
                $this->save(['is_receipt' => 1, 'status' => 20, 'receipt_time' => time()]);
            } else {
                $this->save(['status' => 20, 'receipt_time' => time()]);
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

}
