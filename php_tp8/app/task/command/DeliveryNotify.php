<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use app\task\service\DeliveryNotify as DeliveryNotifyService;

/**
 * 外送订单商户侧 webhook 回写队列消费（移植自 TP5 task/controller/DeliveryNotify + task/service/DeliveryNotify）
 *
 * 用法：
 *   php think delivery:notify                 # 消费待回写任务（默认 50 条）
 *   php think delivery:notify --limit=100     # 指定本次处理条数
 *   php think delivery:notify --job=12        # 只重投指定任务
 *
 * 建议 crontab（每分钟执行一次）：
 *   * * * * * cd /path/to/php_tp8 && php think delivery:notify >> /dev/null 2>&1
 *
 * 处理规则（与 TP5 一致）：
 *   - 成功（HTTP 2xx）    -> 任务 status=1，订单 notify_status=1
 *   - 失败且未达上限      -> 任务保持 status=0，next_time = now + 300s
 *   - 失败且已达 5 次     -> 任务 status=2（放弃），订单 notify_status=2
 */
class DeliveryNotify extends Command
{
    protected function configure()
    {
        $this->setName('delivery:notify')
            ->addOption('limit', null, Option::VALUE_OPTIONAL, '本次最多处理条数，默认 50')
            ->addOption('job', null, Option::VALUE_OPTIONAL, '只重投指定的 job_id')
            ->setDescription('外送订单商户侧 webhook 回写队列消费');
    }

    protected function execute(Input $input, Output $output)
    {
        $service = new DeliveryNotifyService;

        // 单条重投
        $jobId = (int)$input->getOption('job');
        if ($jobId > 0) {
            if (!$service->exec($jobId)) {
                $output->writeln('<error>' . ($service->error ?: '重投失败') . '</error>');
                return 1;
            }
            $output->writeln('<info>任务 #' . $jobId . ' 已重投</info>');
            return 0;
        }

        $limit = (int)$input->getOption('limit');
        $stat  = $service->run($limit > 0 ? $limit : DeliveryNotifyService::BATCH_SIZE);

        if (empty($stat['total'])) {
            $output->writeln('<info>无待处理回写任务</info>');
            return 0;
        }

        $output->writeln(sprintf(
            '<info>处理 %d 条：成功 %d，待重试 %d，放弃 %d</info>',
            $stat['total'], $stat['success'], $stat['retry'], $stat['giveup']
        ));
        return 0;
    }
}
