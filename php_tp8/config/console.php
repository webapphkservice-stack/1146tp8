<?php
// +----------------------------------------------------------------------
// | 控制台配置
// +----------------------------------------------------------------------
return [
    // 指令定义（TP8 从配置加载，不再使用 app/command.php）
    'commands' => [
        'app\task\command\Hello',
        // 订单定时维护：未支付自动关闭 / 已发货自动确认收货 / 已完成订单结算
        // 建议 crontab 每小时执行：php think order:cron
        'app\task\command\Order',
        // 优惠券过期：将已过期的用户优惠券置为已失效
        // 建议 crontab 每天执行：php think coupon:expire
        'app\task\command\UserCoupon',
        // 会员等级自动升级：按累计消费金额升级（对应 php task/behavior/user/Grade.php）
        // 建议 crontab 每小时执行：php think user:grade
        'app\task\command\UserGrade',
        // 分销订单维护：主订单已取消 -> 标记失效（佣金发放由 order:cron 的 settled 负责）
        // 建议 crontab 每天执行：php think dealer:settle
        'app\task\command\DealerOrder',
        // 拼团订单维护：未支付关闭 / 自动确认收货 / 已完成结算（sharing_order 表）
        // 建议 crontab 每小时：php think sharing:cron
        'app\task\command\SharingOrder',
        // 拼团活动维护：过期拼单置失败 + 自动退款（需开启 sharing_setting.basic.auto_refund）
        // 建议 crontab 每 10 分钟：php think sharing:active
        'app\task\command\SharingActive',
        // 砍价任务维护：到期任务置为已结束
        // 建议 crontab 每 10 分钟：php think bargain:task
        'app\task\command\BargainTask',
        // 秒杀订单维护：未支付按分钟关闭（sharp_setting.basic.order.order_close）
        // 建议 crontab 每 5 分钟：php think sharp:cron
        'app\task\command\SharpOrder',
        // 外送订单商户侧 webhook 回写队列消费（job 表 delivery_notify_job）
        // 建议 crontab 每分钟：php think delivery:notify
        'app\task\command\DeliveryNotify',
        // 区域代理分润结算（省/市/区三级，需 region_agent 表与 order 的 region_agent_* 字段）
        // 建议 crontab 每天：php think region_agent
        'app\task\command\RegionAgent',
    ],
];
