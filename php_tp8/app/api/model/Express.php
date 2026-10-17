<?php
namespace app\api\model;

use think\facade\Db;
use app\common\model\BaseModel;

/**
 * 物流公司模型（轻量版，直查 express）
 */
class Express
{
    /** 全部可用物流公司（id/name/code 结构对齐 RN expressPicker） */
    public static function getAll()
    {
        $rows = Db::name('express')
            ->where('wxapp_id', BaseModel::getWxappId())
            ->order('sort', 'asc')
            ->select()->toArray();
        return array_map(function ($r) {
            return [
                'id'   => (int)$r['express_id'],
                'name' => $r['express_name'],
                'code' => $r['express_code'],
            ];
        }, $rows);
    }
}
