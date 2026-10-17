<?php

namespace app\store\controller\market;

use app\store\controller\Controller;
use app\store\model\Goods;
use app\common\model\Region as RegionModel;
use app\store\model\Setting as SettingModel;
use app\common\library\wechat\WxPay;
use app\common\model\Wxapp as WxappModel;
use app\common\service\Order as OrderService;
use think\facade\Db;

/**
 * 营销设置-区域代理商
 * Class Basic
 * @package app\store\controller
 */
class Regionagent extends Controller
{
    public function index(){
        // 获取代理商列表
        $list = Db::name('region_agent')
        ->order(['status' => 'asc'])
        ->paginate(15, false, [
            'query' => \request()->param()
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
            // 合约日期处理
            $item['contract_date'] = !empty($item['contract_date']) ? date('Y-m-d', $item['contract_date']) : '';
            
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
            
            // 获取用户微信信息（open_id）
            $item['open_id'] = $user ? ($user['open_id'] ?? '') : '';
            $item['has_withdraw'] = false; // 标记是否有提现记录
            return $item;
        });
        
        // 从region_agent_fee表获取费用设置（框架会自动添加mybaixin_前缀）
        $feeSettings = Db::name('region_agent_fee')->find();
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
            // 重新获取插入的数据
            $feeSettings = Db::name('region_agent_fee')->find();
        }
        
        // 确保返回的费用是数字类型
        $feeSettings = [
            'province_fee' => (float)$feeSettings['province_fee'],
            'city_fee' => (float)$feeSettings['city_fee'],
            'district_fee' => (float)$feeSettings['district_fee']
        ];
        
        // 从region_agent_tax_rate表获取税率设置（框架会自动添加mybaixin_前缀）
        $taxRateSettings = Db::name('region_agent_tax_rate')->find();
        // 如果没有数据，插入默认值
        if (!$taxRateSettings) {
            $now = time();
            Db::name('region_agent_tax_rate')->insert([
                'withdraw_tax_rate' => 0.00,
                'create_time' => $now,
                'update_time' => $now
            ]);
            // 重新获取插入的数据
            $taxRateSettings = Db::name('region_agent_tax_rate')->find();
        }
        
        // 确保返回的税率是数字类型
        $taxRateSettings = [
            'withdraw_tax_rate' => isset($taxRateSettings['withdraw_tax_rate']) ? (float)$taxRateSettings['withdraw_tax_rate'] : 0.00
        ];
        
        // 从mybaixin_region_agent_quantity表获取代理商可申请数量设置（与API接口统一）
        try {
            $applyCountSettings = Db::name('region_agent_quantity')->find();
            // 如果没有数据，插入默认值
            if (!$applyCountSettings) {
                $now = time();
                Db::name('region_agent_quantity')->insert([
                    'province_quantity' => 34,
                    'city_quantity' => 100,
                    'district_quantity' => 200,
                    'create_time' => $now,
                    'update_time' => $now
                ]);
                // 重新获取插入的数据
                $applyCountSettings = Db::name('region_agent_quantity')->find();
            }
        } catch (\Exception $e) {
            // 如果查询失败，使用默认值
            $applyCountSettings = [
                'province_quantity' => 34,
                'city_quantity' => 100,
                'district_quantity' => 200
            ];
        }
        
        // 确保返回的申请数量是数字类型（映射为前端使用的字段名）
        $applyCountSettings = [
            'province_count' => isset($applyCountSettings['province_quantity']) ? (int)$applyCountSettings['province_quantity'] : 34,
            'city_count' => isset($applyCountSettings['city_quantity']) ? (int)$applyCountSettings['city_quantity'] : 100,
            'district_count' => isset($applyCountSettings['district_quantity']) ? (int)$applyCountSettings['district_quantity'] : 200
        ];
        
        // 调试信息：打印申请数量设置
        // error_log('代理商可申请数量设置：' . print_r($applyCountSettings, true));
        
