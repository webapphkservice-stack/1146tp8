<?php

namespace app\store\controller;

use app\store\controller\Controller;
use app\common\library\wechat\WxPay;
use app\common\model\Wxapp as WxappModel;
use app\common\service\Order as OrderService;
use think\facade\Db;

/**
 * 区域代理商管理
 * Class Regionagent
 * @package app\store\controller
 */
class Regionagent extends Controller
{
    /**
     * 当前分组
     * @var string
     */
    protected $group = 'regionagent';

    /**
     * 获取分组名称
     * @return string
     */
    protected function getGroup()
    {
        return '区域代理';
    }

    /**
     * 初始化代理商的 current_money 字段
     */
    protected function initializeCurrentMoney()
    {
        // 获取所有已通过审核的代理商
        $allAgents = Db::name('region_agent')
            ->where('status', 103)
            ->select();
        
        foreach ($allAgents as $agent) {
            // 如果 current_money 为空或为 NULL，则初始化
            $currentMoney = Db::name('region_agent')->where('id', $agent['id'])->value('current_money');
            if ($currentMoney === null || $currentMoney === '' || !isset($agent['current_money'])) {
                $region = Db::name('region')->where('id', $agent['region_id'])->find();
                if (!$region) continue;
                
                $level = $region['level'];
                $agentLevel = $level;
                
                // 计算该代理商的订单收入总和
                $query = Db::name('order')
                    ->alias('o')
                    ->join('order_address oa', 'o.order_id = oa.order_id', 'left')
                    ->where('o.is_delete', 0)
                    ->where('o.pay_status', 20)
                    ->where('o.is_region_agent', 1);
                
                // 根据代理级别匹配订单
                if ($agentLevel == 1) {
                    $query->where('oa.province_id', $agent['region_id'])
                          ->where('o.region_agent_first', '>', 0);
                } elseif ($agentLevel == 2) {
                    $query->where('oa.city_id', $agent['region_id'])
                          ->where('o.region_agent_second', '>', 0);
                } elseif ($agentLevel == 3) {
                    $query->where('oa.region_id', $agent['region_id'])
                          ->where('o.region_agent_third', '>', 0);
                }
                
                // 计算订单收入总和
                $incomeSum = 0;
                if ($agentLevel == 1) {
                    $incomeSum = $query->sum('o.region_agent_first');
                } elseif ($agentLevel == 2) {
                    $incomeSum = $query->sum('o.region_agent_second');
                } elseif ($agentLevel == 3) {
                    $incomeSum = $query->sum('o.region_agent_third');
                }
                
                // 更新代理商的 current_money 字段
                Db::name('region_agent')
                    ->where('id', $agent['id'])
                    ->update([
                        'current_money' => floatval($incomeSum),
                        'update_time' => time()
                    ]);
                
                // 记录日志
                log_write([
                    'describe' => '初始化代理商 current_money',
                    'agent_id' => $agent['id'],
                    'level' => $agentLevel,
                    'income_sum' => floatval($incomeSum)
                ]);
            }
        }
    }

