<?php
namespace app\store\service\order;

use app\common\model\Region;

/**
 * 订单导出服务类（CSV）
 * 移植自 TP5 application/store/service/order/Export.php
 * 差异：TP5 直接 header 输出文件；TP8 改为返回 CSV 字符串，由控制器用 response() 返回。
 */
class Export
{
    /**
     * 表格标题
     * @var array
     */
    private $tileArray = [
        '订单号', '商品信息', '订单总额', '优惠券抵扣', '积分抵扣', '运费金额', '后台改价', '实付款金额', '支付方式', '下单时间',
        '买家', '买家留言', '配送方式', '自提门店名称', '自提联系人', '自提联系电话', '收货人姓名', '联系电话', '收货人地址',
        '物流公司', '物流单号', '付款状态', '付款时间', '发货状态', '发货时间', '收货状态', '收货时间', '订单状态', '微信支付交易号', '是否已评价',
    ];

    /**
     * 订单导出（生成 CSV 内容）
     * @param array|\think\Collection $list
     * @return string
     */
    public function orderList($list)
    {
        $dataArray = [];
        foreach ($list as $order) {
            $order    = is_array($order) ? $order : $order->toArray();
            $dataArray[] = [
                '订单号'         => $this->filterValue($order['order_no'] ?? ''),
                '商品信息'       => $this->filterGoodsInfo($order),
                '订单总额'       => $this->filterValue($order['total_price'] ?? 0),
                '优惠券抵扣'     => $this->filterValue($order['coupon_money'] ?? 0),
                '积分抵扣'       => $this->filterValue($order['points_money'] ?? 0),
                '运费金额'       => $this->filterValue($order['express_price'] ?? 0),
                '后台改价'       => $this->filterValue($this->filterUpdatePrice($order)),
                '实付款金额'     => $this->filterValue($order['pay_price'] ?? 0),
                '支付方式'       => $this->filterValue($this->statusText($order, 'pay_type')),
                '下单时间'       => $this->filterTime($order['create_time'] ?? 0),
                '买家'           => $this->filterValue($order['user']['nickName'] ?? ''),
                '买家留言'       => $this->filterValue($order['buyer_remark'] ?? ''),
                '配送方式'       => $this->filterValue($this->statusText($order, 'delivery_type')),
                '自提门店名称'   => $this->filterValue($order['extract_shop']['shop_name'] ?? ''),
                '自提联系人'     => $this->filterValue($order['extract']['linkman'] ?? ''),
                '自提联系电话'   => $this->filterValue($order['extract']['phone'] ?? ''),
                '收货人姓名'     => $this->filterValue($order['address']['name'] ?? ''),
                '联系电话'       => $this->filterValue($order['address']['phone'] ?? ''),
                '收货人地址'     => $this->filterValue($this->getFullAddress($order['address'] ?? [])),
                '物流公司'       => $this->filterValue($order['express']['express_name'] ?? ''),
                '物流单号'       => $this->filterValue($order['express_no'] ?? ''),
                '付款状态'       => $this->filterValue($this->statusText($order, 'pay_status')),
                '付款时间'       => $this->filterTime($order['pay_time'] ?? 0),
                '发货状态'       => $this->filterValue($this->statusText($order, 'delivery_status')),
                '发货时间'       => $this->filterTime($order['delivery_time'] ?? 0),
                '收货状态'       => $this->filterValue($this->statusText($order, 'receipt_status')),
                '收货时间'       => $this->filterTime($order['receipt_time'] ?? 0),
                '订单状态'       => $this->filterValue($this->statusText($order, 'order_status')),
                '微信支付交易号' => $this->filterValue($order['transaction_id'] ?? ''),
                '是否已评价'     => $this->filterValue(!empty($order['is_comment']) ? '是' : '否'),
            ];
        }
        return build_csv($this->tileArray, $dataArray);
    }

    /**
     * 批量发货模板（仅表头）
     * @return string
     */
    public function deliveryTpl()
    {
        return build_csv(['订单号', '物流单号'], []);
    }

    /**
     * 格式化商品信息
     */
    private function filterGoodsInfo($order)
    {
        $content = '';
        foreach ($order['goods'] ?? [] as $key => $goods) {
            $content .= ($key + 1) . ".商品名称：{$goods['goods_name']}\n";
            !empty($goods['goods_attr']) && $content .= "　商品规格：{$goods['goods_attr']}\n";
            $content .= "　购买数量：{$goods['total_num']}\n";
            $content .= "　商品总价：{$goods['total_price']}元\n\n";
        }
        return $content;
    }

    /** 表格值过滤（前后加制表符，避免 Excel 把长数字转科学计数法） */
    private function filterValue($value)
    {
        return "\t" . $value . "\t";
    }

    /** 日期值过滤 */
    private function filterTime($value)
    {
        if (!$value) {
            return '';
        }
        return $this->filterValue(date('Y-m-d H:i:s', $value));
    }

    /** 后台改价：symbol + 绝对值 */
    private function filterUpdatePrice($order)
    {
        $update = $order['update_price'] ?? 0;
        if (is_array($update)) {
            return ($update['symbol'] ?? '') . ($update['value'] ?? '');
        }
        $price = (float)$update;
        return ($price < 0 ? '-' : '+') . sprintf('%.2f', abs($price));
    }

    /** 状态字段取文本（兼容 ['text'=>..] 与标量） */
    private function statusText($order, $field)
    {
        $value = $order[$field] ?? null;
        if (is_array($value)) {
            return $value['text'] ?? '';
        }
        return (string)$value;
    }

    /** 收货地址：省市区 + 详细地址 */
    private function getFullAddress(array $address)
    {
        if (empty($address)) {
            return '';
        }
        $region = '';
        if (!empty($address['province_id'])) {
            $region .= Region::getNameById($address['province_id']);
        }
        if (!empty($address['city_id'])) {
            $region .= Region::getNameById($address['city_id']);
        }
        if (!empty($address['region_id'])) {
            $region .= Region::getNameById($address['region_id']);
        }
        return trim($region . ' ' . ($address['detail'] ?? ''));
    }
}
