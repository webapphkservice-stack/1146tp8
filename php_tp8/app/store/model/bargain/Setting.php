<?php

namespace app\store\model\bargain;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 砍价设置模型(后台，自包含)
 * Class Setting
 * @package app\store\model\bargain
 */
class Setting extends BaseModel
{
    protected $name = 'bargain_setting';

    /**
     * 设置项描述
     * @var array
     */
    private $describe = [
        'basic' => '基础设置',
    ];

    /**
     * 获取设置项
     * @param $key
     * @return array
     */
    public static function getItem($key)
    {
        $data = (new static)->where('key', '=', $key)
            ->where('wxapp_id', self::getWxappId())
            ->find();
        return $data ? json_decode($data['values'], true) : [];
    }

    /**
     * 更新设置项
     * @param $key
     * @param $values
     * @return bool
     */
    public function edit($key, $values)
    {
        $model = (new static)->where('key', '=', $key)
            ->where('wxapp_id', self::getWxappId())
            ->find() ?: $this;
        Cache::delete('bargain_setting_' . self::getWxappId());
        return $model->save([
                'key' => $key,
                'describe' => $this->describe[$key] ?? '',
                'values' => json_encode($values, JSON_UNESCAPED_UNICODE),
                'wxapp_id' => self::getWxappId(),
            ]) !== false;
    }

}
