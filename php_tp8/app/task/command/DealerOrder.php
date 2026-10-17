<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;

/**
 * 分销订单定时维护：把「主订单已取消」的分销订单标记为失效
 *
 * 对应 php task/behavior/DealerOrder.php 的 setInvalid() 部分。
 *
 * 说明（与 TP5 的职责划分差异）：
 *   - TP5 的 behavior/DealerOrder 一个任务里同时做「发放佣金」与「标记失效」
 *   - TP8 的佣金发放已由 order:cron 的 settled 子任务完成
 *     （app/task/command/Order.php::grantDealerMoney：入账 dealer_user.money
 *      + 写 dealer_capital 流水 + 标记 dealer_order.is_settled）
 *   - 因此本命令只负责 TP5 遗漏的另一半：已取消订单 -> dealer_order.is_invalid=1，
 *     避免已取消订单的分销记录一直挂着「未结算」、污染分销商订单列表。
 *
 * 用法：
 *   php think dealer:settle
 *   php think dealer:settle --wxapp=10001
 *   php think dealer:settle --dry-run
 *
 * 建议 crontab（每天执行，放在 order:cron 之后）：
 *   20 1 * * * cd /path/to/php_tp8 && php think dealer:settle >> /dev/null 2>&1
 */
class DealerOrder extends Command
{
    /** @var Output 注意：不可命名为 $output，父类 Command 已声明该属性 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    /** @var int 订单状态：已取消 */
    const ORDER_CANCELLED = 20;

    protected function configure()
    {
        $this->setName('dealer:settle')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('分销订单定时维护：把主订单已取消的分销订单标记为失效');
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
            $count = 0;
            // 主订单（order_type=10 -> order 表）
            $count += $this->setInvalid($id, 10, 'order');
            // 拼团订单（order_type=20 -> sharing_order 表）
            $count += $this->setInvalid($id, 20, 'sharing_order');
            $output->writeln(sprintf(
                '  wxapp_id=%d: %d 条%s',
                $id,
                $count,
                $this->dryRun ? '（待标记失效）' : '已标记失效'
            ));
        }
        $output->writeln('<info>dealer:settle 执行完毕</info>');
    }

    /**
     * 找出「主订单已取消」且未结算、未失效的分销订单，标记为失效
     * @param int $wxappId
     * @param int $orderType 10=主订单 20=拼团订单
     * @param string $orderTable 对应的订单表名
     * @return int 处理条数
     */
    private function setInvalid($wxappId, $orderType, $orderTable)
    {
        try {
            $ids = Db::name('dealer_order')->alias('d')
                ->join($orderTable . ' o', 'o.order_id = d.order_id')
                ->where('d.wxapp_id', $wxappId)
                ->where('d.order_type', $orderType)
                ->where('d.is_settled', 0)
                ->where('d.is_invalid', 0)
                ->where('o.order_status', self::ORDER_CANCELLED)
                ->column('d.id');
        } catch (\Throwable $e) {
            // 表不存在（如未启用拼团）时跳过，不影响主订单处理
            log_write('分销失效订单查询失败[' . $orderTable . ']：' . $e->getMessage());
            return 0;
        }

        if (empty($ids)) {
            return 0;
        }
        if ($this->dryRun) {
            return count($ids);
        }

        try {
            Db::name('dealer_order')
                ->where('id', 'in', $ids)
                ->update(['is_invalid' => 1, 'update_time' => time()]);
            return count($ids);
        } catch (\Throwable $e) {
            log_write('分销订单标记失效失败：' . $e->getMessage());
            $this->out->writeln(sprintf('<error>    标记失效失败（%s）</error>', $e->getMessage()));
            return 0;
        }
    }
}
