<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use app\common\service\order\Refund as RefundService;

/**
 * 拼团活动定时维护：过期拼单置为失败，并（可选）自动退款
 *
 * 对应 php task/behavior/sharing/Active.php。
 *
 * 两个子任务：
 *   1. onUpdateActiveEnd：sharing_active 中 end_time <= now 且 status=10（拼单中）
 *      -> status=30（拼单失败）
 *   2. onOrderRefund：若拼团设置开启自动退款（sharing_setting.basic.auto_refund），
 *      对「拼单失败」的已付款拼团订单原路退款
 *      条件：order_type=20 AND pay_status=20 AND order_status=10 AND is_refund=0
 *            AND is_delete=0 AND active_id IN (本次失败的拼单)
 *      退款后置 is_refund=1、order_status=20
 *
 * 单次最多处理 100 单（与 TP5 一致：防止单次运行过久、且微信退款接口有 qps 限制）。
 *
 * 用法：
 *   php think sharing:active [--wxapp=10001] [--dry-run]
 * 建议 crontab：每 10 分钟执行一次（拼单结束时间精确到分钟，跑太慢会让用户等退款）
 */
class SharingActive extends Command
{
    /** @var Output 不可命名为 $output，父类 Command 已声明 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    /** @var int 单次最多处理订单数 */
    const MAX_LIMIT = 100;

    /** @var int 拼单状态：拼单中 */
    const ACTIVE_BEGIN = 10;

    /** @var int 拼单状态：拼单失败 */
    const ACTIVE_FAIL = 30;

    protected function configure()
    {
        $this->setName('sharing:active')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('拼团活动定时维护：过期拼单置为失败并自动退款');
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
            $failedIds = $this->onUpdateActiveEnd($id);
            if (!empty($failedIds) && $this->isAutoRefund($id)) {
                $this->onOrderRefund($id, $failedIds);
            }
        }
        $output->writeln('<info>sharing:active 执行完毕</info>');
    }

    /**
     * 过期拼单置为失败
     * @param int $wxappId
     * @return array 本次置为失败的拼单 id 集合
     */
    private function onUpdateActiveEnd($wxappId)
    {
        $now = time();
        $ids = Db::name('sharing_active')
            ->where('wxapp_id', $wxappId)
            ->where('status', self::ACTIVE_BEGIN)
            ->where('end_time', '<=', $now)
            ->column('active_id');

        if (empty($ids)) {
            $this->out->writeln(sprintf('  wxapp_id=%d: 0 个拼单过期', $wxappId));
            return [];
        }
        if ($this->dryRun) {
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 个拼单待置为失败', $wxappId, count($ids)));
            return $ids;
        }

        try {
            Db::name('sharing_active')
                ->where('active_id', 'in', $ids)
                ->update(['status' => self::ACTIVE_FAIL, 'update_time' => $now]);
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 个拼单已置为失败', $wxappId, count($ids)));
            return $ids;
        } catch (\Throwable $e) {
            log_write('拼团活动过期处理失败：' . $e->getMessage());
            $this->out->writeln(sprintf('<error>  wxapp_id=%d: 处理失败（%s）</error>', $wxappId, $e->getMessage()));
            return [];
        }
    }

    /**
     * 对拼单失败的已付款订单原路退款
     * @param int   $wxappId
     * @param array $failedActiveIds 失败拼单 id
     */
    private function onOrderRefund($wxappId, array $failedActiveIds)
    {
        $list = Db::name('sharing_order')
            ->where('wxapp_id', $wxappId)
            ->where('order_type', 20)          // 20=拼团（10=单买不受拼团失败影响）
            ->where('pay_status', 20)
            ->where('order_status', 10)
            ->where('is_refund', 0)
            ->where('is_delete', 0)
            ->where('active_id', 'in', $failedActiveIds)
            ->limit(self::MAX_LIMIT)
            ->select()->toArray();

        if (empty($list)) {
            $this->out->writeln('    退款: 0 单');
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln('    退款: ' . count($list) . ' 单（待退款）');
            return;
        }

        $count = 0;
        foreach ($list as $order) {
            try {
                // 幂等键固定绑定售后/订单，重复运行不会重复退钱
                $outRefundNo = 'SR' . $order['order_id'] . strtoupper(substr(md5((string)$order['order_id']), 0, 6));
                $refundService = new RefundService;
                if (!$refundService->execute($order, (float)$order['pay_price'], $outRefundNo)) {
                    throw new \Exception($refundService->getError() ?: '退款失败');
                }
                Db::name('sharing_order')->where('order_id', $order['order_id'])->update([
                    'is_refund'   => 1,
                    'order_status' => 20,
                    'update_time' => time(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                log_write('拼团失败退款失败[' . $order['order_no'] . ']：' . $e->getMessage());
            }
        }
        $this->out->writeln('    退款: ' . $count . ' 单已退款');
    }

    /**
     * 是否开启「拼团失败自动退款」
     * @param int $wxappId
     * @return bool
     */
    private function isAutoRefund($wxappId)
    {
        try {
            $values = Db::name('sharing_setting')
                ->where('key', 'basic')
                ->where('wxapp_id', $wxappId)
                ->value('values');
            $basic = $values ? json_decode($values, true) : [];
            return !empty($basic['auto_refund']);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
