<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use app\api\service\OrderStateMachine as StateMachine;
use app\common\library\pay\PayConfig;

/**
 * 订单定时维护（P0：补 task 应用，解决「订单无人推进到完成态」的阻塞）
 *
 * 用法：
 *   php think order:cron                 # 处理全部小程序
 *   php think order:cron --wxapp=10001   # 只处理指定小程序
 *   php think order:cron --dry-run       # 只统计不落库（用于验证）
 *
 * 建议 crontab（每小时执行一次）：
 *   0 * * * * cd /path/to/php_tp8 && php think order:cron >> /dev/null 2>&1
 *
 * 三个子任务（行为对齐 php_my task/behavior/Order.php）：
 *   1. close    未支付订单自动关闭（close_days 天前）-> 回退库存/销量/优惠券
 *   2. receive  已发货订单自动确认收货（receive_days 天前）-> 订单完成
 *   3. settled  已完成订单结算（refund_days 天后）-> 发放分销佣金
 *
 * 天数配置读取优先级：setting 表 trade.order.{close_days,receive_days,refund_days}
 * 默认值与 php_my 一致：close=3 / receive=10 / refund=7，配置为 0 表示不启用该项。
 */
class Order extends Command
{
    /** @var Output 注意：不可命名为 $output，父类 Command 已声明该属性 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('order:cron')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('订单定时维护：未支付自动关闭 / 已发货自动确认收货 / 已完成订单结算');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->out = $output;
        $this->dryRun = (bool)$input->getOption('dry-run');

        $wxappId = (int)$input->getOption('wxapp');
        $wxappList = $wxappId
            ? Db::name('wxapp')->where('wxapp_id', $wxappId)->select()->toArray()
            : Db::name('wxapp')->where('is_recycle', 0)->select()->toArray();

        if (empty($wxappList)) {
            $output->writeln('<error>未找到可处理的小程序</error>');
            return;
        }
        if ($this->dryRun) {
            $output->writeln('<comment>[dry-run] 只统计不落库</comment>');
        }

        foreach ($wxappList as $wxapp) {
            $id = (int)$wxapp['wxapp_id'];
            $trade = PayConfig::trade($id);
            $output->writeln(sprintf(
                '<info>处理 wxapp_id=%d（close=%d天 receive=%d天 refund=%d天）</info>',
                $id, $trade['close_days'], $trade['receive_days'], $trade['refund_days']
            ));
            if ($trade['close_days'] > 0) {
                $this->close($id, $trade['close_days']);
            }
            if ($trade['receive_days'] > 0) {
                $this->receive($id, $trade['receive_days']);
            }
            if ($trade['refund_days'] > 0) {
                $this->settled($id, $trade['refund_days']);
            }
        }
        $output->writeln('<info>order:cron 执行完毕</info>');
    }

    // -------------------- 1. 未支付订单自动关闭 --------------------

    /**
     * 未支付订单自动关闭
     * @param int $wxappId
     * @param int $closeDays
     */
    private function close($wxappId, $closeDays)
    {
        $deadline = time() - ($closeDays * 86400);
        $list = Db::name('order')
            ->where('wxapp_id', $wxappId)
            ->where('pay_status', 10)
            ->where('order_status', 10)
            ->where('is_delete', 0)
            ->where('create_time', '<=', $deadline)
            ->select()->toArray();

        if (empty($list)) {
            $this->out->writeln('  close: 0 单');
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln('  close: ' . count($list) . ' 单（待关闭）');
            return;
        }

        $count = 0;
        foreach ($list as $order) {
            try {
                Db::transaction(function () use ($order) {
                    // 回退库存与销量
                    $goodsList = Db::name('order_goods')->where('order_id', $order['order_id'])->select()->toArray();
                    foreach ($goodsList as $g) {
                        Db::name('goods_sku')->where('goods_sku_id', $g['goods_sku_id'])
                            ->inc('stock_num', $g['total_num'])->update();
                        // sales_actual 为 unsigned，用 GREATEST 兜底，避免销量为 0 时自减下溢
                        Db::name('goods')->where('goods_id', $g['goods_id'])
                            ->update(['sales_actual' => StateMachine::decFloorRaw('sales_actual', (int)$g['total_num'])]);
                    }
                    // 退还优惠券与积分（下单时已核销/扣除）
                    // 注：早期版本此处误用 where('id', ...) 查询 user_coupon 表，
                    // 而该表主键为 user_coupon_id，导致优惠券回退静默失败（更新 0 行）。
                    // 现统一走状态机的 rollbackAssets()，避免主键不一致再次踩坑。
                    StateMachine::rollbackAssets($order, '订单超时关闭退还');
                    Db::name('order')->where('order_id', $order['order_id'])->update([
                        'order_status' => 20,
                        'update_time'  => time(),
                    ]);
                });
                $count++;
            } catch (\Throwable $e) {
                log_write('自动关闭订单失败[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('  close: ' . $count . ' 单已关闭');
    }

    // -------------------- 2. 已发货订单自动确认收货 --------------------

    /**
     * 已发货订单自动确认收货
     * @param int $wxappId
     * @param int $receiveDays
     */
    private function receive($wxappId, $receiveDays)
    {
        $deadline = time() - ($receiveDays * 86400);
        $list = Db::name('order')
            ->where('wxapp_id', $wxappId)
            ->where('pay_status', 20)
            ->where('delivery_status', 20)
            ->where('receipt_status', 10)
            ->where('order_status', 10)
            ->where('is_delete', 0)
            ->where('delivery_time', '<=', $deadline)
            ->select()->toArray();

        if (empty($list)) {
            $this->out->writeln('  receive: 0 单');
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln('  receive: ' . count($list) . ' 单（待确认收货）');
            return;
        }

        $count = 0;
        foreach ($list as $order) {
            try {
                // 统一走状态机：原先此处直接 UPDATE 绕过了 OrderStateMachine，
                // 导致「自动收货」的订单不会发放购物赠送积分，与用户手动确认收货行为不一致。
                $machine = new StateMachine();
                if ($machine->receipt($order['order_id'], $order['user_id'])) {
                    $count++;
                } else {
                    log_write('自动确认收货失败[' . $order['order_no'] . ']：' . $machine->getError());
                }
            } catch (\Throwable $e) {
                log_write('自动确认收货失败[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('  receive: ' . $count . ' 单已确认收货');
    }

    // -------------------- 3. 已完成订单结算（发放分销佣金） --------------------

    /**
     * 已完成订单结算：标记 is_settled 并发放分销佣金
     * @param int $wxappId
     * @param int $refundDays 售后期（超过该期限才结算，避免退款后仍发放佣金）
     */
    private function settled($wxappId, $refundDays)
    {
        $deadline = time() - ($refundDays * 86400);
        $list = Db::name('order')
            ->where('wxapp_id', $wxappId)
            ->where('order_status', 30)
            ->where('is_settled', 0)
            ->where('is_delete', 0)
            ->where('receipt_time', '<=', $deadline)
            ->select()->toArray();

        if (empty($list)) {
            $this->out->writeln('  settled: 0 单');
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln('  settled: ' . count($list) . ' 单（待结算）');
            return;
        }

        $count = 0;
        $money = 0;
        foreach ($list as $order) {
            try {
                $granted = Db::transaction(function () use ($order) {
                    $granted = $this->grantDealerMoney($order);
                    Db::name('order')->where('order_id', $order['order_id'])->update([
                        'is_settled'  => 1,
                        'update_time' => time(),
                    ]);
                    return $granted;
                });
                $count++;
                $money += $granted;
            } catch (\Throwable $e) {
                log_write('订单结算失败[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln(sprintf('  settled: %d 单已结算，发放佣金 %.2f 元', $count, $money));
    }

    /**
     * 发放分销佣金（一/二/三级）
     *
     * 说明：直接使用 dealer_order 表中下单时已算好的 first_money / second_money / third_money，
     * 与用户在「我的分销订单」页面看到的金额一致。旧版 php_my 在结算时会按分销商等级
     * 重新计算佣金（getCapitalByOrder），该逻辑依赖 dealer_setting 的等级费率体系，
     * 属 P2 增强项，本次未迁移——如需按等级重算，在调用 grantMoney 前替换金额来源即可。
     *
     * @param array $order 订单行
     * @return float 本次发放的佣金总额
     */
    private function grantDealerMoney(array $order)
    {
        $dealerOrder = Db::name('dealer_order')
            ->where('order_id', $order['order_id'])
            ->where('order_type', 10)
            ->where('wxapp_id', $order['wxapp_id'])
            ->find();
        if (empty($dealerOrder)) {
            return 0.00;
        }
        // 已结算 / 已失效 不重复发放
        if (!empty($dealerOrder['is_settled']) || !empty($dealerOrder['is_invalid'])) {
            return 0.00;
        }

        $total = 0.00;
        $levels = [
            'first_user_id'  => 'first_money',
            'second_user_id' => 'second_money',
            'third_user_id'  => 'third_money',
        ];
        $now = time();
        foreach ($levels as $userField => $moneyField) {
            $userId = (int)$dealerOrder[$userField];
            $amount = (float)$dealerOrder[$moneyField];
            if ($userId <= 0 || $amount <= 0) {
                continue;
            }
            // 佣金入账（可提现余额）
            Db::name('dealer_user')->where('user_id', $userId)->inc('money', $amount)->update();
            // 资金流水（佣金收入 flow_type=10）
            Db::name('dealer_capital')->insert([
                'user_id'     => $userId,
                'flow_type'   => 10,
                'money'       => $amount,
                'describe'    => '订单佣金：' . $order['order_no'],
                'wxapp_id'    => $order['wxapp_id'],
                'create_time' => $now,
                'update_time' => $now,
            ]);
            $total += $amount;
        }

        Db::name('dealer_order')->where('id', $dealerOrder['id'])->update([
            'is_settled'  => 1,
            'settle_time' => $now,
            'update_time' => $now,
        ]);
        return $total;
    }
}
