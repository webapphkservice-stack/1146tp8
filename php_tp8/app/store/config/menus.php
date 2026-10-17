<?php
/**
 * 后台菜单配置
 *    'home' => [
 *       'name' => '首页',                // 菜单名称
 *       'icon' => 'icon-home',          // 图标 (class)
 *       'index' => 'index/index',         // 链接
 *     ],
 */
return [
    'index' => [
        'name' => '首页',
        'icon' => 'icon-home',
        'index' => 'index/index',
    ],
    'store' => [
        'name' => '管理员',
        'icon' => 'icon-guanliyuan',
        'index' => 'store.user/index',
        'submenu' => [
            [
                'name' => '管理员列表',
                'index' => 'store.user/index',
                'uris' => [
                    'store.user/index',
                    'store.user/add',
                    'store.user/edit',
                    'store.user/delete',
                ],
            ],
            [
                'name' => '角色管理',
                'index' => 'store.role/index',
                'uris' => [
                    'store.role/index',
                    'store.role/add',
                    'store.role/edit',
                    'store.role/delete',
                ],
            ],
        ]
    ],
    'goods' => [
        'name' => '商品管理',
        'icon' => 'icon-goods',
        'index' => 'goods/index',
        'submenu' => [
            [
                'name' => '商品列表',
                'index' => 'goods/index',
                'uris' => [
                    'goods/index',
                    'goods/add',
                    'goods/edit',
                    'goods/copy'
                ],
            ],
            [
                'name' => '商品分类',
                'index' => 'goods.category/index',
                'uris' => [
                    'goods.category/index',
                    'goods.category/add',
                    'goods.category/edit',
                ],
            ],
            [
                'name' => '商品评价',
                'index' => 'goods.comment/index',
                'uris' => [
                    'goods.comment/index',
                    'goods.comment/detail',
                ],
            ]
        ],
    ],
    'order' => [
        'name' => '订单管理',
        'icon' => 'icon-order',
        'index' => 'order/all_list',
        'submenu' => [
            [
                'name' => '全部订单',
                'index' => 'order/all_list',
            ],
            [
                'name' => '待发货',
                'index' => 'order/delivery_list',
            ],
            [
                'name' => '待收货',
                'index' => 'order/receipt_list',
            ],
            [
                'name' => '待付款',
                'index' => 'order/pay_list',
            ],
            [
                'name' => '已完成',
                'index' => 'order/complete_list',

            ],
            [
                'name' => '已取消',
                'index' => 'order/cancel_list',
            ],
            [
                'name' => '虚拟订单',
                'index' => 'order/virtual_list',
            ],
            [
                'name' => '售后管理',
                'index' => 'order.refund/index',
                'uris' => [
                    'order.refund/index',
                    'order.refund/detail',
                ]
            ],
        ]
    ],
    'user' => [
        'name' => '用户管理',
        'icon' => 'icon-user',
        'index' => 'user/index',
        'submenu' => [
            [
                'name' => '用户列表',
                'index' => 'user/index',
            ],
            [
                'name' => '会员等级',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '等级管理',
                        'index' => 'user.grade/index',
                        'uris' => [
                            'user.grade/index',
                            'user.grade/add',
                            'user.grade/edit',
                            'user.grade/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '余额记录',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '充值记录',
                        'index' => 'user.recharge/order',
                    ],
                    [
                        'name' => '余额明细',
                        'index' => 'user.balance/log',
                    ],
                ]
            ],
        ]
    ],
    'shop' => [
        'name' => '门店管理',
        'icon' => 'icon-shop',
        'index' => 'shop/index',
        'submenu' => [
            [
                'name' => '门店管理',
                'active' => true,
                'index' => 'shop/index',
                'submenu' => [
                    [
                        'name' => '门店列表',
                        'index' => 'shop/index',
                        'uris' => [
                            'shop/index',
                            'shop/add',
                            'shop/edit',
                        ]
                    ],
                    [
                        'name' => '店员管理',
                        'index' => 'shop.clerk/index',
                        'uris' => [
                            'shop.clerk/index',
                            'shop.clerk/add',
                            'shop.clerk/edit',
                        ]
                    ],
                ]
            ],
            [
                'name' => '订单核销记录',
                'index' => 'shop.order/index',
            ]
        ]
    ],
    'regionagent' => [
        'name' => '区域代理',
        'icon' => 'icon-marketing',
        'color' => '#fcb500',
        'index' => 'regionagent/index',
        'submenu' => [
            [
                'name' => '代理商审核',
                'index' => 'regionagent/index',
            ],
            [
                'name' => '代理商费用',
                'index' => 'regionagent/fee',
            ],
            [
                'name' => '申请支付订单',
                'index' => 'regionagent/applyOrders',
            ],
            [
                'name' => '代理商订单',
                'index' => 'regionagent/orders',
            ],
            [
                'name' => '税务设置',
                'index' => 'regionagent/tax',
            ],
            [
                'name' => '申请数量',
                'index' => 'regionagent/quantity',
            ],
            [
                'name' => '代理商转账',
                'index' => 'regionagent/withdrawList',
            ],
        ],
    ],
    'water_order' => [
        'name' => '送水配送',
        'icon' => '外送1.png',
        'index' => 'water_order/index',
        'submenu' => [
            [
                'name' => '送水订单',
                'index' => 'water_order/index',
                'uris' => [
                    'water_order/index',
                    'water_order/accept',
                    'water_order/finish',
                    'water_order/cancel',
                    'water_order/auditCancel',
                    'water_order/clerks',
                    'water_order/recycleBucket',
                    'water_order/refundDeposit',
                ]
            ],
            [
                'name' => '水票发放',
                'index' => 'water_ticket/index',
                'uris' => [
                    'water_ticket/index',
                ]
            ],
        ],
    ],
    'content' => [
        'name' => '内容管理',
        'icon' => 'icon-wenzhang',
        'index' => 'content.article/index',
        'submenu' => [
            [
                'name' => '文章管理',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '文章列表',
                        'index' => 'content.article/index',
                        'uris' => [
                            'content.article/index',
                            'content.article/add',
                            'content.article/edit',
                        ]
                    ],
                    [
                        'name' => '文章分类',
                        'index' => 'content.article.category/index',
                        'uris' => [
                            'content.article.category/index',
                            'content.article.category/add',
                            'content.article.category/edit',
                        ]
                    ],
                ]
            ],
            [
                'name' => '文件库管理',
                'submenu' => [
                    [
                        'name' => '文件分组',
                        'index' => 'content.files.group/index',
                        'uris' => [
                            'content.files.group/index',
                            'content.files.group/add',
                            'content.files.group/edit',
                        ]
                    ],
                    [
                        'name' => '文件列表',
                        'index' => 'content.files/index'
                    ],
                    [
                        'name' => '回收站',
                        'index' => 'content.files/recycle',
                    ],
                ]
            ],
        ]
    ],
    'market' => [
        'name' => '营销管理',
        'icon' => 'icon-marketing',
        'index' => 'market.coupon/index',
        'submenu' => [
            [
                'name' => '优惠券',
//                'active' => true,
                'submenu' => [
                    [
                        'name' => '优惠券列表',
                        'index' => 'market.coupon/index',
                        'uris' => [
                            'market.coupon/index',
                            'market.coupon/add',
                            'market.coupon/edit',
                        ]
                    ],
                    [
                        'name' => '领取记录',
                        'index' => 'market.coupon/receive'
                    ],
                ]
            ],
            [
                'name' => '用户充值',
                'submenu' => [
                    [
                        'name' => '充值套餐',
                        'index' => 'market.recharge.plan/index',
                        'uris' => [
                            'market.recharge.plan/index',
                            'market.recharge.plan/add',
                            'market.recharge.plan/edit',
                        ]
                    ],
                    [
                        'name' => '充值设置',
                        'index' => 'market.recharge/setting'
                    ],
                ]
            ],
            [
                'name' => 'VIP会员',
                'submenu' => [
                    [
                        'name' => '套餐管理',
                        'index' => 'market.vip_plan/index',
                        'uris' => [
                            'market.vip_plan/index',
                            'market.vip_plan/add',
                            'market.vip_plan/edit',
                        ]
                    ],
                    [
                        'name' => 'VIP设置',
                        'index' => 'market.vip/setting',
                    ],
                    [
                        'name' => 'VIP订单',
                        'index' => 'market.vip_order/index',
                    ],
                ]
            ],
            [
                'name' => '积分管理',
                'submenu' => [
                    [
                        'name' => '积分设置',
                        'index' => 'market.points/setting'
                    ],
                    [
                        'name' => '积分明细',
                        'index' => 'market.points/log'
                    ],
                ]
            ],
            [
                'name' => '消息推送',
                'submenu' => [
                    [
                        'name' => '发送消息',
                        'index' => 'market.push/send',
                    ],
                    [
                        'name' => '活跃用户',
                        'index' => 'market.push/user',
                    ],
//                    [
//                        'name' => '发送日志',
//                        'index' => 'market.push/log',
//                    ],
                ]
            ],
            [
                'name' => '满额包邮',
                'index' => 'market.basic/full_free',
            ],
        ],
    ],
    'statistics' => [
        'name' => '数据统计',
        'icon' => 'icon-qushitu',
        'index' => 'statistics.data/index',
    ],
    'wxapp' => [
        'name' => '小程序',
        'icon' => 'icon-wxapp',
        'color' => '#36b313',
        'index' => 'wxapp/setting',
        'submenu' => [
            [
                'name' => '小程序设置',
                'index' => 'wxapp/setting',
            ],
            [
                'name' => '页面管理',
                'active' => true,
                'submenu' => [
                    [
                        'name' => '页面设计',
                        'index' => 'wxapp.page/index',
                        'uris' => [
                            'wxapp.page/index',
                            'wxapp.page/add',
                            'wxapp.page/edit',
                        ]
                    ],
                    [
                        'name' => '图标管理',
                        'index' => 'wxapp.page/icon',
                        'uris' => [
                            'wxapp.page/icon',
                        ]
                    ],
                    [
                        'name' => '分类模板',
                        'index' => 'wxapp.page/category'
                    ],
                    [
                        'name' => '页面链接',
                        'index' => 'wxapp.page/links'
                    ]
                ]
            ],
            [
                'name' => '订阅消息',
                'index' => 'wxapp.submsg/index',
                'uris' => [
                    'wxapp.submsg/index',
                ]
            ],
            [
                'name' => '帮助中心',
                'index' => 'wxapp.help/index',
                'uris' => [
                    'wxapp.help/index',
                    'wxapp.help/add',
                    'wxapp.help/edit'
                ]
            ],
        ],
    ],
    'apps' => [
        'name' => '应用中心',
        'icon' => 'icon-application',
        'is_svg' => true,   // 多色图标
        'index' => 'apps.dealer.apply/index',
        'submenu' => [
            [
                'name' => '分销中心',
                'submenu' => [
                    [
                        'name' => '入驻申请',
                        'index' => 'apps.dealer.apply/index',
                    ],
                    [
                        'name' => '分销商用户',
                        'index' => 'apps.dealer.user/index',
                        'uris' => [
                            'apps.dealer.user/index',
                            'apps.dealer.user/edit',
                            'apps.dealer.user/fans',
                        ]
                    ],
                    [
                        'name' => '分销订单',
                        'index' => 'apps.dealer.order/index',
                    ],
                    [
                        'name' => '提现申请',
                        'index' => 'apps.dealer.withdraw/index',
                    ],
                    [
                        'name' => '分销设置',
                        'index' => 'apps.dealer.setting/index',
                    ],
                    [
                        'name' => '分销海报',
                        'index' => 'apps.dealer.setting/qrcode',
                    ],
                ]
            ],
            [
                'name' => '每日任务',
                'index' => 'apps.dealer.task/index',
                'uris' => [
                    'apps.dealer.task/index',
                    'apps.dealer.task/add',
                    'apps.dealer.task/edit',
                    'apps.dealer.task/delete',
                ]
            ],
            [
                'name' => '秒杀管理',
                'submenu' => [
                    [
                        'name' => '活动会场',
                        'index' => 'apps.sharp.active/index',
                        'uris' => [
                            'apps.sharp.active/index',
                            'apps.sharp.active/add',
                            'apps.sharp.active/state',
                            'apps.sharp.active/delete',
                        ]
                    ],
                    [
                        'name' => '场次管理',
                        'index' => 'apps.sharp.active_time/index',
                        'uris' => [
                            'apps.sharp.active_time/index',
                            'apps.sharp.active_time/add',
                            'apps.sharp.active_time/edit',
                            'apps.sharp.active_time/state',
                            'apps.sharp.active_time/delete',
                        ]
                    ],
                    [
                        'name' => '秒杀商品',
                        'index' => 'apps.sharp.goods/index',
                        'uris' => [
                            'apps.sharp.goods/index',
                            'apps.sharp.goods/add',
                            'apps.sharp.goods/edit',
                            'apps.sharp.goods/delete',
                        ]
                    ],
                ]
            ],
            [
                'name' => '拼团管理',
                'submenu' => [
                    [
                        'name' => '商品分类',
                        'index' => 'apps.sharing.category/index',
                        'uris' => [
                            'apps.sharing.category/index',
                            'apps.sharing.category/add',
                            'apps.sharing.category/edit',
                        ]
                    ],
                    [
                        'name' => '商品列表',
                        'index' => 'apps.sharing.goods/index',
                        'uris' => [
                            'apps.sharing.goods/index',
                            'apps.sharing.goods/add',
                            'apps.sharing.goods/edit',
                            'apps.sharing.goods/copy',
                            'apps.sharing.goods/copy_master',
                        ]
                    ],
                    [
                        'name' => '拼单管理',
                        'index' => 'apps.sharing.active/index',
                        'uris' => [
                            'apps.sharing.active/index',
                            'apps.sharing.active/users',
                        ]
                    ],
                    [
                        'name' => '订单管理',
                        'index' => 'apps.sharing.order/index',
                        'uris' => [
                            'apps.sharing.order/index',
                            'apps.sharing.order/detail',
                            'apps.sharing.order.operate/batchdelivery'
                        ]
                    ],
                    [
                        'name' => '售后管理',
                        'index' => 'apps.sharing.order.refund/index',
                        'uris' => [
                            'apps.sharing.order.refund/index',
                            'apps.sharing.order.refund/detail',
                        ]
                    ],
                    [
                        'name' => '商品评价',
                        'index' => 'apps.sharing.comment/index',
                        'uris' => [
                            'apps.sharing.comment/index',
                            'apps.sharing.comment/detail',
                        ],
                    ],
                    [
                        'name' => '拼团设置',
                        'index' => 'apps.sharing.setting/index'
                    ]
                ]
            ],
            [
                'name' => '砍价活动',
                'index' => 'apps.bargain.active/index',
                'submenu' => [
                    [
                        'name' => '活动列表',
                        'index' => 'apps.bargain.active/index',
                        'uris' => [
                            'apps.bargain.active/index',
                            'apps.bargain.active/add',
                            'apps.bargain.active/edit',
                            'apps.bargain.active/delete',
                        ],
                    ],
                    [
                        'name' => '砍价记录',
                        'index' => 'apps.bargain.task/index',
                        'uris' => [
                            'apps.bargain.task/index',
                            'apps.bargain.task/delete',
                            'apps.bargain.task/help',
                        ],
                    ],
                    [
                        'name' => '砍价设置',
                        'index' => 'apps.bargain.setting/index',
                    ]
                ]
            ],
            [
                'name' => '整点秒杀',
                'index' => 'apps.sharp.goods/index',
                'submenu' => [
                    [
                        'name' => '秒杀商品',
                        'index' => 'apps.sharp.goods/index',
                        'uris' => [
                            'apps.sharp.goods/index',
                            'apps.sharp.goods/add',
                            'apps.sharp.goods/edit',
                            'apps.sharp.goods/delete',
                        ],
                    ],
                    [
                        'name' => '活动会场',
                        'index' => 'apps.sharp.active/index',
                        'uris' => [
                            'apps.sharp.active/index',
                            'apps.sharp.active/add',
                            'apps.sharp.active/state',
                            'apps.sharp.active/delete',

                            'apps.sharp.active_time/index',
                            'apps.sharp.active_time/add',
                            'apps.sharp.active_time/edit',
                            'apps.sharp.active_time/state',
                            'apps.sharp.active_time/delete',
                        ],
                    ],
                    [
                        'name' => '基础设置',
                        'index' => 'apps.sharp.setting/index',
                    ]
                ]
            ],
            [
                'name' => '小程序直播',
                'index' => 'apps.live.room/index',
                'submenu' => [
                    [
                        'name' => '直播间管理',
                        'index' => 'apps.live.room/index',
                    ],
                ]
            ],
        ]
    ],
    'transfer' => [
        'name' => '商家转账',
        'icon' => 'icon-rmb',
        'index' => 'transfer/scenes',
        'submenu' => [
            [
                'name' => '转账场景',
                'index' => 'transfer/scenes',
                'uris' => [
                    'transfer/scenes',
                    'transfer/scene_edit',
                ]
            ],
            [
                'name' => '转账接口配置',
                'index' => 'transfer/setting'
            ],
            [
                'name' => '转账记录',
                'index' => 'transfer/records'
            ],
        ],
    ],
    'coin' => [
        'name' => '代币管理',
        'icon' => 'icon-credit-card',
        'index' => 'coin/index',
        'submenu' => [
            [
                'name' => '代币订单',
                'index' => 'coin/index',
            ],
            [
                'name' => '虚拟支付设置',
                'index' => 'coin/setting',
                'uris' => [
                    'coin/setting',
                ],
            ],
        ],
    ],
    'setting' => [
        'name' => '设置',
        'icon' => 'icon-setting',
        'index' => 'setting/store',
        'submenu' => [
            [
                'name' => '商城设置',
                'index' => 'setting/store',
            ],
            [
                'name' => '交易设置',
                'index' => 'setting/trade',
            ],
            [
                'name' => '运费模板',
                'index' => 'setting.delivery/index',
                'uris' => [
                    'setting.delivery/index',
                    'setting.delivery/add',
                    'setting.delivery/edit',
                ],
            ],
            [
                'name' => '物流公司',
                'index' => 'setting.express/index',
                'uris' => [
                    'setting.express/index',
                    'setting.express/add',
                    'setting.express/edit',
                    'setting.express/delete',
                    'setting.express/company',
                ],
            ],
            [
                'name' => '短信通知',
                'index' => 'setting/sms'
            ],
            //    [
            //        'name' => '模板消息',
            //        'index' => 'setting/tplmsg',
            //        'uris' => [
            //            'setting/tplmsg',
            //            'setting.help/tplmsg'
            //
            //        ],
            //    ],
            [
                'name' => '退货地址',
                'index' => 'setting.address/index',
                'uris' => [
                    'setting.address/index',
                    'setting.address/add',
                    'setting.address/edit',
                ],
            ],
            [
                'name' => '上传设置',
                'index' => 'setting/storage',
            ],
            [
                'name' => '小票打印机',
                'submenu' => [
                    [
                        'name' => '打印机管理',
                        'index' => 'setting.printer/index',
                        'uris' => [
                            'setting.printer/index',
                            'setting.printer/add',
                            'setting.printer/edit'
                        ]
                    ],
                    [
                        'name' => '打印设置',
                        'index' => 'setting/printer'
                    ]
                ]
            ],
            [
                'name' => '其他',
                'submenu' => [
                    [
                        'name' => '清理缓存',
                        'index' => 'setting.cache/clear'
                    ]
                ]
            ]
        ],
    ],
    'flight' => [
        'name' => '航班管理',
        'icon' => 'icon-plane',
        'index' => 'flight/index',
        'submenu' => [
            [
                'name' => '默认航班',
                'index' => 'flight/index',
                'uris' => [
                    'flight/index',
                    'flight/add',
                    'flight/edit',
                    'flight/preview',
                ]
            ],
            [
                'name' => '航班接口配置',
                'index' => 'flight/setting'
            ],
        ],
    ],
    'delivery' => [
        'name' => '本地外送',
        'icon' => 'icon-motorcycle',
        'index' => 'delivery/shop',
        'submenu' => [
            [
                'name' => '店铺管理',
                'index' => 'delivery/shop',
                'uris' => [
                    'delivery/shop',
                    'delivery/shopAdd',
                    'delivery/shopEdit',
                    'delivery/index',
                ]
            ],
            [
                'name' => '外送商品',
                'index' => 'delivery/index',
                'uris' => [
                    'delivery/index',
                    'delivery/add',
                    'delivery/edit',
                ]
            ],
            [
                'name' => '外送设置',
                'index' => 'delivery/setting'
            ],
            [
                'name' => '外送订单',
                'index' => 'delivery/order',
                'uris' => [
                    'delivery/order',
                ]
            ],
        ],
    ],
    'taxi' => [
        'name' => '出租车',
        'icon' => 'icon-car',
        'index' => 'taxi/setting',
        'submenu' => [
            [
                'name' => '出租车订单',
                'index' => 'taxi/order',
                'uris' => ['taxi/order', 'taxi/orderState']
            ],
            [
                'name' => '出租车设置',
                'index' => 'taxi/setting',
                'uris' => ['taxi/setting']
            ],
        ],
    ],
    'stripe' => [
        'name' => 'Stripe 支付',
        'icon' => 'icon-credit-card',
        'index' => 'stripe/setting',
        'submenu' => [
            [
                'name' => 'Stripe 配置',
                'index' => 'stripe/setting',
                'uris' => ['stripe/setting']
            ],
        ],
    ],
];
