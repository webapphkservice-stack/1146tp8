<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 规格值模型（服务端渲染版，移植自 TP5 store/model/SpecValue.php）
 */
class SpecValue extends BaseModel
{
    protected $name = 'spec_value';
    protected $pk = 'spec_value_id';
    protected $updateTime = false;

    public function getSpecValueIdByName($spec_id, $spec_value)
    {
        return Db::name('spec_value')->where('spec_id', $spec_id)
            ->where('spec_value', $spec_value)
            ->where('wxapp_id', self::getWxappId())->value('spec_value_id');
    }

    public function add($spec_id, $spec_value)
    {
        return Db::name('spec_value')->insertGetId([
            'spec_value' => $spec_value,
            'spec_id'    => $spec_id,
            'wxapp_id'   => self::getWxappId(),
            'create_time' => time(),
        ]);
    }
}
