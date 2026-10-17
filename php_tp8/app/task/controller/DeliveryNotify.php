<?php
namespace app\task\controller;

use think\facade\Db;
use app\common\controller\Controller;
use app\task\service\DeliveryNotify as DeliveryNotifyService;

/**
 * 外送订单商户侧 webhook 回写队列消费入口（移植自 TP5 task/controller/DeliveryNotify.php）
 *
 * 由定时任务（如每分钟）通过 URL 触发： /task/delivery_notify/index
 * 配置了访问密钥后需携带 ?key=xxx（后台「系统设置 → 外送回写密钥」或 setting 表 delivery_notify.notify_key）
 *
 * 更推荐直接使用命令：php think delivery:notify
 */
class DeliveryNotify extends Controller
{
    public function index()
    {
        // 访问密钥：优先取后台设置 setting.delivery_notify.notify_key，留空表示不校验
        $setting = Db::name('setting')
            ->where('key', 'delivery_notify')
            ->where('wxapp_id', 10001)
            ->find();
        $values    = $setting ? (is_array($setting['values']) ? $setting['values'] : json_decode($setting['values'], true)) : [];
        $configKey = trim((string)($values['notify_key'] ?? ''));

        if ($configKey !== '' && $configKey !== (string)$this->request->param('key')) {
            return $this->renderError('访问密钥错误');
        }

        $limit   = (int)$this->request->param('limit', 50);
        $jobId   = (int)$this->request->param('job_id', 0);
        $service = new DeliveryNotifyService;

        if ($jobId > 0) {
            if (!$service->exec($jobId)) {
                return $this->renderError($service->error ?: '重投失败');
            }
            return $this->renderSuccess([], '已重投任务 #' . $jobId);
        }

        $stat = $service->run($limit > 0 ? $limit : DeliveryNotifyService::BATCH_SIZE);
        return $this->renderSuccess($stat, 'delivery webhook notify processed');
    }
}
