<?php

namespace app\common\model;

/**
 * 小程序图标管理模型
 * Class WxappIcon
 * @package app\common\model
 */
class WxappIcon extends BaseModel
{
    protected $name = 'wxapp_icon';
    protected $pk = 'icon_id';

    /**
     * 获取图标详情
     * @param int $wxappId
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function detail($wxappId = 0)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        $model = new static;
        $data = $model->where('wxapp_id', $wxappId)->find();
        if (empty($data)) {
            // 返回默认数据
            return [
                'icon_id' => 0,
                'wxapp_id' => $wxappId,
                'user_icon' => '',
                'home_icon' => '',
                'logo' => '',
                'create_time' => time(),
                'update_time' => time(),
            ];
        }
        return $data;
    }

    /**
     * 编辑图标
     * @param array $data
     * @return bool
     */
    public function edit($data)
    {
        $wxappId = self::$wxapp_id;
        $iconData = [
            'wxapp_id'   => $wxappId,
            'user_icon'  => self::stripBaseUrl($data['user_icon'] ?? ''),
            'home_icon'  => self::stripBaseUrl($data['home_icon'] ?? ''),
            'logo'       => self::stripBaseUrl($data['logo'] ?? ''),
            'update_time' => time(),
        ];

        // 判断是否存在记录
        $exist = $this->where('wxapp_id', $wxappId)->find();
        if ($exist) {
            // 使用模型 save 更新当前记录（避免调用 Query 的 update 方法）
            return $exist->save($iconData) !== false;
        } else {
            $iconData['create_time'] = time();
            $model = new static;
            return $model->save($iconData) !== false;
        }
    }

    /**
     * 去掉值中的站点域名前缀，统一存为相对路径，避免小程序端拼接 base_url 时重复
     * @param string $value
     * @return string
     */
    private static function stripBaseUrl($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '';
        }
        $base = rtrim(base_url(), '/');
        if (stripos($value, $base) === 0) {
            return substr($value, strlen($base));
        }
        return $value;
    }

    /**
     * 获取图标数据（用于小程序端）
     * @param int $wxappId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getIconData($wxappId = 0)
    {
        $wxappId = $wxappId ?: self::$wxapp_id;
        $data = self::detail($wxappId);

        // 默认图标路径
        $defaultUserIcon = '/assets/user/avatar.png';
        $defaultHomeIcon = '/assets/user/home.png';
        $defaultLogo = '/assets/store/logo.png';

        // 若字段已是完整 URL 则直接使用，避免与 base_url 拼接导致域名重复
        $formatUrl = function ($value, $default) {
            if (empty($value)) {
                return base_url() . $default;
            }
            if (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0) {
                return $value;
            }
            return base_url() . '/' . ltrim($value, '/');
        };

        return [
            'userIcon' => $formatUrl($data['user_icon'], $defaultUserIcon),
            'homeIcon' => $formatUrl($data['home_icon'], $defaultHomeIcon),
            'logo' => $formatUrl($data['logo'], $defaultLogo),
        ];
    }
}
