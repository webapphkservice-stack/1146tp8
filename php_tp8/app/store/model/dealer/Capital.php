<?php

namespace app\store\model\dealer;

use app\common\model\BaseModel;

/**
 * 分销商资金明细模型(后台，自包含)
 * Class Capital
 * @package app\store\model\dealer
 */
class Capital extends BaseModel
{
    protected $name = 'dealer_capital';

    /**
     * 新增资金明细
     * @param $data
     * @return false|int
     */
    public static function add($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $data['create_time'] = time();
        return (new static)->insert($data);
    }

}