        // 获取代理商支付订单数据
        try {
            $payOrders = Db::name('region_agent_pay_order')
                ->order(['create_time' => 'desc'])
                ->paginate(15, false, [
                    'query' => $this->request->param()
                ])
                ->each(function ($item, $key) {
                    $item['user_nickname'] = Db::name('user')->where('user_id', $item['user_id'])->value('nickName');
                    $item['pay_time'] = !empty($item['pay_time']) ? date('Y-m-d H:i:s', $item['pay_time']) : '';
                    $item['is_paid_text'] = $item['is_paid'] == 1 ? '已支付' : '未支付';
                    return $item;
                });
        } catch (\Exception $e) {
            // 如果查询失败，返回空集合
            $payOrders = [];
        }
        
        return $this->fetch('index', [
            // 代理商列表
            'list' => $list,
            // 费用设置
            'feeSettings' => $feeSettings,
            // 税率设置
            'taxRateSettings' => $taxRateSettings,
            // 代理商可申请数量
            'applyCountSettings' => $applyCountSettings,
            // 代理商支付订单
            'payOrders' => $payOrders
        ]);
        
    }
    
    /**
     * 保存代理商费用设置
     */
    public function saveFee(){
        // 直接使用input函数获取POST数据，确保获取到正确的字段值
        $province_fee = input('province_fee', '0');
        $city_fee = input('city_fee', '0');
        $district_fee = input('district_fee', '0');
        
        // 验证数据
        if (floatval($province_fee) < 0 || floatval($city_fee) < 0 || floatval($district_fee) < 0) {
            return $this->renderError('费用不能为负数');
        }
        
        $now = time();
        
        // 直接使用数据库操作，更新或插入到region_agent_fee表（框架会自动添加mybaixin_前缀）
        $tableName = 'region_agent_fee';
        
        // 检查表中是否已有数据
        $exists = Db::name('region_agent_fee')->find();
        
        if ($exists) {
            // 更新现有数据，必须指定where条件
            Db::name('region_agent_fee')->where('id', $exists['id'])->update([
                'province_fee' => $province_fee,
                'city_fee' => $city_fee,
                'district_fee' => $district_fee,
                'update_time' => $now
            ]);
        } else {
            // 插入新数据
            Db::name('region_agent_fee')->insert([
                'province_fee' => $province_fee,
                'city_fee' => $city_fee,
                'district_fee' => $district_fee,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        return $this->renderSuccess('保存成功', url('market.regionagent/index'));
    }
    
    /**
     * 保存提现税率设置
     */
    public function saveTaxRate(){
        // 获取提现税率
        $withdraw_tax_rate = input('withdraw_tax_rate', '0');
        
        // 验证数据
        if (floatval($withdraw_tax_rate) < 0 || floatval($withdraw_tax_rate) > 100) {
            return $this->renderError('税率必须在0-100之间');
        }
        
        $now = time();
        
        // 直接使用数据库操作，更新或插入到region_agent_tax_rate表（框架会自动添加mybaixin_前缀）
        // 检查表中是否已有数据
        $exists = Db::name('region_agent_tax_rate')->find();
        
        if ($exists) {
            // 更新现有数据，必须指定where条件
            Db::name('region_agent_tax_rate')->where('id', $exists['id'])->update([
                'withdraw_tax_rate' => $withdraw_tax_rate,
                'update_time' => $now
            ]);
        } else {
            // 插入新数据
            Db::name('region_agent_tax_rate')->insert([
                'withdraw_tax_rate' => $withdraw_tax_rate,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        return $this->renderSuccess('保存成功', url('market.regionagent/index'));
    }
    
    /**
     * 保存代理商可申请数量设置
     */
    public function saveApplyCount(){
        // 获取代理商可申请数量
        $province_count = input('province_count', '1');
        $city_count = input('city_count', '3');
        $district_count = input('district_count', '5');
        
        // 验证数据
        if (intval($province_count) < 0 || intval($city_count) < 0 || intval($district_count) < 0) {
            return $this->renderError('申请数量不能为负数');
        }
        
        $now = time();
        
        // 检查表中是否已有数据（使用统一的表 mybaixin_region_agent_quantity）
        $exists = Db::name('region_agent_quantity')->find();
        
        if ($exists) {
            // 更新现有数据，必须指定where条件
            Db::name('region_agent_quantity')->where('id', $exists['id'])->update([
                'province_quantity' => $province_count,
                'city_quantity' => $city_count,
                'district_quantity' => $district_count,
                'update_time' => $now
            ]);
        } else {
            // 插入新数据
            Db::name('region_agent_quantity')->insert([
                'province_quantity' => $province_count,
                'city_quantity' => $city_count,
                'district_quantity' => $district_count,
                'create_time' => $now,
                'update_time' => $now
            ]);
        }
        
        return $this->renderSuccess('保存成功', url('market.regionagent/index'));
    }
    
    public function submit()
    {
        $id     = input('id/d');           // 主键ID，必须是整数
        $status = input('apply_status/d'); // 要改成的状态：102=驳回，103=通过
        $reason = input('reject_reason/s', ''); // 驳回原因，字符串，默认空，/s 会 trim
        $contract_date = input('contract_date/s', ''); // 合约日期

        // 1. 基本参数校验
        if (!$id || !$status) {
            return $this->renderError('参数错误');
        }

        // 2. 驳回时必须填写原因
        if ($status == 102 && empty($reason)) {
            return $this->renderError('驳回时必须填写驳回原因');
        }

        // 3. 通过时可以清空原因
        if ($status == 103) {
            $updateData = ['status' => 103, 'reject_reason' => ''];
            // 如果提供了合约日期，则添加到更新数据中
            if (!empty($contract_date)) {
                $updateData['contract_date'] = strtotime($contract_date);
            }
        } else {
            $updateData = [
                'status'        => $status,
                'reject_reason' => $reason
            ];
        }

        // 4. 执行更新
        $result = Db::name('region_agent')
            ->where('id', $id)
            ->update($updateData);

        if ($result !== false) {
            return $this->renderSuccess('操作成功');
        } else {
            return $this->renderError('操作失败或数据无变化');
        }
    }
    
    /**
     * 更新合约日期
     */
    public function updateContract()
    {
        $id = input('id/d');           // 主键ID，必须是整数
        $contract_date = input('contract_date/s', ''); // 合约日期

        // 1. 基本参数校验
        if (!$id) {
            return $this->renderError('参数错误');
        }

        // 2. 合约日期校验
        if (empty($contract_date)) {
            return $this->renderError('合约日期不能为空');
        }

        // 3. 执行更新
        $result = Db::name('region_agent')
            ->where('id', $id)
            ->update([
                'contract_date' => strtotime($contract_date),
                'update_time' => time()
            ]);

        if ($result !== false) {
            return $this->renderSuccess('合约日期更新成功', url('market.regionagent/index'));
        } else {
            return $this->renderError('合约日期更新失败或数据无变化');
        }
    }
    
    /**
     * 获取代理商的订单列表（根据订单地址匹配代理区域）
     */
    public function getAgentOrders()
    {
        $agentId = input('agent_id/d', 0);
        if (!$agentId) {
            return $this->renderError('参数错误');
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agentId)->find();
        if (!$agent) {
            return $this->renderError('代理商不存在');
        }
        
        // 获取代理区域信息
        $region = Db::name('region')->where('id', $agent['region_id'])->find();
        if (!$region) {
            return $this->renderError('区域信息不存在');
        }
        
        $level = $region['level']; // 1-省, 2-市, 3-区
        $regionId = $agent['region_id'];
        
        // 根据代理级别查询订单
        // 需要关联订单地址表，根据省市区匹配
        $query = Db::name('order')
            ->alias('o')
            ->join('mybaixin_order_address oa', 'o.order_id = oa.order_id', 'left')
            ->where('o.is_delete', 0)
            ->where('o.is_region_agent', 1)
            ->where('o.pay_status', 20); // 只查询已支付的订单
        
        // 根据代理级别匹配不同的区域ID和分润字段
        if ($level == 1) {
            // 省代：匹配省ID，查询region_agent_first字段
            $query->where('oa.province_id', $regionId)
                  ->where('o.region_agent_first', '>', 0);
        } elseif ($level == 2) {
            // 市代：匹配市ID，查询region_agent_second字段
            $query->where('oa.city_id', $regionId)
                  ->where('o.region_agent_second', '>', 0);
        } elseif ($level == 3) {
            // 区代：匹配区ID，查询region_agent_third字段
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
        
        // 直接返回JSON数据，不使用renderSuccess()方法
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
            return $this->renderError('参数错误');
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agentId)->find();
        if (!$agent) {
            return $this->renderError('代理商不存在');
        }
        
        // 获取代理区域信息
        $region = Db::name('region')->where('id', $agent['region_id'])->find();
        if (!$region) {
            return $this->renderError('区域信息不存在');
        }
        
        $level = $region['level'];
        $regionId = $agent['region_id'];
        
        // 根据代理级别查询订单
        $query = Db::name('order')
            ->alias('o')
            ->join('mybaixin_order_address oa', 'o.order_id = oa.order_id', 'left')
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
     * 获取代理商的收入订单列表（根据订单分润字段）
     */
    public function getAgentIncomeOrders()
    {
        $agentId = input('agent_id/d', 0);
        if (!$agentId) {
            return $this->renderError('参数错误');
        }
        
        // 获取代理商信息
        $agent = Db::name('region_agent')->where('id', $agentId)->find();
        if (!$agent) {
            return $this->renderError('代理商不存在');
        }
        
        // 获取代理区域信息
        $region = Db::name('region')->where('id', $agent['region_id'])->find();
        if (!$region) {
            return $this->renderError('区域信息不存在');
        }
        
        $level = $region['level']; // 1-省, 2-市, 3-区
        
        // 根据代理级别查询有分润的订单
        $query = Db::name('order')
            ->alias('o')
            ->join('mybaixin_order_address oa', 'o.order_id = oa.order_id', 'left')
            ->where('o.is_delete', 0)
            ->where('o.is_region_agent', 1)
            ->where('o.pay_status', 20); // 只查询已支付的订单
        
        // 根据代理级别匹配不同的区域ID和分润字段
        if ($level == 1) {
            // 省代：匹配省ID，查询region_agent_first字段
            $query->where('oa.province_id', $agent['region_id'])
                  ->where('o.region_agent_first', '>', 0);
        } elseif ($level == 2) {
            // 市代：匹配市ID，查询region_agent_second字段
            $query->where('oa.city_id', $agent['region_id'])
                  ->where('o.region_agent_second', '>', 0);
        } elseif ($level == 3) {
            // 区代：匹配区ID，查询region_agent_third字段
            $query->where('oa.region_id', $agent['region_id'])
                  ->where('o.region_agent_third', '>', 0);
        }
        
        $orders = $query->field('o.order_id, o.order_no, o.total_price, o.pay_price, o.pay_status, o.order_status, o.create_time, o.region_agent_first, o.region_agent_second, o.region_agent_third, o.region_agent_is_settle, oa.name, oa.phone')
            ->order('o.create_time desc')
            ->paginate(15, false, [
                'query' => $this->request->param()
            ]);
        
        // 格式化订单数据
        $orders->each(function ($order) use ($level) {
            $order['create_time_text'] = date('Y-m-d H:i:s', $order['create_time']);
            $order['pay_status_text'] = $order['pay_status'] == 20 ? '已支付' : '未支付';
            $order['order_status_text'] = $order['order_status'] == 10 ? '待付款' : 
                                          ($order['order_status'] == 20 ? '待发货' : 
                                          ($order['order_status'] == 30 ? '待收货' : 
                                          ($order['order_status'] == 40 ? '已完成' : '已取消')));
            // 根据代理级别显示对应的分润金额
            if ($level == 1) {
                $order['income'] = $order['region_agent_first'];
            } elseif ($level == 2) {
                $order['income'] = $order['region_agent_second'];
            } elseif ($level == 3) {
                $order['income'] = $order['region_agent_third'];
            } else {
                $order['income'] = 0;
            }
            $order['is_settle_text'] = $order['region_agent_is_settle'] == 1 ? '已结算' : '未结算';
            return $order;
        });
        
        // 调试输出
        error_log('代理商收入订单列表调试:');
        error_log('列表总数: ' . $orders->total());
        error_log('当前页: ' . $orders->currentPage());
        error_log('每页数量: ' . $orders->listRows());
        error_log('实际数据: ' . print_r($orders->items(), true));
        
        // 直接返回JSON数据，不使用renderSuccess()方法
        return json([
            'code' => 1,
            'msg' => 'success',
            'data' => [
                'list' => $orders->items(),
                'total' => $orders->total(),
                'page' => $orders->currentPage()
            ]
        ]);
    }
    
    /**
     * 获取代理商的提现信息（微信支付）
     */
    public function getAgentWithdrawInfo()
    {
        $userId = input('user_id/d', 0);
        if (!$userId) {
            return $this->renderError('参数错误');
        }
        
        try {
            // 获取用户信息
            $user = Db::name('user')->where('user_id', $userId)->find();
            if (!$user) {
                return $this->renderError('用户不存在');
            }
            
            // 获取用户的提现记录
            $withdrawList = Db::name('user_balance_withdraw')
                ->where('user_id', $userId)
                ->order('create_time desc')
                ->limit(20)
                ->select();
            
            // 格式化提现数据
            foreach ($withdrawList as &$withdraw) {
                $withdraw['create_time_text'] = date('Y-m-d H:i:s', $withdraw['create_time']);
                $withdraw['audit_time_text'] = !empty($withdraw['audit_time']) ? date('Y-m-d H:i:s', $withdraw['audit_time']) : '-';
                // 提现状态
                $withdraw['apply_status_text'] = $withdraw['apply_status'] == 10 ? '待审核' : 
                                                    ($withdraw['apply_status'] == 20 ? '审核通过' : 
                                                    ($withdraw['apply_status'] == 30 ? '已驳回' : 
                                                    ($withdraw['apply_status'] == 40 ? '已打款' : '未知')));
                // 支付方式
                $withdraw['pay_type_text'] = $withdraw['pay_type'] == 10 ? '微信支付' : 
                                            ($withdraw['pay_type'] == 20 ? '支付宝' : 
                                            ($withdraw['pay_type'] == 30 ? '银行卡' : '未知'));
            }
            
            // 返回用户信息和提现记录
            return json([
                'code' => 1,
                'msg' => 'success',
                'data' => [
                    'user' => [
                        'user_id' => $user['user_id'],
                        'nickName' => $user['nickName'] ?? '',
                        'open_id' => $user['open_id'] ?? '',
                        'balance' => $user['balance'] ?? 0,
                    ],
                    'withdraw_list' => $withdrawList,
                    'withdraw_count' => count($withdrawList)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->renderError('获取提现信息失败：' . $e->getMessage());
        }
    }

    /**
     * 代理商提现审核列表
     */
    public function withdraw()
    {
        $list = $this->get_withdraw_list();
        
        // 调试输出
        error_log('代理商提现审核列表调试:');
        error_log('列表总数: ' . $list->total());
        error_log('当前页: ' . $list->currentPage());
        error_log('每页数量: ' . $list->listRows());
        error_log('实际数据: ' . print_r($list->items(), true));
        
        // 直接返回JSON数据，不使用fetch()方法
        return json([
            'code' => 1,
            'msg' => 'success',
            'data' => [
                'list' => $list->items(),
                'total' => $list->total(),
                'page' => $list->currentPage()
            ]
        ]);
    }

    /**
     * 提现审核提交
     */
    public function withdraw_submit()
    {
        $id = $this->request->post('id');
        $status = $this->request->post('apply_status');
        $reason = $this->request->post('reject_reason', '');
        
        $withdraw = Db::name('user_balance_withdraw')
            ->where('id', $id)
            ->where('scene', '代理商提现')
            ->find();
            
        if (!$withdraw) {
            return $this->renderError('提现记录不存在或不是代理商提现记录');
        }
        
        if ($withdraw['apply_status'] != 10) {
            return $this->renderError('该提现申请已处理，不能重复审核');
        }

        Db::startTrans();
        try {
            if ($status == 40) { // 驳回
                // 退还金额
                Db::name('user')->where('user_id', $withdraw['user_id'])->setInc('balance', $withdraw['money']);
                
                // 退还冻结金额
                Db::name('user')->where('user_id', $withdraw['user_id'])->setDec('lock_balance', $withdraw['money']);
                
                // 记录日志
                Db::name('user_balance_log')->insert([
                    'user_id' => $withdraw['user_id'],
                    'scene' => 20, // 提现
                    'money' => $withdraw['money'],
                    'describe' => '代理商提现申请驳回，退还金额',
                    'remark' => '驳回原因: ' . $reason,
                    'wxapp_id' => $this->getWxappId(),
                    'create_time' => time(),
                ]);
            } elseif ($status == 20) { // 审核通过
                // 扣减冻结金额
                Db::name('user')->where('user_id', $withdraw['user_id'])->setDec('lock_balance', $withdraw['money']);
                
                // 记录日志
                Db::name('user_balance_log')->insert([
                    'user_id' => $withdraw['user_id'],
                    'scene' => 20, // 提现
                    'money' => -$withdraw['money'],
                    'describe' => '代理商提现申请通过，扣除冻结金额',
                    'remark' => '提现金额: ' . $withdraw['money'],
                    'wxapp_id' => $this->getWxappId(),
                    'create_time' => time(),
                ]);
                
                // 更新代理商的累积提现金额
                Db::name('region_agent')->where('user_id', $withdraw['user_id'])->setInc('total_withdraw', $withdraw['money']);
            }
            
            // 更新提现记录
            Db::name('user_balance_withdraw')->where('id', $id)->update([
                'apply_status' => $status,
                'reject_reason' => $reason,
                'audit_time' => time(),
                'audit_by' => $this->store['user']['user_name'] ?? '管理员',
            ]);
            
            Db::commit();
            return $this->renderSuccess('操作成功');
        } catch (\Exception $e) {
            Db::rollback();
            return $this->renderError($e->getMessage());
        }
    }

    /**
     * 代理商提现：商家转账到零钱 V3
     */
    public function withdraw_wechat_pay($id = null)
    {
        $id = $id ?: $this->request->post('id');

        $withdraw = Db::name('user_balance_withdraw')
            ->where('id', $id)
            ->where('scene', '代理商提现')
            ->where('apply_status', 20)
            ->where('pay_type', 10) // 微信提现
            ->find();

        if (!$withdraw) {
            return $this->renderError('提现记录不存在或不符合微信打款条件');
        }

        // 获取用户信息
        $user = Db::name('user')->where('user_id', $withdraw['user_id'])
            ->field('user_id,open_id,real_name')->find();
        if (!$user || empty($user['open_id'])) {
            return $this->renderError('用户信息不完整，无法进行微信打款');
        }

        // TP8 未迁移 app\common\library\wechat\WxPay（商家转账到零钱 V3），直接 new 会 fatal，故先判定
        if (!class_exists('\\app\\common\library\\wechat\\WxPay')) {
            return $this->renderError('微信打款功能尚未迁移到 TP8（缺少 WxPay 类），请线下处理');
        }
        // TP8 的 common\service\Order 不一定提供 createOrderNo()，缺失时降级生成本地单号
        $orderNO = class_exists('\\app\\common\\service\\Order') && method_exists('\\app\\common\\service\\Order', 'createOrderNo')
            ? \app\common\service\Order::createOrderNo()
            : ('RA' . date('YmdHis') . mt_rand(1000, 9999));
        try {
            $WxPay = new WxPay(WxappModel::getWxappCache($this->getWxappId()));
            $res = $WxPay->transfers(
                $withdraw['id'],
                $orderNO,
                $user,
                $withdraw['money'],
                '代理商提现',
                'region_agent',
                $user['real_name'],
                ['money' => $withdraw['money'], 'charge' => 0, 'remark' => '区域代理提现']
            );
        } catch (\Exception $e) {
            return $this->renderError('微信打款失败：' . $e->getMessage());
        }

        if ($res === true) {
            Db::name('user_balance_withdraw')->where('id', $id)
                ->update(['apply_status' => 40, 'pay_time' => time()]);
            return $this->renderSuccess('微信打款成功');
        }
        return $this->renderError('微信打款失败，请重试');
    }
    
    /**
     * 代理商微信提现已统一走 WxPay::transfers()（商家转账到零钱 V3），见 withdraw_wechat_pay()
     * 旧的「企业付款到零钱」接口已下线，此处不再使用。
     */

    
    /**
     * 生成微信签名
     * @param array $params 请求参数
     * @param string $apiKey API密钥
     * @return string 签名
     */
    private function makeWechatSign($params, $apiKey)
    {
        ksort($params);
        $string = '';
        foreach ($params as $key => $value) {
            if ($value !== '' && $value !== null) {
                $string .= $key . '=' . $value . '&';
            }
        }
        $string = rtrim($string, '&');
        return strtoupper(md5($string . '&key=' . $apiKey));
    }
    
    /**
     * 数组转XML
     * @param array $data 数组数据
     * @return string XML字符串
     */
    private function arrayToXml($data)
    {
        $xml = '<xml>';
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $xml .= "<{$key}>{$value}</{$key}>";
            } else {
                $xml .= "<{$key}><
![CDATA[{$value}]]></{$key}>";
            }
        }
        $xml .= '</xml>';
        return $xml;
    }
    
    /**
     * 解析微信XML返回
     * @param string $xml XML字符串
     * @return array 解析后的数组
     */
    private function parseWechatXml($xml)
    {
        $data = [];
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);
        
        foreach ($values as $value) {
            if (isset($value['tag'])) {
                $tag = $value['tag'];
                if (isset($value['value'])) {
                    $data[$tag] = $value['value'];
                } else {
                    $data[$tag] = '';
                }
            }
        }
        
        return $data;
    }
    
    /**
     * POST请求微信API
     * @param string $url 请求URL
     * @param string $xml XML数据
     * @param string $certPath 证书路径
     * @param string $keyPath 密钥路径
     * @return string 返回结果
     */
    private function postWechatApi($url, $xml, $certPath = '', $keyPath = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // 使用证书
        if ($certPath && file_exists($certPath)) {
            curl_setopt($ch, CURLOPT_SSLCERT, $certPath);
            curl_setopt($ch, CURLOPT_SSLKEY, $keyPath);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($httpCode != 200) {
            throw new \Exception("HTTP请求失败，状态码: {$httpCode}");
        }
        
        return $response;
    }

    /**
     * 获取提现列表
     */
    private function get_withdraw_list()
    {
        $query = Db::name('user_balance_withdraw')
            ->where('scene', '代理商提现')
            ->order(['create_time' => 'desc']);
        
        $list = $query->paginate(15, false, [
            'query' => \request()->param()
        ]);
        
        return $list->each(function ($item, $key) {
            $user = Db::name('user')->where('user_id', $item['user_id'])->find();
            $item['nickName'] = $user['nickName'];
            $item['avatarUrl'] = $user['avatarUrl'];
            return $item;
        });
    }

}