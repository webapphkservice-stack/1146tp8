<?php

namespace app\store\model\dealer;

use think\facade\Cache;
use app\common\model\BaseModel;

/**
 * 分销商设置模型(后台，自包含)
 * Class Setting
 * @package app\store\model\dealer
 */
class Setting extends BaseModel
{
    protected $name = 'dealer_setting';

    /**
     * 设置项描述
     * @var array
     */
    private $describe = [
        'basic' => '基础设置',
        'condition' => '分销商条件',
        'commission' => '佣金设置',
        'settlement' => '结算',
        'words' => '自定义文字',
        'license' => '申请协议',
        'background' => '页面背景图',
        'qrcode' => '分销海报',
    ];

    /**
     * 获取设置项
     * @param $key
     * @return mixed
     */
    public static function getItem($key)
    {
        $model = (new static)->withoutGlobalScope()
            ->where('key', '=', $key)
            ->where('wxapp_id', self::getWxappId())
            ->find();
        return $model ? json_decode($model['values'], true) : [];
    }

    /**
     * 获取全部设置项
     * @return array
     */
    public static function getAll()
    {
        $list = (new static)->withoutGlobalScope()
            ->where('wxapp_id', self::getWxappId())
            ->select()
            ->toArray();
        $data = [];
        foreach ($list as $item) {
            $item['values'] = json_decode($item['values'], true);
            $data[$item['key']] = $item;
        }
        return $data;
    }

    /**
     * 更新设置
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        $this->startTrans();
        try {
            foreach ($data as $key => $values) {
                $this->saveValues($key, $values);
            }
            $this->commit();
            Cache::delete('dealer_setting_' . self::getWxappId());
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 保存单个设置项
     * @param $key
     * @param $values
     * @return false|int
     */
    private function saveValues($key, $values)
    {
        $model = self::detail($key) ?: new self;
        if (!$this->validValues($key, $values)) {
            throw new \think\Exception($this->error);
        }
        return $model->save([
            'key' => $key,
            'describe' => $this->describe[$key] ?? '',
            'values' => json_encode($values, JSON_UNESCAPED_UNICODE),
            'wxapp_id' => self::getWxappId(),
        ]);
    }

    /**
     * 详情
     * @param $key
     * @return null|\think\Model
     */
    public static function detail($key)
    {
        return static::withoutGlobalScope()
            ->where('key', '=', $key)
            ->where('wxapp_id', self::getWxappId())
            ->find();
    }

    /**
     * 数据验证
     * @param $key
     * @param $values
     * @return bool
     */
    private function validValues($key, $values)
    {
        if ($key === 'settlement') {
            if (!isset($values['pay_type']) || empty($values['pay_type'])) {
                $this->error = '请设置 结算-提现方式';
                return false;
            }
        }
        return true;
    }

}
