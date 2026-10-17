<?php
namespace app\api\model;

use think\facade\Db;

/**
 * 用户收货地址模型（精简版，Db 直查，对齐 RN Address 页面契约）
 * - 列表返回 list[] + default_id（由控制器组装）
 * - 详情返回 { detail, region[] }
 * - 新增/编辑接收 region 文本数组（CN: [省,市,区] / MY|OTHER: [州,市]），尽力映射 region 表 id
 */
class UserAddress
{
    /** @var string 错误信息 */
    private $error = '';

    /**
     * 地址列表
     */
    public function getList($userId)
    {
        $rows = Db::name('user_address')->where('user_id', $userId)
            ->order('address_id', 'desc')->select()->toArray();
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'address_id' => $r['address_id'],
                'name'       => $r['name'],
                'phone'      => $r['phone'],
                'region'     => [
                    'province' => $r['state'],
                    'city'     => $r['city'],
                    'region'   => $r['district'],
                ],
                'detail'     => $r['detail'],
                'country'    => $r['country'] ?: 'CN',
            ];
        }
        return $list;
    }

    /**
     * 地址详情
     */
    public function detail($userId, $addressId)
    {
        $r = Db::name('user_address')->where('user_id', $userId)
            ->where('address_id', $addressId)->find();
        if (!$r) {
            return false;
        }
        return [
            'detail' => [
                'address_id' => $r['address_id'],
                'name'       => $r['name'],
                'phone'      => $r['phone'],
                'detail'     => $r['detail'],
                'country'    => $r['country'] ?: 'CN',
                'state'      => $r['state'],
                'city'       => $r['city'],
            ],
            'region' => [$r['state'], $r['city'], $r['district']],
        ];
    }

    /**
     * 新增地址，返回新地址 id
     */
    public function add($userId, $data)
    {
        $country = isset($data['country']) ? strtoupper($data['country']) : 'CN';
        $regionInfo = $this->parseRegion($data, $country);
        if ($regionInfo === false) {
            return false;
        }
        $addrId = Db::name('user_address')->insertGetId(array_merge([
            'name'     => $data['name'],
            'phone'    => $data['phone'],
            'detail'   => $data['detail'],
            'user_id'  => $userId,
            'wxapp_id' => User::getWxappId(),
            'country'  => $country,
        ], $regionInfo));
        if (!$addrId) {
            $this->error = '地址保存失败';
            return false;
        }
        // 若用户当前无默认地址，则设为默认
        $curDefault = Db::name('user')->where('user_id', $userId)->value('address_id');
        if (empty($curDefault)) {
            Db::name('user')->where('user_id', $userId)->update(['address_id' => $addrId]);
        }
        return $addrId;
    }

    /**
     * 编辑地址
     */
    public function edit($userId, $data)
    {
        if (empty($data['address_id'])) {
            $this->error = '缺少 address_id';
            return false;
        }
        $country = isset($data['country']) ? strtoupper($data['country']) : 'CN';
        $regionInfo = $this->parseRegion($data, $country);
        if ($regionInfo === false) {
            return false;
        }
        $res = Db::name('user_address')->where('user_id', $userId)
            ->where('address_id', $data['address_id'])
            ->update(array_merge([
                'name'    => $data['name'],
                'phone'   => $data['phone'],
                'detail'  => $data['detail'],
                'country' => $country,
            ], $regionInfo));
        return $res !== false;
    }

    /**
     * 删除地址（物理删除；若删除的是默认地址则清空 user.address_id）
     */
    public function remove($userId, $addressId)
    {
        $curDefault = Db::name('user')->where('user_id', $userId)->value('address_id');
        if ($curDefault == $addressId) {
            Db::name('user')->where('user_id', $userId)->update(['address_id' => 0]);
        }
        return Db::name('user_address')->where('user_id', $userId)
            ->where('address_id', $addressId)->delete();
    }

    /**
     * 设为默认地址
     */
    public function setDefault($userId, $addressId)
    {
        return Db::name('user')->where('user_id', $userId)
            ->update(['address_id' => $addressId]);
    }

    public function getDefaultId($userId)
    {
        return (int)Db::name('user')->where('user_id', $userId)->value('address_id');
    }

    /**
     * 整理地区信息
     * @param array $data
     * @param string $country CN / MY / OTHER
     * @return array|false
     */
    private function parseRegion($data, $country)
    {
        $region = isset($data['region'])
            ? (is_array($data['region']) ? $data['region'] : explode(',', $data['region']))
            : [];
        if ($country === 'MY' || $country === 'OTHER') {
            return [
                'province_id' => 0,
                'city_id'     => 0,
                'region_id'   => 0,
                'state'       => $region[0] ?? '',
                'city'        => $region[1] ?? '',
                'district'    => '',
            ];
        }
        // 中国大陆：三级地区（尽力匹配 region 表 id，匹配不到则存 0，文本仍保留）
        $provinceId = $this->regionId($region[0] ?? '', 1, 0);
        $cityId     = $provinceId ? $this->regionId($region[1] ?? '', 2, $provinceId) : 0;
        $regionId   = $cityId ? $this->regionId($region[2] ?? '', 3, $cityId) : 0;
        return [
            'province_id' => $provinceId,
            'city_id'     => $cityId,
            'region_id'   => $regionId,
            'state'       => $region[0] ?? '',
            'city'        => $region[1] ?? '',
            'district'    => $region[2] ?? '',
        ];
    }

    private function regionId($name, $level, $pid)
    {
        if (!$name) {
            return 0;
        }
        $q = Db::name('region')->where('name', $name)->where('level', $level);
        if ($pid > 0) {
            $q->where('pid', $pid);
        }
        return (int)$q->value('id');
    }

    public function getError()
    {
        return $this->error;
    }
}
