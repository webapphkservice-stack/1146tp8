<?php
namespace app\admin\controller\setting;

use think\facade\Db;
use app\common\controller\Controller;

/**
 * 网站信息（备案信息 / 联系信息）
 * 移植自 TP5 admin/controller/setting/Web.php，改为纯 JSON（admin 应用已 API 化，无 view）。
 *
 *   GET  /admin/setting.web/index           读取（wxapp_id 默认 10001）
 *   POST /admin/setting.web/index           保存（form: site_info[name] / site_info[beian] / site_info[contact]）
 */
class Web extends Controller
{
    /** @var string setting 表的 key */
    const SETTING_KEY = 'site_info';

    /** @var string */
    const SETTING_DESCRIBE = '网站信息';

    /** @var array 默认值，与 TP5 视图初值一致 */
    const DEFAULT_VALUES = ['name' => '', 'logo' => '', 'beian' => '', 'contact' => ''];

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
        if ($row && !empty($row['values'])) {
            // values 可能是 JSON 字符串，也可能是模型 json 字段已解码的数组
            $values = is_array($row['values']) ? $row['values'] : json_decode($row['values'], true);
            if (is_array($values)) {
                return array_merge(self::DEFAULT_VALUES, $values);
            }
        }
        return self::DEFAULT_VALUES;
    }

    /**
     * 保存配置
     */
    private function save()
    {
        $data = $this->request->param('site_info/a', []);
        $values = [
            'name'    => trim((string)($data['name'] ?? '')),
            'logo'    => trim((string)($data['logo'] ?? '')),
            'beian'   => trim((string)($data['beian'] ?? '')),
            'contact' => trim((string)($data['contact'] ?? '')),
        ];
        $json    = json_encode($values, JSON_UNESCAPED_UNICODE);
        $wxappId = $this->getWxappId();

        $row = Db::name('setting')
            ->where('key', self::SETTING_KEY)
            ->where('wxapp_id', $wxappId)
            ->find();
        if ($row) {
            Db::name('setting')
                ->where('key', self::SETTING_KEY)
                ->where('wxapp_id', $wxappId)
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
        return $this->renderSuccess(['values' => $values], '保存成功');
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
