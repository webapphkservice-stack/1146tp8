<?php

namespace app\store\model\store;

use app\common\model\store\Shop as ShopModel;

/**
 * 商家门店模型（服务端渲染版）
 * Class Shop
 * @package app\store\model\store
 */
class Shop extends ShopModel
{
    /**
     * 获取列表数据（分页 + 筛选）
     * @param array $param
     * @return \think\Paginator
     */
    public function getList($param = [])
    {
        return $this->setListQueryWhere($param)->paginate(15);
    }

    /**
     * 获取所有门店列表（供订单/核销筛选下拉使用，返回 shop_id, shop_name）
     * @param array $param
     * @return array
     */
    public static function getAllList($param = [])
    {
        return (new static)->setListQueryWhere($param)
            ->field('shop_id, shop_name')
            ->order('sort', 'asc')
            ->select();
    }

    /**
     * 设置列表查询条件
     * @param array $param
     * @return $this
     */
    private function setListQueryWhere($param = [])
    {
        $param = array_merge(['is_check' => '', 'search' => '', 'status' => null], $param);
        is_numeric($param['is_check']) && $param['is_check'] > -1 && $this->where('is_check', '=', (int)$param['is_check']);
        !empty($param['search']) && $this->where('shop_name|linkman|phone', 'like', "%{$param['search']}%");
        is_numeric($param['status']) && $this->where('status', '=', (int)$param['status']);
        return $this->where('is_delete', '=', '0')->order(['sort' => 'asc', 'create_time' => 'desc']);
    }

    /**
     * 新增记录
     * @param $data
     * @return bool
     */
    public function add($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->save($this->createData($data));
    }

    /**
     * 编辑记录
     * @param $data
     * @return bool
     */
    public function edit($data)
    {
        if (!$this->validateForm($data)) {
            return false;
        }
        return $this->save($this->createData($data)) !== false;
    }

    /**
     * 软删除
     * @return false|int
     */
    public function setDelete()
    {
        return $this->save(['is_delete' => 1]);
    }

    /**
     * 保存门店配送(送水)配置
     * @param array $data
     * @return bool
     */
    public function editDelivery($data)
    {
        return $this->allowField([
            'delivery_enabled',
            'delivery_fee',
            'min_order_amount',
            'free_delivery_amount',
            'deposit_enabled',
            'deposit_amount',
            'water_ticket_enabled',
            'empty_bucket_recycle',
            'delivery_time_range',
        ])->save($data) !== false;
    }

    /**
     * 组装写入数据（补全 wxapp_id、坐标、geohash）
     * @param array $data
     * @return array
     */
    private function createData($data)
    {
        $data['wxapp_id'] = self::getWxappId();
        $coordinate = explode(',', $data['coordinate'] ?? '');
        $data['latitude'] = $coordinate[0] ?? '';
        $data['longitude'] = $coordinate[1] ?? '';
        $data['geohash'] = static::geohashEncode(
            (float)($data['longitude'] ?? 0),
            (float)($data['latitude'] ?? 0)
        );
        unset($data['coordinate']);
        return $data;
    }

    /**
     * 表单验证
     * @param $data
     * @return bool
     */
    private function validateForm($data)
    {
        if (!isset($data['logo_image_id']) || empty($data['logo_image_id'])) {
            $this->error = '请选择门店logo';
            return false;
        }
        return true;
    }

    /**
     * 生成 geohash（GeoHash 库在 TP8 不可用，内置实现，精度 12）
     * @param float $lng
     * @param float $lat
     * @return string
     */
    private static function geohashEncode($lng, $lat)
    {
        $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
        $latRange = [-90.0, 90.0];
        $lngRange = [-180.0, 180.0];
        $geohash = '';
        $ch = 0;
        $bit = 1;
        $even = true;
        while (strlen($geohash) < 12) {
            if ($even) {
                $mid = ($lngRange[0] + $lngRange[1]) / 2;
                if ($lng >= $mid) {
                    $ch |= $bit;
                    $lngRange[0] = $mid;
                } else {
                    $lngRange[1] = $mid;
                }
            } else {
                $mid = ($latRange[0] + $latRange[1]) / 2;
                if ($lat >= $mid) {
                    $ch |= $bit;
                    $latRange[0] = $mid;
                } else {
                    $latRange[1] = $mid;
                }
            }
            $even = !$even;
            if ($bit < 16) {
                $bit <<= 1;
            } else {
                $geohash .= $base32[$ch];
                $bit = 1;
                $ch = 0;
            }
        }
        return $geohash;
    }
}
