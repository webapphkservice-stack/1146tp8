<?php
namespace app\store\model;

use app\common\model\Delivery as DeliveryModel;
use app\common\model\Region;
use app\store\model\Goods as GoodsModel;
use think\facade\Db;

/**
 * 配送模板模型（服务端渲染版，继承 common\model\Delivery）
 *
 * TP8 适配要点：
 *  - allowField(true) → allowField($this->field)（think-orm v4 必须传数组）
 *  - rule() 关联（TP8 无 DeliveryRule 模型）→ 改为 Db 直写 delivery_rule
 *  - getAll() 保留（供商品编辑页下拉，返回 method 的 text/value）
 */
class Delivery extends DeliveryModel
{
    protected $updateTime = false;

    /** 可写字段白名单（替代 TP5 的 allowField(true)） */
    protected $field = ['name', 'method', 'sort', 'wxapp_id'];

    /**
     * 获取全部（供商品编辑页下拉，method 转成视图需要的 text/value）
     * @return array
     */
    public static function getAll()
    {
        $list = Db::name('delivery')->where('wxapp_id', self::getWxappId())
            ->order('sort', 'asc')->select()->toArray();
        foreach ($list as &$item) {
            $value = (int)$item['method'];
            $item['method'] = [
                'text'  => self::$methodData[$value] ?? '未知',
                'value' => $value,
            ];
        }
        unset($item);
        return $list;
    }

    /**
     * 添加新记录
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        if (!$this->onValidate($data)) return false;
        $data['wxapp_id'] = self::getWxappId();
        if ($this->allowField($this->field)->save($data)) {
            return $this->createDeliveryRule($data['rule']);
        }
        return false;
    }

    /**
     * 编辑记录
     * @param array $data
     * @return bool
     */
    public function edit($data)
    {
        if (!$this->onValidate($data)) return false;
        if ($this->allowField($this->field)->save($data)) {
            return $this->createDeliveryRule($data['rule']);
        }
        return false;
    }

    /**
     * 表单验证
     * @param array $data
     * @return bool
     */
    private function onValidate($data)
    {
        if (!isset($data['rule']) || empty($data['rule'])) {
            $this->error = '请选择可配送区域';
            return false;
        }
        foreach ($data['rule']['first'] as $value) {
            if ((int)$value <= 0 || (int)$value != $value) {
                $this->error = '首件或首重必须是正整数';
                return false;
            }
        }
        foreach ($data['rule']['additional'] as $value) {
            if ((int)$value <= 0 || (int)$value != $value) {
                $this->error = '续件或续重必须是正整数';
                return false;
            }
        }
        return true;
    }

    /**
     * 获取配送区域及运费设置项
     * @return array
     */
    public function getFormList()
    {
        // 所有地区
        $regions = Region::getCacheAll();
        $rules = Db::name('delivery_rule')
            ->where('delivery_id', $this['delivery_id'])
            ->select()->toArray();
        $list = [];
        foreach ($rules as $rule) {
            $citys = explode(',', $rule['region']);
            $province = [];
            foreach ($citys as $cityId) {
                if (!isset($regions[$cityId])) continue;
                !in_array($regions[$cityId]['pid'], $province) && $province[] = $regions[$cityId]['pid'];
            }
            $list[] = [
                'first'          => $rule['first'],
                'first_fee'      => $rule['first_fee'],
                'additional'     => $rule['additional'],
                'additional_fee' => $rule['additional_fee'],
                'province'       => $province,
                'citys'          => $citys,
            ];
        }
        return $list;
    }

    /**
     * 添加模板区域及运费（Db 直写，避免依赖未迁移的 DeliveryRule 模型）
     * @param array $data
     * @return bool
     */
    private function createDeliveryRule($data)
    {
        $save = [];
        $connt = count($data['region']);
        for ($i = 0; $i < $connt; $i++) {
            $save[] = [
                'delivery_id'    => $this['delivery_id'],
                'region'         => $data['region'][$i],
                'first'          => $data['first'][$i],
                'first_fee'      => $data['first_fee'][$i],
                'additional'     => $data['additional'][$i],
                'additional_fee' => $data['additional_fee'][$i],
                'wxapp_id'       => self::getWxappId(),
            ];
        }
        Db::name('delivery_rule')->where('delivery_id', $this['delivery_id'])->delete();
        return Db::name('delivery_rule')->insertAll($save) !== false;
    }

    /**
     * 删除记录
     * @return bool
     */
    public function remove()
    {
        // 验证运费模板是否被商品使用
        if (!$this->checkIsUseGoods($this['delivery_id'])) {
            return false;
        }
        // 删除运费规则与模板
        Db::name('delivery_rule')->where('delivery_id', $this['delivery_id'])->delete();
        return $this->delete();
    }

    /**
     * 验证运费模板是否被商品使用
     * @param int $deliveryId
     * @return bool
     */
    private function checkIsUseGoods($deliveryId)
    {
        $goodsCount = (new GoodsModel)->where('delivery_id', '=', $deliveryId)
            ->where('is_delete', '=', 0)
            ->count();
        if ($goodsCount > 0) {
            $this->error = '该模板被' . $goodsCount . '个商品使用，不允许删除';
            return false;
        }
        return true;
    }
}
