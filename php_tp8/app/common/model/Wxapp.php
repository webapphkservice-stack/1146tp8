<?php
namespace app\common\model;

use think\facade\Cache;

/**
 * 微信小程序模型（基础表，移植自 php_my common/model/Wxapp）
 */
class Wxapp extends BaseModel
{
    protected $name = 'wxapp';

    /**
     * 获取小程序详情
     * @param int $wxappId
     * @return array|null
     */
    public static function detail($wxappId)
    {
        if (empty($wxappId)) {
            return null;
        }
        $detail = self::where(['wxapp_id' => $wxappId, 'is_delete' => 0])
            ->find();
        return $detail ? $detail->toArray() : null;
    }

    /**
     * 获取小程序缓存（去除 Cache::tag，避免 file 驱动不支持 tag 时报错）
     * @param int $wxappId
     * @return array|null
     */
    public static function getWxappCache($wxappId)
    {
        $cacheKey = "wxapp_{$wxappId}";
        if (!$data = Cache::get($cacheKey)) {
            $data = self::detail($wxappId);
            if (empty($data)) {
                return null;
            }
            Cache::set($cacheKey, $data, 1800);
        }
        return $data;
    }
}
