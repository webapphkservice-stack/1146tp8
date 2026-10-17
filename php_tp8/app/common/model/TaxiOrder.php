<?php

namespace app\common\model;

use think\facade\Db;

/**
 * 出租车预约订单模型（轻量 Db 实现，移植自 php common/model/TaxiOrder）
 * Class TaxiOrder
 * @package app\common\model
 */
class TaxiOrder extends BaseModel
{
    protected $name = 'taxi_order';

    /** @var array 订单状态文字 */
    public static $statusText = [
        0 => '待接单',
        1 => '已接单',
        2 => '已完成',
        3 => '已取消',
    ];

    /** @var array 车型文字 */
    public static $carTypeText = [
        0 => '普通',
        1 => '商务',
    ];

    public $error = '';

    public function getError()
    {
        return $this->error ?: '';
    }

    /**
     * 订单编号
     */
    public static function createOrderNo()
    {
        return 'TX' . date('YmdHis') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * 新增订单，成功返回 order_id
     */
    public function add($data)
    {
        $data['order_no'] = self::createOrderNo();
        $data['status'] = 0;
        $data['create_time'] = time();
        $data['update_time'] = time();
        try {
            $orderId = Db::name('taxi_order')->insertGetId($data);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return (int)$orderId;
    }

    /**
     * 我的订单列表（RN taxiApi.orders）
     */
    public static function getListByUser($userId, $wxappId)
    {
        $rows = Db::name('taxi_order')->where('user_id', $userId)
            ->where('wxapp_id', $wxappId)
            ->order('order_id', 'desc')
            ->limit(50)
            ->select()->toArray();
        return array_map([self::class, 'format'], $rows);
    }

    /**
     * 统一字段格式化（对齐 tp5 append 输出）
     */
    public static function format($row)
    {
        $row['status_text'] = self::$statusText[$row['status']] ?? '';
        $row['car_type_text'] = self::$carTypeText[$row['car_type']] ?? '';
        $row['create_time_text'] = '';
        if (!empty($row['create_time'])) {
            $dt = new \DateTime('@' . $row['create_time']);
            $dt->setTimezone(new \DateTimeZone('Asia/Kuala_Lumpur'));
            $row['create_time_text'] = $dt->format('Y-m-d H:i');
        }
        $arr = [];
        if (!empty($row['dispatch_car'])) {
            $decoded = json_decode($row['dispatch_car'], true);
            $arr = is_array($decoded) ? $decoded : [];
        }
        $row['dispatch_car_arr'] = $arr;
        $label = '';
        if ($arr) {
            $label = trim(($arr['type'] ?? '') . ' ' . ($arr['brand'] ?? '') . ' ' . ($arr['number'] ?? ''));
            if (!empty($arr['whatsapp'])) {
                $label .= '(司机WA:' . $arr['whatsapp'] . ')';
            }
        }
        $row['dispatch_label'] = $label;
        return $row;
    }

    /**
     * 确保数据表存在（首次使用自动建表）
     */
    public static function ensureTable()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        $table = config('database.prefix') . 'taxi_order';
        try {
            Db::execute("CREATE TABLE IF NOT EXISTS `{$table}` (
              `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
              `order_no` varchar(30) NOT NULL DEFAULT '' COMMENT '订单编号',
              `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
              `airport_name` varchar(120) NOT NULL DEFAULT '' COMMENT '抵达机场',
              `car_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '车型：0普通 1商务',
              `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '出租车价格',
              `currency` varchar(10) NOT NULL DEFAULT 'RM' COMMENT '货币符号',
              `flight_no` varchar(30) NOT NULL DEFAULT '' COMMENT '航班号',
              `departure_time` varchar(30) NOT NULL DEFAULT '' COMMENT '出发时间',
              `takeoff_time` varchar(30) NOT NULL DEFAULT '' COMMENT '起飞时间',
              `landing_time` varchar(30) NOT NULL DEFAULT '' COMMENT '降落时间',
              `hotel_name` varchar(120) NOT NULL DEFAULT '' COMMENT '酒店名称',
              `hotel_address` varchar(255) NOT NULL DEFAULT '' COMMENT '酒店地址',
              `contact_name` varchar(50) NOT NULL DEFAULT '' COMMENT '联系人',
              `contact_phone` varchar(30) NOT NULL DEFAULT '' COMMENT '联系电话',
              `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
              `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0待接单 1已接单 2已完成 3已取消',
              `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
              `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
              `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
              PRIMARY KEY (`order_id`),
              KEY `user_id` (`user_id`),
              KEY `wxapp_id` (`wxapp_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='出租车预约订单表'");
        } catch (\Exception $e) {
            // 建表失败不阻断流程
        }
        self::ensureDispatchFields();
    }

    /**
     * 确保派车字段存在
     */
    public static function ensureDispatchFields()
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        $table = config('database.prefix') . 'taxi_order';
        try {
            $rows = Db::query(
                "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'dispatch_car'",
                [$table]
            );
        } catch (\Exception $e) {
            return;
        }
        if (!empty($rows)) {
            return;
        }
        try {
            Db::execute("ALTER TABLE `{$table}` ADD COLUMN `dispatch_car` varchar(1000) NOT NULL DEFAULT '' COMMENT '派车信息(JSON: type/brand/number/whatsapp)' AFTER `status`");
        } catch (\Exception $e) {
            // 字段已存在则忽略
        }
    }

    /**
     * 商家后台订单列表
     * @param int $wxappId
     * @param array $params
     * @return \think\Paginator
     */
    public function getListForStore($wxappId, $params = [])
    {
        self::ensureTable();
        $query = $this->where('wxapp_id', $wxappId);
        if (!empty($params['order_no'])) {
            $query->where('order_no', 'like', '%' . trim($params['order_no']) . '%');
        }
        if (!empty($params['flight_no'])) {
            $query->where('flight_no', 'like', '%' . trim($params['flight_no']) . '%');
        }
        if (isset($params['status']) && $params['status'] !== '' && $params['status'] >= 0) {
            $query->where('status', (int)$params['status']);
        }
        return $query->order(['order_id' => 'desc'])
            ->paginate(15, false, ['query' => request()->param()])
            ->append(['status_text', 'car_type_text', 'create_time_text', 'dispatch_car_arr', 'dispatch_label']);
    }

    /**
     * 后台修改订单状态
     * @param int $status
     * @param string|null $dispatchCar
     * @return bool
     */
    public function setStatus($status, $dispatchCar = null)
    {
        $status = (int)$status;
        if (!array_key_exists($status, self::$statusText)) {
            $this->error = '无效的订单状态';
            return false;
        }
        $data = ['status' => $status, 'update_time' => time()];
        if ($dispatchCar !== null) {
            $data['dispatch_car'] = $dispatchCar;
        }
        if ($this->save($data) === false) {
            $this->error = '状态更新失败';
            return false;
        }
        return true;
    }
}
