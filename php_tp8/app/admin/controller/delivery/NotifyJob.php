<?php
namespace app\admin\controller\delivery;

use think\facade\Db;
use app\common\controller\Controller;

/**
 * 外送回写任务队列（移植自 php admin/controller/delivery/NotifyJob.php）
 *
 * 纯 JSON：
 *   GET  /admin/delivery.notify_job/index?status=0&order_id=xxx   队列列表
 *   POST /admin/delivery.notify_job/retry?job_id=1                手动重试单条
 *
 * 状态：0 待处理 / 1 已成功 / 2 已失败(放弃)
 */
class NotifyJob extends Controller
{
    /** @var int 最大重试次数，达到后标记为失败 */
    const MAX_ATTEMPTS = 5;

    public function index()
    {
        $query = Db::name('delivery_notify_job');

        $status = $this->request->param('status', null);
        if ($status !== null && $status !== '') {
            $query->where('status', (int)$status);
        }
        $orderId = trim((string)$this->request->param('order_id', ''));
        if ($orderId !== '') {
            $query->where('order_id', $orderId);
        }

        $list = $query->order('job_id', 'desc')->paginate(15);
        return $this->renderSuccess(['list' => $list], 'ok');
    }

    /**
     * 手动重试：重置任务状态并立即投递一次
     */
    public function retry()
    {
        $jobId = (int)$this->request->param('job_id', 0);
        $job   = Db::name('delivery_notify_job')->where('job_id', $jobId)->find();
        if (!$job) {
            return $this->renderError('任务不存在');
        }

        $payload = json_decode($job['payload'] ?? '', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        // 立即投递
        $ok    = false;
        $error = '';
        try {
            [$httpCode, $body] = $this->postJson($job['notify_url'], $payload, 5);
            if ($httpCode >= 200 && $httpCode < 300) {
                $ok = true;
            } else {
                $error = 'HTTP ' . $httpCode . ' ' . mb_substr((string)$body, 0, 200);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if ($ok) {
            Db::name('delivery_notify_job')->where('job_id', $jobId)->update([
                'status'      => 1,
                'attempts'    => (int)$job['attempts'] + 1,
                'error'       => '',
                'next_time'   => 0,
                'update_time' => time(),
            ]);
            return $this->renderSuccess([], '已重新投递并成功');
        }

        $attempts = (int)$job['attempts'] + 1;
        $giveUp   = $attempts >= self::MAX_ATTEMPTS;
        Db::name('delivery_notify_job')->where('job_id', $jobId)->update([
            'status'      => $giveUp ? 2 : 0,
            'attempts'    => $attempts,
            'error'       => mb_substr($error, 0, 500),
            'next_time'   => $giveUp ? 0 : time() + 300,
            'update_time' => time(),
        ]);
        return $this->renderError('投递失败：' . $error . ($giveUp ? '（已达最大重试次数，标记为失败）' : '（稍后将自动重试）'));
    }

    /**
     * POST JSON
     * @param string $url
     * @param array  $payload
     * @param int    $timeout 秒
     * @return array [httpCode, body]
     */
    private function postJson($url, array $payload, $timeout = 5)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $body     = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        // 注意：PHP 8.5 起 curl_close() 已弃用，不做显式关闭
        unset($ch);
        if ($body === false) {
            throw new \Exception('HTTP 请求失败：' . $err);
        }
        return [$httpCode, (string)$body];
    }
}
