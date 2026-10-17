<?php
namespace app\task\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use think\facade\Log;

/**
 * 区域代理分润结算（移植自 TP5 task/command/RegionAgent.php）
 *
 * 用法：
 *   php think region_agent              # 结算全部待分润订单
 *   php think region_agent --dry-run    # 只统计不落库（用于验证）
 *
 * 建议 crontab（每天执行一次）：
 *   0 3 * * * cd /path/to/php_tp8 && php think region_agent >> /dev/null 2>&1
 *
 * 结算规则（与 TP5 完全一致）：
 *   1. 取 order 表中 已付款(20) + 已完成(30) + is_region_agent=1 + 未结算(0) 的订单
 *   2. 按订单收货地址的省 / 市 / 区 三级，分别找到 status=103 的代理商，
 *      发放 region_agent_first / second / third 三档金额到用户余额并写余额日志(scene=70)
 *   3. 订单标记 region_agent_is_settle=1
 *   4. 汇总代理商累计分润(money)与本月分润(current_money)回写 region_agent 表
 *
 * 依赖表：region_agent、order(含 region_agent_* 字段)、order_address、user、user_balance_log
 * 表/字段缺失时命令会给出明确提示并安全退出（不会 500）。
 */
class RegionAgent extends Command
{
    /** @var int 余额日志场景：区域代理分润 */
    const SCENE_REGION_AGENT = 70;

    /** @var int 代理商审核通过状态 */
    const AGENT_STATUS_PASS = 103;

    /** @var bool 是否只统计不落库 */
    private $dryRun = false;

    /** @var Output 注意：不可命名为 $output，父类 Command 已声明该属性 */
    private $out;

    protected function configure()
    {
        $this->setName('region_agent')
            ->addOption('dry-run', null, Option::VALUE_NONE, '只统计不落库')
            ->setDescription('区域代理分润结算');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->out    = $output;
        $this->dryRun = (bool)$input->getOption('dry-run');

        // 前置检查：区域代理相关表/字段是否齐备（本地库未建表时安全退出）
        $missing = $this->checkSchema();
        if (!empty($missing)) {
            $output->writeln('<comment>区域代理分润依赖缺失，已跳过：</comment>');
            foreach ($missing as $item) {
                $output->writeln('  - ' . $item);
            }
            $output->writeln('<comment>请先导入 yoshop_region_agent* 建表 SQL 及 order 表的 region_agent_* 字段。</comment>');
            return 1;
        }

        $orders = Db::name('order')
            ->where('pay_status', 20)
            ->where('order_status', 30)
            ->where('is_region_agent', 1)
            ->where('region_agent_is_settle', 0)
            ->select()->toArray();

        if (empty($orders)) {
            $output->writeln('<info>无待结算的区域代理订单</info>');
            return 0;
        }

        $stat = ['order' => 0, 'pay' => 0, 'amount' => 0];
        foreach ($orders as $order) {
            $stat['order']++;
            $address = Db::name('order_address')
                ->where('order_id', $order['order_id'])
                ->field('province_id,city_id,region_id')
                ->find();
            if (empty($address)) {
                $this->settleOrder($order['order_id']);
                continue;
            }

            $levels = [
                ['label' => '省代', 'region_id' => $address['province_id'], 'money' => $order['region_agent_first']],
                ['label' => '市代', 'region_id' => $address['city_id'],    'money' => $order['region_agent_second']],
                ['label' => '区代', 'region_id' => $address['region_id'],  'money' => $order['region_agent_third']],
            ];

            $userIds = [];
            foreach ($levels as $level) {
                $userId = $this->getRegionAgentUserId($level['region_id']);
                $money  = (float)$level['money'];
                if ($userId <= 0 || $money <= 0) {
                    continue;
                }
                $userIds[] = $userId;
                $this->grant($userId, $money, $level['label'], $level['region_id'], $order);
                $stat['pay']++;
                $stat['amount'] += $money;
            }

            $this->settleOrder($order['order_id']);

            // 更新涉及的代理商利润统计
            foreach (array_unique(array_filter($userIds)) as $userId) {
                $this->refreshAgentStat($userId);
            }
        }

        $prefix = $this->dryRun ? '[dry-run] ' : '';
        $output->writeln(sprintf(
            '<info>%s结算订单 %d 笔，发放分润 %d 笔，合计 %.2f 元</info>',
            $prefix, $stat['order'], $stat['pay'], $stat['amount']
        ));
        return 0;
    }

