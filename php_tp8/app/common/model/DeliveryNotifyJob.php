<?php
namespace app\common\model;

use think\facade\Db;

/**
 * 外送订单商户侧回写任务（队列，移植自 TP5 common/model/DeliveryNotifyJob.php）
 *
 * 表结构见 sql/alter_delivery_wechat_code_and_webhook.sql（delivery_notify_job）。
 * 状态：0待处理 1成功 2失败(已放弃)
 */
class DeliveryNotifyJob
{
    /**
     * 新增回写任务
     * @param array $data
     * @return int 新增的任务ID
     */
    public function addJob($data)
    {
        return (int)Db::name('delivery_notify_job')->insertGetId($data);
    }

    /**
     * 获取待处理任务（next_time <= 当前时间 且 status=0）
     * @param int $limit
     * @return array
     */
    public function getPending($limit = 50)
    {
        return Db::name('delivery_notify_job')
            ->where('status', 0)
            ->where('next_time', '<=', time())
            ->order('job_id', 'asc')
            ->limit($limit)
            ->select()->toArray();
    }

    /**
     * 更新任务
     * @param int $jobId
     * @param array $data
     * @return bool
     */
    public function onUpdate($jobId, $data)
    {
        return Db::name('delivery_notify_job')
            ->where('job_id', $jobId)->update($data) !== false;
    }

    /**
     * 后台列表（分页）
     * @param array $param
     * @return array
     */
    public function getList($param = [])
    {
        $query = Db::name('delivery_notify_job');
        if (isset($param['status']) && $param['status'] !== '' && $param['status'] !== null) {
            $query->where('status', (int)$param['status']);
        }
        if (!empty($param['order_id'])) {
            $query->where('order_id', (int)$param['order_id']);
        }
        $page = (int)($param['page'] ?? 1);
        $pageSize = 15;
        $total = (clone $query)->count();
        $rows = $query->order('job_id', 'desc')
            ->page($page, $pageSize)
            ->select()->toArray();
        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $pageSize,
            'current_page' => $page,
        ];
    }

    /**
     * 详情
     * @param int $jobId
     * @return array|null
     */
    public function detail($jobId)
    {
        return Db::name('delivery_notify_job')->where('job_id', (int)$jobId)->find();
    }
}
