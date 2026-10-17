<?php
namespace app\task\service;

use think\facade\Log;
use app\common\library\Http;
use app\common\model\DeliveryNotifyJob;
use app\common\model\DeliveryOrder;

/**
 * 外送订单商户侧 webhook 回写队列消费（移植自 TP5 task/service/DeliveryNotify.php）
 *
 * 入队端：app\common\library\DeliveryWebhookQueue::push()
 * 消费端：php think delivery:notify（推荐）或 URL /task/delivery_notify/index
 */
class DeliveryNotify
{
    /** @var int 每次最多处理条数 */
    const BATCH_SIZE = 50;

    /** @var int 最大重试次数（达到后标记失败，订单 notify_status=2） */
    const MAX_ATTEMPTS = 5;

    /** @var int 重试间隔（秒） */
    const RETRY_INTERVAL = 300;

    /** @var string 错误信息 */
    public $error = '';

    /**
     * 执行队列消费
     * @param int $limit
     * @return array ['total' => int, 'success' => int, 'retry' => int, 'giveup' => int]
     */
    public function run($limit = self::BATCH_SIZE)
    {
        $stat = ['total' => 0, 'success' => 0, 'retry' => 0, 'giveup' => 0];
        $jobs = (new DeliveryNotifyJob)->getPending((int)$limit);
        foreach ($jobs as $job) {
            $stat['total']++;
            $result = $this->process($job);
            $stat[$result]++;
        }
        return $stat;
    }

    /**
     * 手动重试单条回写任务（后台入口调用）
     * 重置为待处理并立即重投，清空失败信息与重试计数
     * @param int $jobId
     * @return bool
     */
    public function exec($jobId)
    {
        $model = new DeliveryNotifyJob;
        $job   = $model->detail($jobId);
        if (!$job) {
            $this->error = '任务不存在';
            return false;
        }
        $model->onUpdate($job['job_id'], [
            'status'    => 0,
            'attempts'  => 0,
            'error'     => '',
            'next_time' => time(),
        ]);
        $this->process($model->detail($jobId));
        return true;
    }

    /**
     * 处理单条回写任务
     * @param array $job
     * @return string success / retry / giveup
     */
    private function process($job)
    {
        if (empty($job)) {
            return 'giveup';
        }

        $payload   = json_decode($job['payload'] ?? '', true) ?: [];
        $notifyUrl = $job['notify_url'] ?? '';
        $attempts  = (int)$job['attempts'] + 1;

        $result = Http::postJson($notifyUrl, $payload, 5);

        if ($result['success']) {
            (new DeliveryNotifyJob)->onUpdate($job['job_id'], [
                'status'      => 1,
                'attempts'    => $attempts,
                'error'       => '',
                'next_time'   => 0,
                'update_time' => time(),
            ]);
            DeliveryOrder::markNotified($job['order_id'], $job['wxapp_id'], 1);
            $this->log('success', $job, $result);
            return 'success';
        }

        // 失败：未达上限则安排重试，否则标记失败
        $giveup = $attempts >= self::MAX_ATTEMPTS;
        $error  = ($result['error'] ?: '') . ' HTTP:' . $result['http_code'];
        (new DeliveryNotifyJob)->onUpdate($job['job_id'], [
            'status'      => $giveup ? 2 : 0,
            'attempts'    => $attempts,
            'error'       => $error,
            'next_time'   => $giveup ? 0 : time() + self::RETRY_INTERVAL,
            'update_time' => time(),
        ]);
        if ($giveup) {
            DeliveryOrder::markNotified($job['order_id'], $job['wxapp_id'], 2);
        }
        $this->log($giveup ? 'giveup' : 'retry', $job, $result, $error);
        return $giveup ? 'giveup' : 'retry';
    }

    /**
     * 记录日志
     * @param string $result success / retry / giveup
     * @param array  $job
     * @param array  $http
     * @param string $error
     */
    private function log($result, $job, $http, $error = '')
    {
        $context = [
            'job_id'     => $job['job_id'] ?? 0,
            'order_id'   => $job['order_id'] ?? 0,
            'notify_url' => $job['notify_url'] ?? '',
            'result'     => $result,
            'error'      => $error,
            'http_code'  => $http['http_code'] ?? 0,
        ];
        // TP8 无 log_write()，统一走框架日志（runtime/log）
        Log::write('delivery_webhook ' . json_encode($context, JSON_UNESCAPED_UNICODE), 'info');
    }
}
