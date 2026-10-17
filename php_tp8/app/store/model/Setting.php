<?php
namespace app\store\model;

use app\common\model\Setting as SettingModel;

/**
 * 商家后台系统设置（移植自 TP5 store/model/Setting.php）
 *
 * 数据结构沿用 common\model\Setting::getAll()：
 * [ key => ['values' => array, 'describe' => string] ]
 * layout 模板中按 $setting['store']['values']['name'] 读取，与 TP5 一致。
 */
class Setting extends SettingModel
{
    /**
     * 获取全部设置（供 layout / 首页使用）
     * @return array
     */
    public static function getAll()
    {
        return SettingModel::getAll();
    }

    /**
     * 获取指定项设置
     * @param string $key
     * @return array
     */
    public static function getItem($key)
    {
        return SettingModel::getItem($key);
    }

    /**
     * 保存指定项设置（新增或更新）
     * @param string $key
     * @param array $values
     * @return bool
     */
    public function edit($key, $values)
    {
        $row = $this->where('key', '=', $key)
            ->where('wxapp_id', '=', self::getWxappId())
            ->find();
        if ($row) {
            $row->save(['values' => json_encode($values, JSON_UNESCAPED_UNICODE)]);
        } else {
            $this->save([
                'key'      => $key,
                'values'   => json_encode($values, JSON_UNESCAPED_UNICODE),
                'wxapp_id' => self::getWxappId(),
            ]);
        }
        return true;
    }
}
