<?php
namespace app\common\model;

use think\facade\Db;

/**
 * 对应 TP5 的 common/model/Setting.php 的 getItem/getAll/defaultData。
 * 鉴于是 POC，仅保留 google 登录所需读取逻辑 + 新建商城时的默认配置。
 */
class Setting extends BaseModel
{
    protected $name = 'setting';

    public static function getItem($key)
    {
        $data = self::getAll();
        $values = $data[$key]['values'] ?? [];
        // 数据库缺省时以 defaultData 兜底，避免视图读取未定义键导致 500
        $defaults = self::defaultData();
        if (isset($defaults[$key]['values'])) {
            $values = array_replace_recursive($defaults[$key]['values'], is_array($values) ? $values : []);
        }
        return $values;
    }

    public static function getAll()
    {
        $list = self::where('wxapp_id', self::getWxappId())->select();
        $data = [];
        foreach ($list as $item) {
            $data[$item['key']] = [
                'values'   => json_decode($item['values'], true),
                'describe' => $item['describe'],
            ];
        }
        return $data;
    }

    /**
     * 商城默认配置（完整移植 php common/model/Setting.php::defaultData）
     *
     * 新建小程序商城时按此批量写 setting 表，保证新商城开箱可用
     * （缺配置项会导致下单/积分/订阅消息等依赖 setting 的功能异常）。
     *
     * @param string|null $storeName 商城名称
     * @return array 每项结构 ['key' => .., 'describe' => .., 'values' => ..]
     */
    public static function defaultData($storeName = null)
    {
        return [
            // VIP会员设置
            'vip' => [
                'key'      => 'vip',
                'describe' => 'VIP会员设置',
                'values'   => [
                    'member_day'          => '15',  // 每月会员日(几号)，0 表示不启用
                    'is_member_day_double' => '1',  // 会员日双倍积分开关
                    'double_points_ratio' => '2',   // 会员日积分倍数
                    'freight_coupon_id'   => '0',   // 开通会员赠送的运费券ID(0不赠送)
                    'return_freight'      => '1',   // 退货包运费开关
                ],
            ],
            // 商城设置
            'store' => [
                'key'      => 'store',
                'describe' => '商城设置',
                'values'   => [
                    'name'          => $storeName ?: '萤火小程序商城',
                    // 配送方式：10 快递 / 20 上门自提 / 30 门店配送(送水)
                    'delivery_type' => [10, 20, 30],
                    'kuaidi100'     => [
                        'customer' => '',
                        'key'      => '',
                    ],
                    'icp_text'   => '',
                    // 腾讯地图Key（门店坐标选取器用，可在「商城设置」中修改）
                    'tx_map_key' => 'ZWEBZ-R7N3U-BJSVH-4TCR3-66MDQ-S3FDJ',
                ],
            ],
            // 交易设置
            'trade' => [
                'key'      => 'trade',
                'describe' => '交易设置',
                'values'   => [
                    'order' => [
                        'close_days'   => '3',
                        'receive_days' => '10',
                        'refund_days'  => '7',
                    ],
                    'freight_rule' => '10',
                ],
            ],
            // 上传设置
            'storage' => [
                'key'      => 'storage',
                'describe' => '上传设置',
                'values'   => [
                    'default' => 'local',
                    'engine'  => [
                        'local'  => [],
                        'qiniu'  => [
                            'bucket'     => '',
                            'access_key' => '',
                            'secret_key' => '',
                            'domain'     => 'http://',
                        ],
                        'aliyun' => [
                            'bucket'            => '',
                            'access_key_id'     => '',
                            'access_key_secret' => '',
                            'domain'            => 'http://',
                        ],
                        'qcloud' => [
                            'bucket'     => '',
                            'region'     => '',
                            'secret_id'  => '',
                            'secret_key' => '',
                            'domain'     => 'http://',
                        ],
                    ],
                ],
            ],
            // 短信通知
            'sms' => [
                'key'      => 'sms',
                'describe' => '短信通知',
                'values'   => [
                    'default' => 'aliyun',
                    'engine'  => [
                        'aliyun' => [
                            'AccessKeyId'     => '',
                            'AccessKeySecret' => '',
                            'sign'            => '萤火科技',
                            'register'        => [
                                'is_enable'     => '0',
                                'template_code' => '',
                                'accept_phone'  => '',
                            ],
                        ],
                    ],
                    // 阿里云 ChatApp（WhatsApp 验证码）
                    'chatapp' => [
                        'accessKeyId'         => '',
                        'accessKeySecret'     => '',
                        'endpoint'            => 'cams.ap-southeast-1.aliyuncs.com',
                        'regionId'            => 'ap-southeast-1',
                        'custSpaceId'         => '',
                        'channelType'         => 'whatsapp',
                        'language'            => 'zh_CN',
                        'fromPhone'           => '',
                        'verifyTemplateCode'  => '',
                        'verifyExpire'        => 300,
                        'verifyInterval'      => 60,
                    ],
                ],
            ],
            // 小票打印机设置
            'printer' => [
                'key'      => 'printer',
                'describe' => '小票打印机设置',
                'values'   => [
                    'is_open'      => '0',
                    'printer_id'   => '',
                    'order_status' => [],
                ],
            ],
            // 满额包邮设置
            'full_free' => [
                'key'      => 'full_free',
                'describe' => '满额包邮设置',
                'values'   => [
                    'is_open'      => '0',
                    'money'        => '',
                    'notin_region' => [
                        'province' => [],
                        'citys'    => [],
                        'treeData' => [],
                    ],
                    'notin_goods' => [],
                ],
            ],
            // 用户充值设置
            'recharge' => [
                'key'      => 'recharge',
                'describe' => '用户充值设置',
                'values'   => [
                    'is_entrance'   => '1',
                    'is_custom'     => '1',
                    'is_match_plan' => '1',
                    'describe'      => "1. 账户充值仅限微信在线方式支付，充值金额实时到账；\n"
                        . "2. 账户充值套餐赠送的金额即时到账；\n"
                        . "3. 账户余额有效期：自充值日起至用完即止；\n"
                        . "4. 若有其它疑问，可拨打客服电话400-000-1234",
                ],
            ],
            // 积分设置
            'points' => [
                'key'      => 'points',
                'describe' => '积分设置',
                'values'   => [
                    'points_name'          => '积分',
                    'is_shopping_gift'     => '0',
                    'gift_ratio'           => '100',
                    'is_shopping_discount' => '0',
                    'discount'             => [
                        'discount_ratio'   => '0.01',
                        'full_order_price' => '100.00',
                        'max_money_ratio'  => '10',
                    ],
                    'describe' => "a) 积分不可兑现、不可转让,仅可在本平台使用;\n"
                        . "b) 您在本平台参加特定活动也可使用积分,详细使用规则以具体活动时的规则为准;\n"
                        . "c) 积分的数值精确到个位(小数点后全部舍弃,不进行四舍五入)\n"
                        . "d) 买家在完成该笔交易(订单状态为“已签收”)后才能得到此笔交易的相应积分,如购买商品参加店铺其他优惠,则优惠的金额部分不享受积分获取;",
                ],
            ],
            // 订阅消息设置
            'submsg' => [
                'key'      => 'submsg',
                'describe' => '订阅消息设置',
                'values'   => [
                    'order' => [
                        'payment' => [
                            'template_id' => '',
                            'keywords'    => ['character_string1', 'time2', 'amount4', 'thing3'],
                            'title'       => '新订单提醒',
                        ],
                        'delivery' => [
                            'template_id' => '',
                            'keywords'    => ['character_string1', 'thing2', 'name12', 'thing11', 'thing17'],
                            'title'       => '订单发货通知',
                        ],
                        'refund' => [
                            'template_id' => '',
                            'keywords'    => ['phrase1', 'thing6', 'character_string2', 'date3', 'thing4'],
                            'title'       => '售后状态通知',
                        ],
                    ],
                    'sharing' => [
                        'active_status' => [
                            'template_id' => '',
                            'keywords'    => ['thing1', 'amount5', 'number7', 'thing3', 'thing6'],
                            'title'       => '拼团进度通知',
                        ],
                    ],
                    'dealer' => [
                        'apply' => [
                            'template_id' => '',
                            'keywords'    => ['date1', 'phrase2', 'date3', 'thing4'],
                            'title'       => '代理商入驻审核通知',
                        ],
                        'withdraw_01' => [
                            'template_id' => '',
                            'keywords'    => ['amount1', 'thing3', 'thing4'],
                            'title'       => '提现成功通知',
                        ],
                        'withdraw_02' => [
                            'template_id' => '',
                            'keywords'    => ['amount1', 'time3', 'thing4'],
                            'title'       => '提现失败通知',
                        ],
                    ],
                ],
            ],
            // 外送回写队列访问密钥
            'delivery_notify' => [
                'key'      => 'delivery_notify',
                'describe' => '外送回写队列访问密钥',
                'values'   => [
                    'notify_key' => '',   // 留空不校验，兼容旧定时任务
                ],
            ],
            // 出租车设置
            'taxi' => [
                'key'      => 'taxi',
                'describe' => '出租车设置',
                'values'   => [
                    'enable'   => '1',
                    'currency' => 'RM',
                    'airports' => [],
                ],
            ],
            // 小程序虚拟支付配置（售卖 VIP / 代币等虚拟商品）
            'virtual_payment' => [
                'key'      => 'virtual_payment',
                'describe' => '小程序虚拟支付配置',
                'values'   => [
                    'app_key'  => '',
                    'offer_id' => '',
                    'token'    => '',
                    'env'      => 0,   // 0 正式 / 1 沙箱
                ],
            ],
        ];
    }

    /**
     * 写入某小程序的默认设置（移植自 php admin/model/Setting.php::insertDefault）
     *
     * @param int         $wxappId
     * @param string|null $storeName
     * @return int 写入行数
     */
    public static function insertDefault($wxappId, $storeName = null)
    {
        $now  = time();
        $rows = [];
        foreach (self::defaultData($storeName) as $item) {
            $rows[] = [
                'key'         => $item['key'],
                'describe'    => $item['describe'],
                'values'      => json_encode($item['values'], JSON_UNESCAPED_UNICODE),
                'wxapp_id'    => (int)$wxappId,
                'update_time' => $now,
            ];
        }
        return Db::name('setting')->insertAll($rows);
    }
}
