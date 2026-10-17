<?php
namespace app\api\model;

use app\common\model\BaseModel;

/**
 * 代币充值订单模型（微信虚拟支付 currency 模式）
 * 移植自 TP5：application/api/model/CoinOrder.php
 */
class CoinOrder extends BaseModel
{
    protected $name = 'coin_order';
}
