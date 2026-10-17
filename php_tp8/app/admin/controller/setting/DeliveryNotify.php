<?php
namespace app\admin\controller\setting;

use think\facade\Db;
use app\common\controller\Controller;

/**
 * 外送回写队列访问密钥（移植自 php admin/controller/setting/DeliveryNotify.php）
 *
 * 纯 JSON：
 *   GET  /admin/setting.delivery_notify/index          读取（wxapp_id 默认 10001）
 *   POST /admin/setting.delivery_notify/index          保存
 *
 * 字段：notify_key —— 定时任务以 URL 参数 ?key=xxx 触发回写消费时使用；
 *                     留空表示不校验（兼容旧定时任务）。
 */
class DeliveryNotify extends Controller
{
    /** @var string setting 表的 key */
    const SETTING_KEY = 'delivery_notify';

    /** @var string */
    const SETTING_DESCRIBE = '外送回写队列访问密钥';

    public function index()
    {
        if ($this->request->isPost()) {
            return $this->save();
        }
        return $this->renderSuccess(['values' => $this->getValues()], 'ok');
    }

    /**
     * 读取配置
     * @return array
     */
    private function getValues()
    {
        $row = Db::name('setting')
            ->where('key', self::SETTING_KEY)
            ->where('wxapp_id', $this->getWxappId())
            ->find();
        $values = $row ? json_decode($row['values'], true) : [];
        return is_array($values) ? $values : ['notify_key' => ''];
    }

    /**
     * 保存配置
     */
    private function save()
    {
        $data      = $this->request->param('delivery_notify/a', []);
        $notifyKey = trim((string)($data['notify_key'] ?? ''));
        $wxappId   = $this->getWxappId();
        $json      = json_encode(['notify_key' => $notifyKey], JSON_UNESCAPED_UNICODE);

        $row = Db::name('setting')
            ->where('key', self::SETTING_KEY)
            ->where('wxapp_id', $wxappId)
            ->find();
        if ($row) {
            Db::name('setting')->where('key', self::SETTING_KEY)->where('wxapp_id', $wxappId)
                ->update(['values' => $json, 'update_time' => time()]);
        } else {
            Db::name('setting')->insert([
                'key'         => self::SETTING_KEY,
                'describe'    => self::SETTING_DESCRIBE,
                'values'      => $json,
                'wxapp_id'    => $wxappId,
                'update_time' => time(),
            ]);
        }
        return $this->renderSuccess(['notify_key' => $notifyKey], '保存成功');
    }

    /**
     * 当前操作的 wxapp_id（平台级配置默认 10001）
     * @return int
     */
    private function getWxappId()
    {
        return (int)($this->request->param('wxapp_id', 10001) ?: 10001);
    }
}
