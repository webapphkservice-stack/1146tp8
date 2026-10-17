<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use app\api\service\sharing\Order as SharingOrderService;
use app\common\library\pay\PayConfig;

/**
 * 拼团订单定时维护
 *
 * 对应 php task/behavior/sharing/Order.php。
 * 注意：拼团订单独立落 sharing_order 表（不复用 order 表），
 * 因此 app\api\service\OrderStateMachine 不覆盖本场景，命令内直接操作 sharing_order。
 *
 * 三个子任务：
 *   1. close    未支付拼团订单自动关闭（close_days 天前）-> 复用 SharingOrder::cancel()（回退库存/券/积分）
 *   2. receive  已发货自动确认收货（receive_days 天前）-> 复用 SharingOrder::receipt()
 *   3. settled  已完成订单标记结算（refund_days 天后）-> is_settled=1
 *
 * 天数：与 order:cron 一致，读 setting 表 trade.order.{close_days,receive_days,refund_days}
 *
 * 用法：
 *   php think sharing:cron [--wxapp=10001] [--dry-run]
 * 建议 crontab（每小时）：
 *   5 * * * * cd /path/to/php_tp8 && php think sharing:cron >> /dev/null 2>&1
 */
class SharingOrder extends Command
{
    /** @var Output 不可命名为 $output，父类 Command 已声明 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('sharing:cron')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('拼团订单定时维护：未支付关闭 / 自动确认收货 / 已完成结算');
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
            $id    = (int)$wxapp['wxapp_id'];
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
        $output->writeln('<info>sharing:cron 执行完毕</info>');
    }

    /**
     * 未支付订单自动关闭
     */
    private function close($wxappId, $days)
    {
        $deadline = time() - ($days * 86400);
        $list = Db::name('sharing_order')
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
                $service = new SharingOrderService;
                if ($service->cancel($order, $wxappId)) {
                    $count++;
                } else {
                    log_write('拼团订单关闭失败[' . $order['order_no'] . ']：' . SharingOrderService::$lastError);
                }
            } catch (\Throwable $e) {
                log_write('拼团订单关闭异常[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('  close: ' . $count . ' 单已关闭');
    }

    /**
     * 已发货订单自动确认收货
     */
    private function receive($wxappId, $days)
    {
        $deadline = time() - ($days * 86400);
        $list = Db::name('sharing_order')
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
            $this->out->writeln('  receive: ' . count($list) . ' 单（待收货）');
            return;
        }

        $count = 0;
        foreach ($list as $order) {
            try {
                $service = new SharingOrderService;
                if ($service->receipt($order)) {
                    $count++;
                } else {
                    log_write('拼团订单收货失败[' . $order['order_no'] . ']：' . SharingOrderService::$lastError);
                }
            } catch (\Throwable $e) {
                log_write('拼团订单收货异常[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('  receive: ' . $count . ' 单已确认收货');
    }

    /**
     * 已完成订单标记结算
     */
    private function settled($wxappId, $days)
    {
        $deadline = time() - ($days * 86400);
        $list = Db::name('sharing_order')
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
        foreach ($list as $order) {
            try {
                Db::name('sharing_order')->where('order_id', $order['order_id'])->update([
                    'is_settled'  => 1,
                    'update_time' => time(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                log_write('拼团订单结算失败[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('  settled: ' . $count . ' 单已结算');
    }
}
