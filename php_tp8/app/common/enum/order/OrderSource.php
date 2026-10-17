<?php

namespace app\common\enum\order;

/**
 * 订单来源（对齐 php_my common/enum/order/OrderSource）
 *  - MASTER 普通订单
 *  - BARGAIN 砍价订单
 *  - SHARP  秒杀订单
 */
class OrderSource
{
    const MASTER = 10;
    const BARGAIN = 20;
    const SHARP = 30;
}