    /**
     * 发放分润：加余额 + 写余额日志
     * @param int    $userId
     * @param float  $money
     * @param string $label   省代/市代/区代
     * @param int    $regionId
     * @param array  $order
     */
    private function grant($userId, $money, $label, $regionId, $order)
    {
        $describe = $label . '分润,区域id:' . $regionId . ',订单id:' . $order['order_id'];
        $this->out->writeln(sprintf('  - %s user#%d +%.2f（%s）', $label, $userId, $money, $describe));

        if ($this->dryRun) {
            return;
        }

        Db::name('user')->where('user_id', $userId)->inc('balance', $money)->update();
        Db::name('user_balance_log')->insert([
            'user_id'     => $userId,
            'scene'       => self::SCENE_REGION_AGENT,
            'money'       => $money,
            'describe'    => $describe,
            'remark'      => '',
            'wxapp_id'    => $order['wxapp_id'],
            'create_time' => time(),
        ]);
        Log::write('region_agent settle ' . $describe . ' money:' . $money, 'info');
    }

    /**
     * 标记订单已结算
     * @param int $orderId
     */
    private function settleOrder($orderId)
    {
        if ($this->dryRun) {
            return;
        }
        Db::name('order')->where('order_id', $orderId)->update(['region_agent_is_settle' => 1]);
    }

    /**
     * 刷新代理商的累计 / 本月分润统计
     * @param int $userId
     */
    private function refreshAgentStat($userId)
    {
        $start = strtotime(date('Y-m-01 00:00:00'));
        $end   = strtotime(date('Y-m-t 23:59:59'));

        $query = Db::name('user_balance_log')
            ->where('scene', self::SCENE_REGION_AGENT)
            ->where('user_id', $userId);
        $totalProfit = (float)$query->sum('money');

        $monthProfit = (float)Db::name('user_balance_log')
            ->where('scene', self::SCENE_REGION_AGENT)
            ->where('user_id', $userId)
            ->whereBetween('create_time', [$start, $end])
            ->sum('money');

        if ($this->dryRun) {
            return;
        }
        Db::name('region_agent')
            ->where('user_id', $userId)
            ->where('status', self::AGENT_STATUS_PASS)
            ->update([
                'money'         => $totalProfit,
                'current_money' => $monthProfit,
            ]);
    }

    /**
     * 根据区域 id 查找对应代理商用户 id
     * @param int $regionId
     * @return int 找不到返回 0
     */
    private function getRegionAgentUserId($regionId)
    {
        $userId = Db::name('region_agent')
            ->where('region_id', $regionId)
            ->where('status', self::AGENT_STATUS_PASS)
            ->value('user_id');
        return (int)($userId ?: 0);
    }

    /**
     * 检查依赖的表与字段是否齐备
     * @return array 缺失项描述，为空表示齐备
     */
    private function checkSchema()
    {
        $required = [
            'order'            => ['is_region_agent', 'region_agent_is_settle', 'region_agent_first'],
            'order_address'    => ['province_id', 'city_id', 'region_id'],
            'region_agent'     => ['region_id', 'user_id', 'status'],
            'user'             => ['user_id', 'balance'],
            'user_balance_log' => ['user_id', 'scene', 'money'],
        ];

        $missing = [];
        foreach ($required as $table => $columns) {
            try {
                $exists = array_column(Db::query('SHOW COLUMNS FROM ' . $this->realTable($table)), 'Field');
            } catch (\Throwable $e) {
                $missing[] = "表 {$table} 不存在";
                continue;
            }
            foreach ($columns as $column) {
                if (!in_array($column, $exists, true)) {
                    $missing[] = "表 {$table} 缺字段 {$column}";
                }
            }
        }
        return $missing;
    }

    /**
     * 真实表名（带前缀）
     * @param string $name
     * @return string
     */
    private function realTable($name)
    {
        static $prefix = null;
        if ($prefix === null) {
            $prefix = (string)config('database.connections.mysql.prefix');
        }
        return $prefix . $name;
    }
}
