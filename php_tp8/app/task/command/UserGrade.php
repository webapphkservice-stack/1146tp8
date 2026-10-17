<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;

/**
 * 会员等级定时维护：按累计消费金额自动升级用户等级
 *
 * 对应 php task/behavior/user/Grade.php（原为模型 init 时 Hook 触发 + Cache 节流 600s，
 * TP8 改为显式 crontab 命令）。
 *
 * 规则（与 TP5 一致）：
 *   1. 取全部可用等级，按 weight 降序遍历（保证用户最终落到「满足条件的最高等级」）
 *   2. 对每个等级，找出满足「累计消费 >= 该等级升级门槛」且
 *      「当前无等级 或 当前等级权重 < 该等级权重」的用户
 *   3. 已在本轮被更高等级匹配过的用户跳过，避免重复升级
 *   4. 更新 user.grade_id，并写 user_grade_log（change_type=20 自动升级）
 *
 * 用法：
 *   php think user:grade
 *   php think user:grade --wxapp=10001
 *   php think user:grade --dry-run
 *
 * 建议 crontab（每小时或每天一次）：
 *   30 * * * * cd /path/to/php_tp8 && php think user:grade >> /dev/null 2>&1
 */
class UserGrade extends Command
{
    /** @var Output 注意：不可命名为 $output，父类 Command 已声明该属性 */
    private $out;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    protected function configure()
    {
        $this->setName('user:grade')
            ->addOption('wxapp', null, Option::VALUE_OPTIONAL, '指定小程序 id，不传则处理全部')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('会员等级定时维护：按累计消费金额自动升级用户等级');
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
            $this->setUserGrade((int)$wxapp['wxapp_id']);
        }
        $output->writeln('<info>user:grade 执行完毕</info>');
    }

    /**
     * 按等级权重降序，逐等级升级达标用户
     * @param int $wxappId
     */
    private function setUserGrade($wxappId)
    {
        $grades = Db::name('user_grade')
            ->where('wxapp_id', $wxappId)
            ->where('status', 1)
            ->where('is_delete', 0)
            ->order('weight', 'desc')
            ->select()->toArray();

        if (empty($grades)) {
            $this->out->writeln(sprintf('  wxapp_id=%d: 无可用会员等级', $wxappId));
            return;
        }

        $upgraded = [];   // 本轮已升级的用户，避免被低等级重复匹配
        $count    = 0;

        foreach ($grades as $grade) {
            $expendMoney = $this->getUpgradeExpendMoney($grade);
            if ($expendMoney <= 0) {
                continue;
            }
            $query = Db::name('user')->alias('user')
                ->leftJoin('user_grade grade', 'grade.grade_id = user.grade_id')
                ->where('user.wxapp_id', $wxappId)
                ->where('user.is_delete', 0)
                ->where('user.expend_money', '>=', $expendMoney)
                ->where(function ($q) use ($grade) {
                    $q->where('user.grade_id', 0)
                        ->whereOr('grade.weight', '<', (int)$grade['weight']);
                });
            if ($upgraded) {
                $query->whereNotIn('user.user_id', $upgraded);
            }
            $list = $query->field('user.user_id, user.grade_id')->select()->toArray();
            if (empty($list)) {
                continue;
            }
            if ($this->dryRun) {
                $count += count($list);
                continue;
            }

            foreach ($list as $user) {
                try {
                    Db::transaction(function () use ($user, $grade, $wxappId) {
                        Db::name('user')
                            ->where('user_id', $user['user_id'])
                            ->update(['grade_id' => (int)$grade['grade_id']]);
                        $this->writeGradeLog($user, $grade, $wxappId);
                    });
                    $upgraded[] = (int)$user['user_id'];
                    $count++;
                } catch (\Throwable $e) {
                    log_write('会员等级升级失败[user_id=' . $user['user_id'] . ']：' . $e->getMessage());
                }
            }
        }

        $this->out->writeln(sprintf(
            '  wxapp_id=%d: %d 人%s',
            $wxappId,
            $count,
            $this->dryRun ? '（待升级）' : '已升级'
        ));
    }

    /**
     * 取等级的升级门槛金额（upgrade 为 JSON 字段）
     * @param array $grade
     * @return float
     */
    private function getUpgradeExpendMoney(array $grade)
    {
        $upgrade = $grade['upgrade'] ?? [];
        if (is_string($upgrade)) {
            $upgrade = json_decode($upgrade, true) ?: [];
        }
        return (float)($upgrade['expend_money'] ?? 0);
    }

    /**
     * 写会员等级变更日志（表不存在时不影响主流程）
     * @param array $user
     * @param array $grade
     * @param int $wxappId
     */
    private function writeGradeLog(array $user, array $grade, $wxappId)
    {
        try {
            Db::name('user_grade_log')->insert([
                'user_id'      => (int)$user['user_id'],
                'old_grade_id' => (int)($user['grade_id'] ?? 0),
                'new_grade_id' => (int)$grade['grade_id'],
                'change_type'  => 20,   // 20=自动升级（对齐 ChangeTypeEnum::AUTO_UPGRADE）
                'wxapp_id'     => $wxappId,
                'create_time'  => time(),
            ]);
        } catch (\Throwable $e) {
            log_write('会员等级变更日志写入失败[user_id=' . $user['user_id'] . ']：' . $e->getMessage());
        }
    }
}