    /**
     * 代理商审核列表
     */
    public function index()
    {
        // 本地 DB 可能没 mybaixin_region_agent 表，降级返回空列表
        try {
        // 获取代理商列表
        $list = Db::name('region_agent')
        ->order(['status' => 'asc'])
        ->paginate(15, false, [
            'query' => \request()->request()
        ])
        ->each(function ($item, $key){
            // 获取用户信息
            $user = Db::name('user')->where('user_id', $item['user_id'])->find();
            // 昵称后面添加用户ID
            $item['nick_name'] = $user ? ($user['nickName'] . '(ID:' . $item['user_id'] . ')') : ('未知用户(ID:' . $item['user_id'] . ')');
            $item['region_text'] = Db::name('region')->where('id', $item['region_id'])->value('merger_name');
            // 状态文本：101-待审核（未支付），102-已支付待审核，103-已通过，104-已驳回
            if ($item['status'] == 101) {
                $item['status_text'] = '待审核';
            } elseif ($item['status'] == 102) {
                $item['status_text'] = $item['is_paid'] == 1 ? '已支付待审核' : '审核中';
            } elseif ($item['status'] == 103) {
                $item['status_text'] = '已通过';
            } elseif ($item['status'] == 104) {
                $item['status_text'] = '已驳回';
            } else {
                $item['status_text'] = '-';
            }
            $level = Db::name('region')->where('id', $item['region_id'])->value('level');
            $item['level_text'] = $level == 1 ? '省代' : ($level == 2 ? '市代' : ($level == 3 ? '区代' : '-'));
            $item['reason'] = $item['reject_reason'] ;
            // 合约日期处理（兼容 int 时间戳与 datetime 字符串两种存储）
            $item['contract_date'] = !empty($item['contract_date'])
                ? (is_numeric($item['contract_date']) ? date('Y-m-d', (int)$item['contract_date']) : mb_substr((string)$item['contract_date'], 0, 10))
                : '';
            
            // 从支付订单表获取支付状态（根据区域ID查询）
            $payOrder = Db::name('region_agent_pay_order')
                ->where('user_id', $item['user_id'])
                ->where('region_id', $item['region_id'])
                ->order('create_time', 'desc')
                ->find();
            
            // 支付状态处理
            $item['is_paid'] = $payOrder ? $payOrder['is_paid'] : 0;
            $item['is_paid_text'] = ($payOrder && $payOrder['is_paid'] == 1) ? '已支付' : '未支付';
            $item['pay_time_text'] = ($payOrder && !empty($payOrder['pay_time'])) ? date('Y-m-d H:i:s', $payOrder['pay_time']) : '';
            $item['order_sn'] = $payOrder ? $payOrder['order_sn'] : '';
            
            // 计算已支付金额
            $payAmount = Db::name('region_agent_pay_order')
                ->where('user_id', $item['user_id'])
                ->where('is_paid', 1)
                ->sum('amount');
            $item['pay_amount'] = floatval($payAmount);
            
            // 计算当前代理商的订单收入总和（实时计算）
            $incomeSum = 0;
            if ($item['status'] == 103 && $level > 0) {
                $query = Db::name('order')
                    ->alias('o')
                    ->join('order_address oa', 'o.order_id = oa.order_id', 'left')
                    ->where('o.is_delete', 0)
                    ->where('o.pay_status', 20)
                    ->where('o.is_region_agent', 1);
                
                // 根据代理级别匹配订单
                if ($level == 1) {
                    $query->where('oa.province_id', $item['region_id'])
                          ->where('o.region_agent_first', '>', 0);
                    $incomeSum = $query->sum('o.region_agent_first');
                } elseif ($level == 2) {
                    $query->where('oa.city_id', $item['region_id'])
                          ->where('o.region_agent_second', '>', 0);
                    $incomeSum = $query->sum('o.region_agent_second');
                } elseif ($level == 3) {
                    $query->where('oa.region_id', $item['region_id'])
                          ->where('o.region_agent_third', '>', 0);
                    $incomeSum = $query->sum('o.region_agent_third');
                }
            }
            $item['current_money'] = floatval($incomeSum);
            
            // 计算已提现金额：从代理商提现记录中获取已完成（status=3,4,5）的实际到账金额总和
            $withdrawn = Db::name('region_agent_withdraw')
                ->where('user_id', $item['user_id'])
                ->whereIn('status', [3, 4, 5]) // 已微信打款、已完成、银行转账完成
                ->sum('actual_amount');
            $item['withdrawn'] = floatval($withdrawn);
            
            // 可提现金额 = 订单收入 - 已提现金额
            $item['withdrawable'] = floatval($incomeSum) - floatval($withdrawn);
            if ($item['withdrawable'] < 0) {
                $item['withdrawable'] = 0; // 防止负数
            }
            
            // 获取用户微信信息（open_id）
            $user = Db::name('user')->where('user_id', $item['user_id'])->find();
            $item['open_id'] = $user ? ($user['open_id'] ?? '') : '';
            $item['has_withdraw'] = false; // 标记是否有提现记录
            
            return $item;
        });
        
        // 从region_agent_fee表获取费用设置
        $feeSettings = Db::name('region_agent_fee')->where('id', '>', 0)->find();
        // 如果没有数据，插入默认值
        if (!$feeSettings) {
            $now = time();
            Db::name('region_agent_fee')->insert([
                'province_fee' => 5000,
                'city_fee' => 3000,
                'district_fee' => 1500,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $feeSettings = Db::name('region_agent_fee')->where('id', '>', 0)->find();
        }
        
        // 确保返回的费用是数字类型
        $feeSettings = [
            'province_fee' => (float)$feeSettings['province_fee'],
            'city_fee' => (float)$feeSettings['city_fee'],
            'district_fee' => (float)$feeSettings['district_fee']
        ];
        
        // 从region_agent_tax_rate表获取税率设置
        $taxRateSettings = Db::name('region_agent_tax_rate')->where('id', '>', 0)->find();
        // 如果没有数据，插入默认值
        if (!$taxRateSettings) {
            $now = time();
            Db::name('region_agent_tax_rate')->insert([
                'withdraw_tax_rate' => 0.00,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $taxRateSettings = Db::name('region_agent_tax_rate')->where('id', '>', 0)->find();
        }
        
        // 获取代理商可申请数量设置
        $quantitySettings = Db::name('region_agent_quantity')->where('id', '>', 0)->find();
        if (!$quantitySettings) {
            $now = time();
            Db::name('region_agent_quantity')->insert([
                'province_quantity' => 34,
                'city_quantity' => 100,
                'district_quantity' => 200,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $quantitySettings = Db::name('region_agent_quantity')->where('id', '>', 0)->find();
        }
        
        $quantitySettings = [
            'province_quantity' => (int)$quantitySettings['province_quantity'],
            'city_quantity' => (int)$quantitySettings['city_quantity'],
            'district_quantity' => (int)$quantitySettings['district_quantity']
        ];

        // 计算所有已通过审核代理商的总订单收入、总已提现金额和总可提现金额
        $total_current_money = 0;
        $total_withdrawn = 0;
        $total_withdrawable = 0;
        if (!empty($list->items())) {
            foreach ($list->items() as $agent) {
                $total_current_money += floatval($agent['current_money']);
                $total_withdrawn += floatval($agent['withdrawn']);
                $total_withdrawable += floatval($agent['withdrawable']);
            }
        }

        return $this->fetch('index', [
            'list' => $list,
            'feeSettings' => $feeSettings,
            'taxRateSettings' => $taxRateSettings,
            'quantitySettings' => $quantitySettings,
            'total_current_money' => $total_current_money,
            'total_withdrawn' => $total_withdrawn,
            'total_withdrawable' => $total_withdrawable
        ]);
        } catch (\Throwable $e) {
            \think\facade\Log::warning('Regionagent::index 降级：' . $e->getMessage());
            return $this->fetch('index', [
                'list' => new \think\paginator\driver\Bootstrap([], 15, 1, 0),
                'feeSettings' => [],
                'taxRateSettings' => [],
                'quantitySettings' => [],
                'total_current_money' => 0,
                'total_withdrawn' => 0,
                'total_withdrawable' => 0
            ]);
        }
    }

    /**
     * 代理商费用设置
     */
    public function fee()
    {
        // 从region_agent_fee表获取费用设置
        $feeSettings = Db::name('region_agent_fee')->where('id', '>', 0)->find();
        // 如果没有数据，插入默认值
        if (!$feeSettings) {
            $now = time();
            Db::name('region_agent_fee')->insert([
                'province_fee' => 5000,
                'city_fee' => 3000,
                'district_fee' => 1500,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $feeSettings = Db::name('region_agent_fee')->where('id', '>', 0)->find();
        }
        
        // 确保返回的费用是数字类型
        $feeSettings = [
            'province_fee' => (float)$feeSettings['province_fee'],
            'city_fee' => (float)$feeSettings['city_fee'],
            'district_fee' => (float)$feeSettings['district_fee']
        ];
        
        return $this->fetch('fee', [
            'feeSettings' => $feeSettings
        ]);
    }

    /**
     * 代理订单列表
     */
    public function orders()
    {
        // 获取筛选条件
        $level = input('level/d', 0);
        $delivery_status = input('delivery_status/s', '');
        $receipt_status = input('receipt_status/s', '');
        $is_settle = input('is_settle/s', '');
        $page = input('page/d', 1);
        $limit = 10; // 每页显示10条
        
        // 查询所有已通过的代理商
        $agents = Db::name('region_agent')
            ->where('status', 103)
            ->select();
        
        // 添加调试日志
        $debugInfo = [
            'level' => $level,
            'delivery_status' => $delivery_status,
            'receipt_status' => $receipt_status,
            'is_settle' => $is_settle,
            'agents_count' => count($agents),
            'time' => date('Y-m-d H:i:s')
        ];
        
        $allOrders = [];
        
        foreach ($agents as $agent) {
            // 获取代理商信息
            $agentUser = Db::name('user')
                ->where('user_id', $agent['user_id'])
                ->find();
            
            $region = Db::name('region')
                ->where('id', $agent['region_id'])
                ->find();
            
            if (!$region) continue;
            
            $agentLevel = $region['level'];
            
            // 如果指定了级别筛选，跳过不匹配的
            if ($level > 0 && $agentLevel != $level) continue;
            
            // 查询代理商的订单
            $query = Db::name('order')
                ->alias('o')
                ->join('order_address oa', 'o.order_id = oa.order_id', 'left')
                ->join('user u', 'o.user_id = u.user_id', 'left')
                ->where('o.is_delete', 0)
                ->where('o.is_region_agent', 1)
                ->where('o.pay_status', 20); // 只查询已付款订单
            
            // 根据代理级别匹配订单
            if ($agentLevel == 1) {
                $query->where('oa.province_id', $agent['region_id'])
                      ->where('o.region_agent_first', '>', 0);
            } elseif ($agentLevel == 2) {
                $query->where('oa.city_id', $agent['region_id'])
                      ->where('o.region_agent_second', '>', 0);
            } elseif ($agentLevel == 3) {
                $query->where('oa.region_id', $agent['region_id'])
                      ->where('o.region_agent_third', '>', 0);
            }
            
            // 发货状态筛选
            if ($delivery_status !== '' && $delivery_status !== null) {
                $query->where('o.delivery_status', $delivery_status);
            }
            
            // 收货状态筛选
            if ($receipt_status !== '' && $receipt_status !== null) {
                $query->where('o.receipt_status', $receipt_status);
            }
            
            // 结算状态筛选
            if ($is_settle !== '' && $is_settle !== null) {
                $query->where('o.region_agent_is_settle', $is_settle);
            }
            
            $orders = $query->field('o.*, oa.province_id, oa.city_id, oa.region_id, u.nickName')
                ->order('o.create_time', 'desc')
                ->select();
            
            foreach ($orders as &$order) {
                // 格式化数据
                $order['agent_name'] = $agentUser['nickName'] ?? '未知';
                $order['agent_user_id'] = $agent['user_id'];
                $order['agent_level'] = $agentLevel;
                $order['agent_level_text'] = $agentLevel == 1 ? '省代' : ($agentLevel == 2 ? '市代' : '区代');
                $order['region_name'] = $region['merger_name'] ?? '未知';
                
                // 订单状态
                $order['pay_status_text'] = $order['pay_status'] == 20 ? '已付款' : '未付款';
                $order['order_status_text'] = $order['order_status'] == 10 ? '待付款' : 
                                              ($order['order_status'] == 20 ? '待发货' : 
                                              ($order['order_status'] == 30 ? '待收货' : 
                                              ($order['order_status'] == 40 ? '已完成' : '已取消')));
                
                // 发货状态
                $order['delivery_status_text'] = $order['delivery_status'] == 1 ? '已发货' : '未发货';
                
                // 收货状态
                $order['receipt_status_text'] = $order['receipt_status'] == 1 ? '已收货' : '未收货';
                
                // 结算状态
                $order['is_settle_text'] = $order['region_agent_is_settle'] == 1 ? '已结算' : '未结算';
                
                // 收入金额
                if ($agentLevel == 1) {
                    $order['income'] = $order['region_agent_first'];
                } elseif ($agentLevel == 2) {
                    $order['income'] = $order['region_agent_second'];
                } elseif ($agentLevel == 3) {
                    $order['income'] = $order['region_agent_third'];
                } else {
                    $order['income'] = 0;
                }
                
                $allOrders[] = $order;
            }
        }
        
        // 记录调试日志
        $debugInfo['total_orders'] = count($allOrders);
        log_write([
            'describe' => '代理订单列表查询',
            'debug' => $debugInfo
        ]);
        
        // 计算统计信息
        $totalCount = count($allOrders);
        $totalIncome = 0;
        $settledCount = 0;
        $unsettledCount = 0;
        
        foreach ($allOrders as $order) {
            $totalIncome += $order['income'];
            if ($order['region_agent_is_settle'] == 1) {
                $settledCount++;
            } else {
                $unsettledCount++;
            }
        }
        
        // 按订单创建时间倒序排序（最新订单在最上面）
        usort($allOrders, function($a, $b) {
            return $b['create_time'] - $a['create_time'];
        });
        
        // 分页处理
        $totalPages = $totalCount > 0 ? ceil($totalCount / $limit) : 1;
        $currentPage = min(max(1, $page), max(1, $totalPages));
        $offset = ($currentPage - 1) * $limit;
        $pageOrders = array_slice($allOrders, $offset, $limit);
        
        $stats = [
            'total' => $totalCount,
            'settled' => $settledCount,
            'unsettled' => $unsettledCount,
            'total_income' => $totalIncome
        ];
        
        return $this->fetch('orders', [
            'orders' => $pageOrders,
            'stats' => $stats,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'limit' => $limit
        ]);
    }

    /**
     * 提现税率设置
     */
    public function tax()
    {
        // 从region_agent_tax_rate表获取税率设置
        $taxRateSettings = Db::name('region_agent_tax_rate')->where('id', '>', 0)->find();
        // 如果没有数据，插入默认值
        if (!$taxRateSettings) {
            $now = time();
            Db::name('region_agent_tax_rate')->insert([
                'withdraw_tax_rate' => 0.00,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $taxRateSettings = Db::name('region_agent_tax_rate')->where('id', '>', 0)->find();
        }
        
        return $this->fetch('tax', [
            'taxRateSettings' => $taxRateSettings
        ]);
    }

    /**
     * 代理商申请数量设置
     */
    public function quantity()
    {
        // 从mybaixin_region_agent_quantity表获取设置
        $quantitySettings = Db::name('region_agent_quantity')->where('id', '>', 0)->find();
        if (!$quantitySettings) {
            $now = time();
            Db::name('region_agent_quantity')->insert([
                'province_quantity' => 34,
                'city_quantity' => 100,
                'district_quantity' => 200,
                'create_time' => $now,
                'update_time' => $now
            ]);
            $quantitySettings = Db::name('region_agent_quantity')->where('id', '>', 0)->find();
        }
        
        $quantitySettings = [
            'province_quantity' => (int)$quantitySettings['province_quantity'],
            'city_quantity' => (int)$quantitySettings['city_quantity'],
            'district_quantity' => (int)$quantitySettings['district_quantity']
        ];
        
        // 获取各级别已申请的数量统计
        // 获取所有代理商申请记录，按区域级别分组统计
        $agentStats = Db::name('region_agent')
            ->alias('ra')
            ->join('region r', 'ra.region_id = r.id')
            ->whereIn('ra.status', [101, 102, 103]) // 待审核、已支付待审核、已通过
            ->field('r.level, count(*) as count')
            ->group('r.level')
            ->select();
        
        // 初始化统计数据
        $appliedStats = [
            1 => 0, // 省级已申请数量
            2 => 0, // 市级已申请数量
            3 => 0  // 区级已申请数量
        ];
        
        // 填充统计数据
        foreach ($agentStats as $stat) {
            $appliedStats[$stat['level']] = (int)$stat['count'];
        }
        
        // 计算剩余可用数量
        $availableStats = [
            'province_available' => max(0, $quantitySettings['province_quantity'] - $appliedStats[1]),
            'city_available' => max(0, $quantitySettings['city_quantity'] - $appliedStats[2]),
            'district_available' => max(0, $quantitySettings['district_quantity'] - $appliedStats[3])
        ];
        
        // 获取已申请的地区详细信息
        $appliedRegions = Db::name('region_agent')
            ->alias('ra')
            ->join('region r', 'ra.region_id = r.id')
            ->join('user u', 'ra.user_id = u.user_id')
            ->whereIn('ra.status', [101, 102, 103])
            ->field('r.id as region_id, r.name as region_name, r.level, r.merger_name, u.nickName as user_name, u.user_id, ra.status, ra.create_time')
            ->order('r.level asc, r.id asc')
            ->select();
        
        // 按级别分组
        $regionList = [
            1 => [], // 省级
            2 => [], // 市级
            3 => []  // 区级
        ];
        
        foreach ($appliedRegions as $region) {
            $regionList[$region['level']][] = $region;
        }

        return $this->fetch('quantity', [
            'quantitySettings' => $quantitySettings,
            'appliedStats' => $appliedStats,
            'availableStats' => $availableStats,
            'regionList' => $regionList
        ]);
    }

    /**
     * 申请支付订单列表
     */
    public function applyOrders()
    {
        // 获取筛选条件
        $isPaid = input('is_paid/s', '');
        $page = input('page/d', 1);
        $limit = 10;
        
        // 使用原生SQL查询，避免join中的表名被截断
        $where = '1=1';
        if ($isPaid !== '') {
            $where .= " AND po.is_paid = " . intval($isPaid);
        }
        
        // 获取总记录数
        $totalCount = Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_pay_order po WHERE {$where}")[0]['total'] ?? 0;
        $totalPages = $totalCount > 0 ? ceil($totalCount / $limit) : 1;
        $currentPage = min(max(1, $page), max(1, $totalPages));
        $offset = ($currentPage - 1) * $limit;
        
        // 原生SQL查询
        $sql = "SELECT po.*, u.nickName, r.merger_name as region_name, r.level as region_level, po.user_id as agent_id
                FROM mybaixin_region_agent_pay_order po 
                LEFT JOIN mybaixin_user u ON po.user_id = u.user_id 
                LEFT JOIN mybaixin_region_agent ra ON ra.user_id = u.user_id 
                LEFT JOIN mybaixin_region r ON r.id = ra.region_id 
                WHERE {$where} 
                ORDER BY po.create_time DESC 
                LIMIT {$offset}, {$limit}";
        
        $orders = Db::query($sql);
        
        // 格式化数据
        foreach ($orders as &$order) {
            $order['is_paid_text'] = $order['is_paid'] == 1 ? '已支付' : '未支付';
            $order['level_text'] = $order['region_level'] == 1 ? '省代' : ($order['region_level'] == 2 ? '市代' : '区代');
            $order['create_time_text'] = $order['create_time'] ? date('Y-m-d H:i:s', $order['create_time']) : '-';
            $order['pay_time_text'] = $order['pay_time'] ? date('Y-m-d H:i:s', $order['pay_time']) : '-';
        }
        
        // 统计信息
        $stats = [
            'total' => $totalCount,
            'paid' => Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_pay_order WHERE is_paid = 1")[0]['total'] ?? 0,
            'unpaid' => Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_pay_order WHERE is_paid = 0")[0]['total'] ?? 0,
            'total_amount' => Db::query("SELECT SUM(amount) as total FROM mybaixin_region_agent_pay_order")[0]['total'] ?? 0,
            'paid_amount' => Db::query("SELECT SUM(amount) as total FROM mybaixin_region_agent_pay_order WHERE is_paid = 1")[0]['total'] ?? 0,
            'unpaid_amount' => Db::query("SELECT SUM(amount) as total FROM mybaixin_region_agent_pay_order WHERE is_paid = 0")[0]['total'] ?? 0,
        ];
        
        return $this->fetch('applyOrders', [
            'orders' => $orders,
            'stats' => $stats,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'limit' => $limit
        ]);
    }

    /**
     * 获取代理商订单列表
     */
    public function getAgentOrders()
    {
        $agentId = input('agent_id/d', 0);
        
        if (!$agentId) {
            return json(['code' => 0, 'msg' => '参数错误', 'data' => []]);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agentId)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在', 'data' => []]);
        }
        
        // 获取代理区域信息
        $region = Db::name('region')->where('id', $agent['region_id'])->find();
        if (!$region) {
            return json(['code' => 0, 'msg' => '区域信息不存在', 'data' => []]);
        }
        
        $level = $region['level'];
        $regionId = $agent['region_id'];
        
        // 根据代理级别查询订单
        $query = Db::name('order')
            ->alias('o')
            ->join('order_address oa', 'o.order_id = oa.order_id', 'left')
            ->where('o.is_delete', 0)
            ->where('o.is_region_agent', 1)
            ->where('o.pay_status', 20); // 只查询已支付的订单
        
        // 根据代理级别匹配不同的区域ID和分润字段
        if ($level == 1) {
            $query->where('oa.province_id', $regionId)
                  ->where('o.region_agent_first', '>', 0);
        } elseif ($level == 2) {
            $query->where('oa.city_id', $regionId)
                  ->where('o.region_agent_second', '>', 0);
        } elseif ($level == 3) {
            $query->where('oa.region_id', $regionId)
                  ->where('o.region_agent_third', '>', 0);
        }
        
        $orders = $query->field('o.order_id, o.order_no, o.total_price, o.pay_price, o.pay_status, o.order_status, o.create_time, o.region_agent_first, o.region_agent_second, o.region_agent_third, o.region_agent_is_settle, oa.name, oa.phone')
            ->order('o.create_time desc')
            ->limit(50)
            ->select();
        
        // 格式化订单数据
        foreach ($orders as &$order) {
            $order['create_time_text'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['pay_status_text'] = $order['pay_status'] == 20 ? '已支付' : '未支付';
            $order['order_status_text'] = $order['order_status'] == 10 ? '待付款' : 
                                          ($order['order_status'] == 20 ? '待发货' : 
                                          ($order['order_status'] == 30 ? '待收货' : 
                                          ($order['order_status'] == 40 ? '已完成' : '已取消')));
            
            // 根据代理级别显示对应的分润金额
            if ($level == 1) {
                $order['income'] = (string)floatval($order['region_agent_first']);
            } elseif ($level == 2) {
                $order['income'] = (string)floatval($order['region_agent_second']);
            } elseif ($level == 3) {
                $order['income'] = (string)floatval($order['region_agent_third']);
            } else {
                $order['income'] = '0.00';
            }
            
            // 添加结算状态
            $order['is_settle_text'] = $order['region_agent_is_settle'] == 1 ? '已结算' : '未结算';
        }
        
        // 添加调试日志
        log_write([
            'describe' => '代理商订单列表返回数据',
            'agent_id' => $agentId,
            'level' => $level,
            'orders_count' => count($orders),
            'first_order' => $orders[0] ?? null
        ]);
        
        return json([
            'code' => 1,
            'msg' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * 获取代理商订单列表（支持筛选）
     */
    public function getAgentOrdersWithFilter()
    {
        $agentId = input('agent_id/d', 0);
        $filter = input('filter/s', ''); // 筛选条件：settled=已结算, unsettled=未结算, all=全部
        
        if (!$agentId) {
            return json(['code' => 0, 'msg' => '参数错误', 'data' => []]);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agentId)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在', 'data' => []]);
        }
        
        // 获取代理区域信息
        $region = Db::name('region')->where('id', $agent['region_id'])->find();
        if (!$region) {
            return json(['code' => 0, 'msg' => '区域信息不存在', 'data' => []]);
        }
        
        $level = $region['level'];
        $regionId = $agent['region_id'];
        
        // 根据代理级别查询订单
        $query = Db::name('order')
            ->alias('o')
            ->join('order_address oa', 'o.order_id = oa.order_id', 'left')
            ->where('o.is_delete', 0)
            ->where('o.is_region_agent', 1)
            ->where('o.pay_status', 20); // 只查询已支付的订单
        
        // 根据代理级别匹配不同的区域ID和分润字段
        if ($level == 1) {
            $query->where('oa.province_id', $regionId)
                  ->where('o.region_agent_first', '>', 0);
        } elseif ($level == 2) {
            $query->where('oa.city_id', $regionId)
                  ->where('o.region_agent_second', '>', 0);
        } elseif ($level == 3) {
            $query->where('oa.region_id', $regionId)
                  ->where('o.region_agent_third', '>', 0);
        }
        
        // 根据筛选条件过滤
        if ($filter == 'settled') {
            $query->where('o.region_agent_is_settle', 1);
        } elseif ($filter == 'unsettled') {
            $query->where('o.region_agent_is_settle', 0);
        }
        
        $orders = $query->field('o.order_id, o.order_no, o.total_price, o.pay_price, o.pay_status, o.order_status, o.create_time, o.region_agent_first, o.region_agent_second, o.region_agent_third, o.region_agent_is_settle, oa.name, oa.phone')
            ->order('o.create_time desc')
            ->limit(50)
            ->select();
        
        // 格式化订单数据
        foreach ($orders as &$order) {
            $order['create_time_text'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['pay_status_text'] = $order['pay_status'] == 20 ? '已支付' : '未支付';
            $order['order_status_text'] = $order['order_status'] == 10 ? '待付款' : 
                                          ($order['order_status'] == 20 ? '待发货' : 
                                          ($order['order_status'] == 30 ? '待收货' : 
                                          ($order['order_status'] == 40 ? '已完成' : '已取消')));
            
            // 根据代理级别显示对应的分润金额
            if ($level == 1) {
                $order['income'] = (string)floatval($order['region_agent_first']);
            } elseif ($level == 2) {
                $order['income'] = (string)floatval($order['region_agent_second']);
            } elseif ($level == 3) {
                $order['income'] = (string)floatval($order['region_agent_third']);
            } else {
                $order['income'] = '0.00';
            }
            
            // 添加结算状态
            $order['is_settle_text'] = $order['region_agent_is_settle'] == 1 ? '已结算' : '未结算';
        }
        
        // 添加调试日志
        log_write([
            'describe' => '代理订单列表返回数据',
            'agent_id' => $agentId,
            'level' => $level,
            'filter' => $filter,
            'orders_count' => count($orders)
        ]);
        
        // 直接返回JSON数据
        return json([
            'code' => 1,
            'msg' => 'success',
            'data' => $orders
        ]);
    }

    /**
     * 同意代理商申请
     */
    public function agree()
    {
        $agent_id = input('agent_id/d', 0);
        if (!$agent_id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agent_id)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在']);
        }

        // 允许状态 101（待审核）和 102（已支付待审核）的申请通过
        if (!in_array($agent['status'], [101, 102])) {
            return json(['code' => 0, 'msg' => '只有待审核的申请可以同意']);
        }
        
        // 更新代理商状态
        $updateData = [
            'status' => 103,
            'update_time' => time()
        ];
        
        $result = Db::name('region_agent')->where('id', $agent_id)->update($updateData);
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
    }

    /**
     * 驳回代理商申请
     */
    public function refuse()
    {
        $agent_id = input('agent_id/d', 0);
        $reason = input('reason/s', '');
        
        if (!$agent_id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agent_id)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在']);
        }
        
        // 允许状态 101（待审核）和 102（已支付待审核）的申请驳回
        if (!in_array($agent['status'], [101, 102])) {
            return json(['code' => 0, 'msg' => '只有待审核的申请可以驳回']);
        }
        
        // 更新代理商状态为 104（已驳回）
        $updateData = [
            'status' => 104,
            'reject_reason' => $reason,
            'update_time' => time()
        ];
        
        $result = Db::name('region_agent')->where('id', $agent_id)->update($updateData);
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '操作成功']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
    }

    /**
     * 删除代理商申请
     */
    public function delete()
    {
        $agent_id = input('agent_id/d', 0);
        if (!$agent_id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agent_id)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在']);
        }
        
        if ($agent['status'] == 103) {
            return json(['code' => 0, 'msg' => '已通过的代理商不能删除']);
        }
        
        // 删除代理商
        $result = Db::name('region_agent')->where('id', $agent_id)->delete();
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     * 微信打款给代理商
     */
    public function wechatTransfer()
    {
        $agent_id = input('agent_id/d', 0);
        if (!$agent_id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agent_id)->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商不存在']);
        }
        
        if ($agent['status'] != 103) {
            return json(['code' => 0, 'msg' => '只有已通过的代理商可以打款']);
        }

        // 代理商表无 open_id 字段，统一从用户表获取微信信息
        $user = Db::name('user')->where('user_id', $agent['user_id'])
            ->field('user_id,open_id,real_name')->find();
        if (!$user) {
            return json(['code' => 0, 'msg' => '用户不存在']);
        }
        if (empty($user['open_id'])) {
            return json(['code' => 0, 'msg' => '代理商未绑定微信，无法打款']);
        }

        // 获取可提现金额
        $money = (float)$agent['current_money'];
        if ($money <= 0) {
            return json(['code' => 0, 'msg' => '可提现金额为0']);
        }

        // 生成打款单号并落库（先记流水，避免转账成功但无记录）
        $orderNO = OrderService::createOrderNo();
        $logId = Db::name('region_agent_withdraw_log')->insertGetId([
            'agent_id' => $agent_id,
            'money' => $money,
            'actual_money' => $money,
            'tax_money' => 0,
            'pay_type' => 10,
            'status' => 1,
            'order_sn' => $orderNO,
            'remark' => '代理商收益打款',
            'create_time' => time(),
            'update_time' => time(),
        ]);

        // 调用商家转账到零钱 V3
        try {
            $WxPay = new WxPay(WxappModel::getWxappCache($this->getWxappId()));
            $res = $WxPay->transfers(
                $logId,
                $orderNO,
                $user,
                $money,
                '代理商收益打款',
                'region_agent',
                $user['real_name'],
                ['money' => $money, 'charge' => 0, 'remark' => '代理商收益打款']
            );
        } catch (\Exception $e) {
            Db::name('region_agent_withdraw_log')->where('id', $logId)
                ->update(['status' => 3, 'audit_remark' => $e->getMessage(), 'update_time' => time()]);
            return json(['code' => 0, 'msg' => '微信打款失败：' . $e->getMessage()]);
        }

        if ($res !== true) {
            Db::name('region_agent_withdraw_log')->where('id', $logId)
                ->update(['status' => 3, 'audit_remark' => '微信返回失败', 'update_time' => time()]);
            return json(['code' => 0, 'msg' => '微信打款失败，请重试']);
        }

        // 打款已提交，扣减代理商可提现余额
        Db::name('region_agent_withdraw_log')->where('id', $logId)
            ->update(['status' => 2, 'transfer_time' => time(), 'update_time' => time()]);
        $updateResult = Db::name('region_agent')
            ->where('id', $agent_id)
            ->setDec('current_money', $money);

        if ($updateResult !== false) {
            return json(['code' => 1, 'msg' => '打款已发起，待代理商在小程序确认收款']);
        }
        return json(['code' => 0, 'msg' => '打款已发起，但代理商余额更新失败，请人工核对']);
    }

    /**
     * 设置代理商费用
     */
    public function setFee()
    {
        $data = input();
        $province_fee = isset($data['province_fee']) ? floatval($data['province_fee']) : 0;
        $city_fee = isset($data['city_fee']) ? floatval($data['city_fee']) : 0;
        $district_fee = isset($data['district_fee']) ? floatval($data['district_fee']) : 0;
        
        // 更新或插入费用设置
        $now = time();
        $result = Db::name('region_agent_fee')->where('id', '>', 0)->update([
            'province_fee' => $province_fee,
            'city_fee' => $city_fee,
            'district_fee' => $district_fee,
            'update_time' => $now
        ]);
        
        if ($result === false) {
            // 如果更新失败，尝试插入
            $result = Db::name('region_agent_fee')->insert([
                'province_fee' => $province_fee,
                'city_fee' => $city_fee,
                'district_fee' => $district_fee,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '设置成功']);
        } else {
            return json(['code' => 0, 'msg' => '设置失败']);
        }
    }

    /**
     * 设置提现税率
     */
    public function setTaxRate()
    {
        $tax_rate = input('tax_rate/f', 0);
        
        if ($tax_rate < 0 || $tax_rate > 100) {
            return json(['code' => 0, 'msg' => '税率必须在0-100之间']);
        }
        
        $now = time();
        $result = Db::name('region_agent_tax_rate')->where('id', '>', 0)->update([
            'withdraw_tax_rate' => $tax_rate,
            'update_time' => $now
        ]);
        
        if ($result === false) {
            $result = Db::name('region_agent_tax_rate')->insert([
                'withdraw_tax_rate' => $tax_rate,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '设置成功']);
        } else {
            return json(['code' => 0, 'msg' => '设置失败']);
        }
    }

    /**
     * 设置代理商可申请数量
     */
    public function setQuantity()
    {
        $data = input();
        $province_quantity = isset($data['province_quantity']) ? intval($data['province_quantity']) : 0;
        $city_quantity = isset($data['city_quantity']) ? intval($data['city_quantity']) : 0;
        $district_quantity = isset($data['district_quantity']) ? intval($data['district_quantity']) : 0;
        
        $now = time();
        $result = Db::name('region_agent_quantity')->where('id', '>', 0)->update([
            'province_quantity' => $province_quantity,
            'city_quantity' => $city_quantity,
            'district_quantity' => $district_quantity,
            'update_time' => $now
        ]);
        
        if ($result === false) {
            $result = Db::name('region_agent_quantity')->insert([
                'province_quantity' => $province_quantity,
                'city_quantity' => $city_quantity,
                'district_quantity' => $district_quantity,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '设置成功']);
        } else {
            return json(['code' => 0, 'msg' => '设置失败']);
        }
    }

    /**
     * 代理商转账记录列表
     */
    public function withdrawList()
    {
        // 获取筛选条件
        $status = input('status/s', '');
        $withdrawType = input('withdraw_type/s', '');
        $page = input('page/d', 1);
        $limit = 10;
        
        // 构建WHERE条件
        $where = 'w.id > 0';
        if ($status !== '') {
            $where .= ' AND w.status = ' . intval($status);
        }
        if ($withdrawType !== '') {
            $where .= ' AND w.withdraw_type = ' . intval($withdrawType);
        }
        
        // 获取总记录数
        $totalCount = Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_withdraw w WHERE {$where}")[0]['total'] ?? 0;
        $totalPages = $totalCount > 0 ? ceil($totalCount / $limit) : 1;
        $currentPage = min(max(1, $page), max(1, $totalPages));
        $offset = ($currentPage - 1) * $limit;
        
        // 原生SQL查询
        $sql = "SELECT w.*, u.nickName, r.merger_name as region_name, r.level as region_level 
                FROM mybaixin_region_agent_withdraw w 
                LEFT JOIN mybaixin_user u ON w.user_id = u.user_id 
                LEFT JOIN mybaixin_region_agent ra ON w.user_id = ra.user_id 
                LEFT JOIN mybaixin_region r ON ra.region_id = r.id 
                WHERE {$where} 
                ORDER BY w.create_time DESC 
                LIMIT {$offset}, {$limit}";
        
        $withdrawList = Db::query($sql);
        
        // 格式化数据
        foreach ($withdrawList as &$item) {
            // 提现方式
            $item['withdraw_type_text'] = $item['withdraw_type'] == 1 ? '微信钱包' : '银行卡';
            
            // 状态
            $statusMap = [
                0 => '待审核',
                1 => '审核通过',
                2 => '审核拒绝',
                3 => '已微信打款',
                4 => '已完成',
                5 => '银行转账完成'
            ];
            $item['status_text'] = $statusMap[$item['status']] ?? '未知';
            
            // 级别
            $item['level_text'] = $item['region_level'] == 1 ? '省代' : ($item['region_level'] == 2 ? '市代' : '区代');
            
            // 时间
            $item['apply_time_text'] = $item['apply_time'] ? date('Y-m-d H:i:s', $item['apply_time']) : '-';
            $item['audit_time_text'] = $item['audit_time'] ? date('Y-m-d H:i:s', $item['audit_time']) : '-';
            $item['complete_time_text'] = $item['complete_time'] ? date('Y-m-d H:i:s', $item['complete_time']) : '-';
            $item['create_time_text'] = $item['create_time'] ? date('Y-m-d H:i:s', $item['create_time']) : '-';
        }
        
        // 统计信息
        $stats = [
            'total' => $totalCount,
            'pending' => Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_withdraw WHERE status = 0")[0]['total'] ?? 0,
            'approved' => Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_withdraw WHERE status = 1")[0]['total'] ?? 0,
            'completed' => Db::query("SELECT COUNT(*) as total FROM mybaixin_region_agent_withdraw WHERE status = 4")[0]['total'] ?? 0,
            'total_amount' => Db::query("SELECT SUM(withdraw_amount) as total FROM mybaixin_region_agent_withdraw")[0]['total'] ?? 0,
            'total_tax' => Db::query("SELECT SUM(tax_amount) as total FROM mybaixin_region_agent_withdraw")[0]['total'] ?? 0,
            'total_actual' => Db::query("SELECT SUM(actual_amount) as total FROM mybaixin_region_agent_withdraw")[0]['total'] ?? 0,
        ];
        
        return $this->fetch('withdrawList', [
            'withdrawList' => $withdrawList,
            'stats' => $stats,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
            'limit' => $limit
        ]);
    }
    
    /**
     * 审核通过提现申请
     */
    public function withdrawApprove()
    {
        $id = input('id/d', 0);
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        $withdraw = Db::name('region_agent_withdraw')->where('id', $id)->find();
        if (!$withdraw) {
            return json(['code' => 0, 'msg' => '提现记录不存在']);
        }
        
        if ($withdraw['status'] != 0) {
            return json(['code' => 0, 'msg' => '只有待审核的申请可以通过']);
        }
        
        $updateData = [
            'status' => 1,
            'audit_time' => time(),
            'update_time' => time()
        ];
        
        $result = Db::name('region_agent_withdraw')->where('id', $id)->update($updateData);
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '审核通过成功']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
    }
    
    /**
     * 拒绝提现申请
     */
    public function withdrawReject()
    {
        $id = input('id/d', 0);
        $reason = input('reason/s', '');
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        $withdraw = Db::name('region_agent_withdraw')->where('id', $id)->find();
        if (!$withdraw) {
            return json(['code' => 0, 'msg' => '提现记录不存在']);
        }
        
        if ($withdraw['status'] != 0) {
            return json(['code' => 0, 'msg' => '只有待审核的申请可以拒绝']);
        }
        
        $updateData = [
            'status' => 2,
            'reject_reason' => $reason,
            'audit_time' => time(),
            'update_time' => time()
        ];
        
        $result = Db::name('region_agent_withdraw')->where('id', $id)->update($updateData);
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '已拒绝该申请']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
    }
    
    /**
     * 微信打款
     */
    public function withdrawWechatTransfer()
    {
        $id = input('id/d', 0);
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        $withdraw = Db::name('region_agent_withdraw')->where('id', $id)->find();
        if (!$withdraw) {
            return json(['code' => 0, 'msg' => '提现记录不存在']);
        }
        
        if ($withdraw['status'] != 1) {
            return json(['code' => 0, 'msg' => '只有审核通过的申请可以打款']);
        }
        
        // 获取代理商用户信息
        $user = Db::name('user')->where('user_id', $withdraw['user_id'])->find();
        if (!$user || empty($user['open_id'])) {
            return json(['code' => 0, 'msg' => '该用户没有绑定微信，无法打款']);
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('user_id', $withdraw['user_id'])->find();
        if (!$agent) {
            return json(['code' => 0, 'msg' => '代理商信息不存在']);
        }
        
        // 调用商家转账到零钱 V3 接口
        $orderNO = $withdraw['order_sn'] ?: OrderService::createOrderNo();
        try {
            $WxPay = new WxPay(WxappModel::getWxappCache($this->getWxappId()));
            $res = $WxPay->transfers(
                $withdraw['id'],
                $orderNO,
                $user,
                $withdraw['actual_amount'],
                '代理商提现',
                'region_agent',
                $user['real_name'],
                [
                    'money' => $withdraw['withdraw_amount'],
                    'charge' => $withdraw['tax_amount'],
                    'remark' => '区域代理提现',
                ]
            );
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '微信打款失败：' . $e->getMessage()]);
        }

        if ($res === true) {
            // 更新提现记录状态
            Db::name('region_agent_withdraw')->where('id', $id)->update([
                'status' => 3,
                'complete_time' => time(),
                'update_time' => time()
            ]);
            return json(['code' => 1, 'msg' => '微信打款成功']);
        }
        return json(['code' => 0, 'msg' => '微信打款失败']);
    }
    
    /**
     * 银行转账完成
     */
    public function withdrawBankTransfer()
    {
        $id = input('id/d', 0);
        if (!$id) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        $withdraw = Db::name('region_agent_withdraw')->where('id', $id)->find();
        if (!$withdraw) {
            return json(['code' => 0, 'msg' => '提现记录不存在']);
        }
        
        if ($withdraw['status'] != 1) {
            return json(['code' => 0, 'msg' => '只有审核通过的申请可以标记银行转账']);
        }
        
        // 只能是银行转账类型
        if ($withdraw['withdraw_type'] != 2) {
            return json(['code' => 0, 'msg' => '只有银行卡提现才能标记银行转账']);
        }
        
        // 更新提现记录状态
        $updateData = [
            'status' => 5, // 5 = 银行转账完成
            'complete_time' => time(),
            'update_time' => time()
        ];
        
        $result = Db::name('region_agent_withdraw')->where('id', $id)->update($updateData);
        
        if ($result !== false) {
            return json(['code' => 1, 'msg' => '已标记银行转账完成']);
        } else {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
    }
}