<?php
namespace app\store\model;

use app\common\model\BaseModel;
use think\facade\Db;

/**
 * 规格(组)模型（服务端渲染版，移植自 TP5 store/model/Spec.php）
 */
class Spec extends BaseModel
{
    protected $name = 'spec';
    protected $pk = 'spec_id';
    protected $updateTime = false;

    public function getSpecIdByName($spec_name)
    {
        return Db::name('spec')->where('spec_name', $spec_name)
            ->where('wxapp_id', self::getWxappId())->value('spec_id');
    }

    public function add($spec_name)
    {
        return Db::name('spec')->insertGetId([
            'spec_name' => $spec_name,
            'wxapp_id'  => self::getWxappId(),
            'create_time' => time(),
        ]);
    }
}
