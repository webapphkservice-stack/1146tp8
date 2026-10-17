<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;

/**
 * 优惠券定时维护：把已过期的用户优惠券标记为「已失效」
 *
 * 对应 php task/behavior/UserCoupon.php（原为模型 init 时 Hook 触发 + Cache 节流，
 * TP8 改为显式 crontab 命令）。
 *
 * 过期判定（与 TP5 一致）：
 *   - expire_type = 20（有效期至）：end_time + 86400 < now
 *   - expire_type = 10（领取后 N 天）：create_time + expire_day * 86400 < now
 *
 * 用法：
 *   php think coupon:expire
 *   php think coupon:expire --wxapp=10001
 *   php think coupon:expire --dry-run
 *
 * 建议 crontab（每天执行一次）：
 *   0 1 * * * cd /path/to/php_tp8 && php think coupon:expire >> /dev/null 2>&1
 */
class UserCoupon extends Command
{
    /** @var Output 注意：不可命名为 $output，父类 Command 已声明该属性 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('coupon:expire')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('优惠券定时维护：将已过期的用户优惠券标记为已失效');
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
            $this->setExpired((int)$wxapp['wxapp_id']);
        }
        $output->writeln('<info>coupon:expire 执行完毕</info>');
    }

    /**
     * 标记已过期的优惠券
     * @param int $wxappId
     */
    private function setExpired($wxappId)
    {
        $now = time();
        // 未失效、未使用，且按有效期类型判定已过期
        $ids = Db::name('user_coupon')
            ->where('wxapp_id', $wxappId)
            ->where('is_expire', 0)
            ->where('is_use', 0)
            ->whereRaw(
                'IF(expire_type = 20, end_time + 86400 < ?, create_time + expire_day * 86400 < ?)',
                [$now, $now]
            )
            ->column('user_coupon_id');

        if (empty($ids)) {
            $this->out->writeln(sprintf('  wxapp_id=%d: 0 张', $wxappId));
            return;
        }
        if ($this->dryRun) {
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 张（待置为已失效）', $wxappId, count($ids)));
            return;
        }

        try {
            Db::name('user_coupon')
                ->where('user_coupon_id', 'in', $ids)
                ->update(['is_expire' => 1]);
            $this->out->writeln(sprintf('  wxapp_id=%d: %d 张已置为失效', $wxappId, count($ids)));
        } catch (\Throwable $e) {
            log_write('优惠券过期处理失败[wxapp_id=' . $wxappId . ']：' . $e->getMessage());
            $this->out->writeln(sprintf('<error>  wxapp_id=%d: 处理失败（%s）</error>', $wxappId, $e->getMessage()));
        }
    }
}
