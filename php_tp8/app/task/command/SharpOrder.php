<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use app\api\service\OrderStateMachine as StateMachine;
use app\common\enum\order\OrderSource;

/**
 * 秒杀订单定时维护：未支付订单按「分钟」自动关闭
 *
 * 对应 php task/behavior/sharp/Order.php。
 * 秒杀订单复用主表 order（order_source = 30 = SHARP），关闭走
 * OrderStateMachine::cancel()（内部对 SHARP 会回退秒杀库存 seckill_stock）。
 *
 * 与普通订单的差异：普通订单按「天」关闭（order:cron 的 close_days），
 * 秒杀按「分钟」关闭（sharp_setting 的 basic.order.order_close），
 * 因为秒杀场次很短，按天关会占用库存太久。
 *
 * 与 order:cron 的关系：本命令先按分钟关闭；已关闭订单 order_status=20，
 * 不再满足 order:cron 的 close 条件（order_status=10），因此不会重复回退。
 *
 * 用法：
 *   php think sharp:cron [--wxapp=10001] [--dry-run]
 * 建议 crontab：每 5 分钟执行一次（秒杀关闭时限是分钟级的）
 */
class SharpOrder extends Command
{
    /** @var Output 不可命名为 $output，父类 Command 已声明 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('sharp:cron')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('秒杀订单定时维护：未支付订单按分钟自动关闭');
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
            $minute = $this->getCloseMinute($id);
            if ($minute < 1) {
                $output->writeln(sprintf('<comment>  wxapp_id=%d: 未配置秒杀自动关闭时间，跳过</comment>', $id));
                continue;
            }
            $this->close($id, $minute);
        }
        $output->writeln('<info>sharp:cron 执行完毕</info>');
    }

    /**
     * 关闭超时的未支付秒杀订单
     * @param int $wxappId
     * @param int $minute 关闭时限（分钟）
     */
    private function close($wxappId, $minute)
    {
        $deadline = time() - ($minute * 60);
        $list = Db::name('order')
            ->where('wxapp_id', $wxappId)
            ->where('order_source', OrderSource::SHARP)
            ->where('pay_status', 10)
            ->where('order_status', 10)
            ->where('is_delete', 0)
            ->where('create_time', '<=', $deadline)
            ->select()->toArray();

        if (empty($list)) {
            $this->out->writeln(sprintf('  wxapp_id=%d: 0 单（%d 分钟内未支付）', $wxappId, $minute));
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 单（待关闭）', $wxappId, count($list)));
            return;
        }

        $count = 0;
        foreach ($list as $order) {
            try {
                $machine = new StateMachine;
                if ($machine->cancel($order['order_id'], $order['user_id'])) {
                    $count++;
                } else {
                    log_write('秒杀订单关闭失败[' . $order['order_no'] . ']：' . $machine->getError());
                }
            } catch (\Throwable $e) {
                log_write('秒杀订单关闭异常[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln(sprintf('  wxapp_id=%d: %d 单已关闭', $wxappId, $count));
    }

    /**
     * 读取秒杀「未支付自动关闭」分钟数（sharp_setting.basic.order.order_close）
     * @param int $wxappId
     * @return int <1 表示不启用
     */
    private function getCloseMinute($wxappId)
    {
        try {
            $values = Db::name('sharp_setting')
                ->where('key', 'basic')
                ->where('wxapp_id', $wxappId)
                ->value('values');
            $basic = $values ? json_decode($values, true) : [];
            return (int)($basic['order']['order_close'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
