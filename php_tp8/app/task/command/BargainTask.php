<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;

/**
 * 砍价任务定时维护：到期的砍价任务置为已结束
 *
 * 对应 php task/behavior/bargain/Task.php。
 * 条件：bargain_task 中 end_time <= now AND status = 1（砍价中） AND is_delete = 0
 * 动作：status = 0（已结束），用户不能再发起/接受帮砍。
 *
 * 用法：
 *   php think bargain:task [--wxapp=10001] [--dry-run]
 * 建议 crontab：每 10 分钟执行一次
 */
class BargainTask extends Command
{
    /** @var Output 不可命名为 $output，父类 Command 已声明 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    /** @var int 任务状态：砍价中 */
    const TASK_ING = 1;

    /** @var int 任务状态：已结束 */
    const TASK_END = 0;

    protected function configure()
    {
        $this->setName('bargain:task')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('砍价任务定时维护：到期的砍价任务置为已结束');
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
            $this->onSetIsEnd((int)$wxapp['wxapp_id']);
        }
        $output->writeln('<info>bargain:task 执行完毕</info>');
    }

    /**
     * 到期的砍价任务置为已结束
     * @param int $wxappId
     */
    private function onSetIsEnd($wxappId)
    {
        $now = time();
        $ids = Db::name('bargain_task')
            ->where('wxapp_id', $wxappId)
            ->where('status', self::TASK_ING)
            ->where('is_delete', 0)
            ->where('end_time', '<=', $now)
            ->column('task_id');

        if (empty($ids)) {
            $this->out->writeln(sprintf('  wxapp_id=%d: 0 个任务到期', $wxappId));
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 个任务待结束', $wxappId, count($ids)));
            return;
        }

        try {
            Db::name('bargain_task')
                ->where('task_id', 'in', $ids)
                ->update(['status' => self::TASK_END, 'update_time' => $now]);
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 个任务已结束', $wxappId, count($ids)));
        } catch (\Throwable $e) {
            log_write('砍价任务结束处理失败：' . $e->getMessage());
            $this->out->writeln(sprintf('<error>  wxapp_id=%d: 处理失败（%s）</error>', $wxappId, $e->getMessage()));
        }
    }
}
