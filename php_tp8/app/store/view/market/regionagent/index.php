<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">代理商管理</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 选项卡导航 -->
                    <div class="j-tabs js-tabs mb-1">
                        <ul class="flex flex-wrap gap-2 border-b border-gray-200">
                            <li><a href="javascript:;" data-target="#tab1" class="inline-block px-3 py-2 border-b-2 border-blue-500 text-blue-600">代理商审核</a></li>
                            <li><a href="javascript:;" data-target="#tab2" class="inline-block px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">代理商费用设置</a></li>
                            <li><a href="javascript:;" data-target="#tab3" class="inline-block px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">代理订单</a></li>
                            <li><a href="javascript:;" data-target="#tab4" class="inline-block px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">提现税率设置</a></li>
                            <li><a href="javascript:;" data-target="#tab5" class="inline-block px-3 py-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700">代理商可申请数量设置</a></li>
                        </ul>
                        
                        <!-- 选项卡内容 -->
                        <div class="mt-4">
                            <!-- 选项卡1：代理商审核 -->
                            <div class="js-tab-panel" id="tab1">
                                <div class="tips mb-4 sm:w-full">
                                    <div class="pre">
                                        <p class="pb-4-xs"> 代理商说明：</p>
                                        <p> 商品编辑中 设置：代理商利润比例 省、市、区 2025-09-17 GUOZHIWEI </p>
                                    </div>
                                </div>
                                <!-- 工具栏 -->
                                <div class="page_toolbar mb-1 ">

                                </div>
                                <div class="overflow-x-auto sm:w-full pb-6">
                                    <table width="100%" class="table-base  
                                     tpl-table-black whitespace-nowrap">
                                        <thead>
                                        <tr>
                                            <th>用户id</th>
                                            <!--<th>-->
                                            <!--    <p>姓名</p>-->
                                            <!--    <p>手机号</p>-->
                                            <!--</th>-->
                                            <th>用户昵称</th>
                                            <th>代理订单</th>
                                            <th>提现信息</th>
                                            <th>地区</th>
                                            <th>级别</th>
                                            <th>状态</th>
                                            <th>支付状态</th>
                                            <th>支付时间</th>
                                            <th>总利润</th>
                                            <th>合约日期</th>
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!$list->isEmpty()): ?>
                                            <?php foreach ($list as $item): ?>
                                            <tr>
                                                <td class="align-middle"><?= $item['user_id'] ?></td>
                                                <td class="align-middle"><?= $item['nick_name'] ?></td>
                                                 <td class="align-middle">
                                                     <a href="javascript:void(0);" class="j-show-orders" data-agent-id="<?= $item['id'] ?>" data-user-id="<?= $item['user_id'] ?>">
                                                         <i class="icon-list"></i> 查看订单
                                                     </a>
                                                 </td>
                                                 <td class="align-middle">
                                                     <a href="javascript:void(0);" class="j-show-withdraw-list" data-user-id="<?= $item['user_id'] ?>">
                                                         <i class="icon-list"></i> 提现审核
                                                     </a>
                                                 </td>
                                                <td class="align-middle"><?= $item['region_text'] ?></td>
                                                <td class="align-middle"><?= $item['level_text'] ?></td>
                                                <td class="align-middle"><?= $item['status_text'] ?></td>
                                                <td class="align-middle" style="color: <?php echo $item['is_paid'] == 1 ? '#52C41A' : '#FF4D4F'; ?>">
                                                    <?= $item['is_paid_text'] ?></td>
                                                <td class="align-middle"><?= $item['pay_time_text'] ?></td>
                                                <td class="align-middle"><?= $item['money'] ?></td>
                                                <td class="align-middle"><?= $item['contract_date'] ?></td>
                                                <td class="align-middle" >
                                                    <div class="tpl-table-black-operation">
                                                        <?php if ($item['status_text'] == '待审核') : ?>

                                                            <a class="j-audit" data-id="<?= $item['id'] ?>"
                                                               href="javascript:void(0);">
                                                                <i class="icon-pencil"></i> 审核</a>

                                                        <?php elseif ($item['status_text'] == '已驳回') : ?>
                                                            <a class="j-show-reason tpl-table-black-operation-green"
                                                               href="javascript:void(0);"
                                                               data-reason="<?= $item['reason'] ?>">
                                                                驳回原因</a>
                                                        <?php elseif ($item['status_text'] == '已通过') : ?>
                                                            <a class="j-edit-contract tpl-table-black-operation-green"
                                                               href="javascript:void(0);"
                                                               data-id="<?= $item['id'] ?>"
                                                               data-contract-date="<?= $item['contract_date'] ?>">
                                                                <i class="icon-pencil"></i> 修改合约</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">暂无记录</td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="lg:w-full ">
                                        <div class="float-right"><?= $list->render() ?> </div>
                                        <div class="float-right pagination-total mr-4">
                                            <div class="align-middle">总记录：<?= $list->total() ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 选项卡2：代理商费用设置 -->
                            <div class="js-tab-panel hidden" id="tab2">
                                <form class="tpl-form-line-form" id="feeForm">
                                    <input type="hidden" name="s" value="/store/market.regionagent/saveFee">
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="province_fee" class="sm:w-1/4 text-sm text-gray-600 shrink-0">省代理费用 <span class="tpl-form-line-small-title">元</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="province_fee" name="province_fee" placeholder="请输入省代理费用" value="<?= isset($feeSettings['province_fee']) ? htmlspecialchars($feeSettings['province_fee']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="city_fee" class="sm:w-1/4 text-sm text-gray-600 shrink-0">市代理费用 <span class="tpl-form-line-small-title">元</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="city_fee" name="city_fee" placeholder="请输入市代理费用" value="<?= isset($feeSettings['city_fee']) ? htmlspecialchars($feeSettings['city_fee']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="district_fee" class="sm:w-1/4 text-sm text-gray-600 shrink-0">区代理费用 <span class="tpl-form-line-small-title">元</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="district_fee" name="district_fee" placeholder="请输入区代理费用" value="<?= isset($feeSettings['district_fee']) ? htmlspecialchars($feeSettings['district_fee']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%]">
                                            <button type="button" class="btn btn-primary tpl-btn-bg-color-success" id="saveFeeBtn">保存设置</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- 选项卡3：代理商支付订单 -->
                            <div class="js-tab-panel hidden" id="tab3">
                                <div class="overflow-x-auto sm:w-full pb-6">
                                    <table width="100%" class="table-base  
                                     tpl-table-black whitespace-nowrap">
                                        <thead>
                                        <tr>
                                            <th>代理商用户ID</th>
                                            <th>用户昵称</th>
                                            <th>订单号</th>
                                            <th>支付费用</th>
                                            <th>支付时间</th>
                                            <th>是否支付成功</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php if (!$payOrders->isEmpty()): ?>
                                            <?php foreach ($payOrders as $order): ?>
                                            <tr>
                                                <td class="align-middle"><?= $order['user_id'] ?></td>
                                                <td class="align-middle"><?= $order['user_nickname'] ?></td>
                                                <td class="align-middle"><?= $order['order_sn'] ?></td>
                                                <td class="align-middle">¥<?= $order['amount'] ?></td>
                                                <td class="align-middle"><?=  time_text($order['pay_time'])  ?></td>
                                                <td class="align-middle"><?= $order['is_paid_text'] ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center">暂无记录</td>
                                            </tr>
                                        <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="lg:w-full ">
                                        <div class="float-right"><?= $payOrders->render() ?> </div>
                                        <div class="float-right pagination-total mr-4">
                                            <div class="align-middle">总记录：<?= $payOrders->total() ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 选项卡3：代理订单 -->
                            <div class="js-tab-panel hidden" id="tab3">
                                <div class="tips mb-4 sm:w-full">
                                    <div class="pre">
                                        <p class="pb-4-xs">代理订单说明：</p>
                                        <p> 查看代理商的订单收入和结算状态</p>
                                    </div>
                                </div>
                                <!-- 工具栏 -->
                                <div class="page_toolbar mb-1 ">
                                    <div class="flex flex-wrap flex flex-wrap">
                                        <button type="button" class="btn btn-secondary rounded-0" onclick="resetAgentOrderFilter()">
                                            <i class="icon-refresh"></i> 重置筛选
                                        </button>
                                        <button type="button" class="btn btn-primary rounded-0" onclick="filterAgentOrders()">
                                            <i class="icon-search"></i> 筛选
                                        </button>
                                    </div>
                                </div>
                                <div class="overflow-x-auto sm:w-full pb-6">
                                    <table width="100%" class="table-base  
                                         tpl-table-black whitespace-nowrap">
                                        <thead>
                                            <tr>
                                                <th>订单号</th>
                                                <th>订单金额</th>
                                                <th>收入</th>
                                                <th>结算状态</th>
                                                <th>筛选条件</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!$list->isEmpty()): ?>
                                                <?php foreach ($list as $item): ?>
                                                    <tr>
                                                        <td class="align-middle"><?= $item['order_no'] ?></td>
                                                        <td class="align-middle">¥<?= $item['pay_price'] ?></td>
                                                        <td class="align-middle" style="color: #52C41A; font-weight: bold;">¥<?= $item['income'] ?></td>
                                                        <td class="align-middle"><?= $item['is_settle_text'] ?></td>
                                                        <td class="align-middle">
                                                            <select class="form-input text-sm py-1" onchange="filterAgentOrder(<?= $item['id'] ?>, this.value)">
                                                                <option value="">全部</option>
                                                                <option value="settled" <?= $item['is_settle'] == 1 ? 'selected' : '' ?>>已结算</option>
                                                                <option value="unsettled" <?= $item['is_settle'] == 0 ? 'selected' : '' ?>>未结算</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">暂无订单数据</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <div class="lg:w-full ">
                                        <div class="float-right"><?= $list->render() ?></div>
                                        <div class="float-right pagination-total mr-4">
                                            <div class="align-middle">总记录：<?= $list->total() ?></div>
                                        </div>
                                    </div>
                            </div>
                            
                            <!-- 选项卡4：提现税率设置 -->
                            <div class="js-tab-panel hidden" id="tab4">
                                <form class="tpl-form-line-form" id="taxRateForm">
                                    <input type="hidden" name="s" value="/store/market.regionagent/saveTaxRate">
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="withdraw_tax_rate" class="sm:w-1/4 text-sm text-gray-600 shrink-0">提现税率 <span class="tpl-form-line-small-title">百分比</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="withdraw_tax_rate" name="withdraw_tax_rate" placeholder="请输入提现税率（0-100）" value="<?= isset($taxRateSettings['withdraw_tax_rate']) ? htmlspecialchars($taxRateSettings['withdraw_tax_rate']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%]">
                                            <button type="button" class="btn btn-primary tpl-btn-bg-color-success" id="saveTaxRateBtn">保存税率</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- 选项卡5：代理商可申请数量设置 -->
                            <div class="js-tab-panel hidden" id="tab5">
                                <form class="tpl-form-line-form" id="applyCountForm">
                                    <input type="hidden" name="s" value="/store/market.regionagent/saveApplyCount">
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="province_count" class="sm:w-1/4 text-sm text-gray-600 shrink-0">省代理可申请数量 <span class="tpl-form-line-small-title">个</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="province_count" name="province_count" placeholder="请输入省代理可申请数量" value="<?= isset($applyCountSettings['province_count']) ? htmlspecialchars($applyCountSettings['province_count']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="city_count" class="sm:w-1/4 text-sm text-gray-600 shrink-0">市代理可申请数量 <span class="tpl-form-line-small-title">个</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="city_count" name="city_count" placeholder="请输入市代理可申请数量" value="<?= isset($applyCountSettings['city_count']) ? htmlspecialchars($applyCountSettings['city_count']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <label for="district_count" class="sm:w-1/4 text-sm text-gray-600 shrink-0">区代理可申请数量 <span class="tpl-form-line-small-title">个</span></label>
                                        <div class="sm:w-3/4">
                                            <input type="text" class="tpl-form-input" id="district_count" name="district_count" placeholder="请输入区代理可申请数量" value="<?= isset($applyCountSettings['district_count']) ? htmlspecialchars($applyCountSettings['district_count']) : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                                        <div class="sm:w-3/4 sm:ml-[25%]">
                                            <button type="button" class="btn btn-primary tpl-btn-bg-color-success" id="saveApplyCountBtn">保存设置</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 提现审核 -->
<script id="tpl-dealer-withdraw" type="text/template">
    <div class="pt-3">
        <form class="form-dealer-withdraw tpl-form-line-form" method="post"
              action="<?= url('user.balance/submit') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 审核状态 </label>
                <div class="sm:w-3/4">
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="withdraw[apply_status]" value="20"
                               checked> 审核通过
                    </label>
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="withdraw[apply_status]" value="30"> 驳回
                    </label>
                </div>
            </div>
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 驳回原因 </label>
                <div class="sm:w-3/4">
                    <input type="text" class="tpl-form-input" name="withdraw[reject_reason]" placeholder="仅在驳回时填写"
                           value="">
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 审核操作 -->
<script id="tpl-dealer-withdraw1" type="text/template">
    <div class="pt-3">
        <form class="form-dealer-withdraw tpl-form-line-form" method="post"
              action="<?= url('market.regionagent/submit') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 审核状态 </label>
                <div class="sm:w-3/4">
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply_status" value="103"
                               checked> 审核通过
                    </label>
                    <label class="inline-flex items-center gap-2 mr-4">
                        <input type="radio" class="w-4 h-4 accent-blue-600" name="apply_status" value="102"> 驳回
                    </label>
                </div>
            </div>
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 合约日期 </label>
                <div class="sm:w-3/4">
                    <div class="input-group ">
                        <input type="text" class="form-input tpl-form-input" name="contract_date" placeholder="选择合约到期日期"
                               value="">
                        <span class="input-group-btn input-group-btn">
                            <button class="btn btn-default" type="button"><span class="icon-calendar"></span> </button>
                        </span>
                    </div>
                    <!-- 快捷期限选项 -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-default btn-xs contract-term" data-years="3">3年</button>
                        <button type="button" class="btn btn-default btn-xs contract-term" data-years="5">5年</button>
                    </div>
                </div>
            </div>
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 驳回原因 </label>
                <div class="sm:w-3/4">
                    <input type="text" class="tpl-form-input" name="reject_reason" placeholder="仅在驳回时填写"
                           value="">
                </div>
            </div>
        </form>
    </div>
</script>

<!-- 修改合约操作 -->
<script id="tpl-edit-contract" type="text/template">
    <div class="pt-3">
        <form class="form-edit-contract tpl-form-line-form" method="post"
              action="<?= url('market.regionagent/updateContract') ?>">
            <input type="hidden" name="id" value="{{ id }}">
            <div class="form-group mb-4 flex flex-wrap items-start gap-x-2">
                <label class="sm:w-1/4 text-sm text-gray-600 shrink-0"> 合约日期 </label>
                <div class="sm:w-3/4">
                    <div class="input-group ">
                        <input type="text" class="form-input tpl-form-input" name="contract_date" placeholder="选择合约到期日期"
                               value="{{ contract_date }}">
                        <span class="input-group-btn input-group-btn">
                            <button class="btn btn-default" type="button"><span class="icon-calendar"></span> </button>
                        </span>
                    </div>
                    <!-- 快捷期限选项 -->
                    <div class="mt-3">
                        <button type="button" class="btn btn-default btn-xs contract-term" data-years="3">3年</button>
                        <button type="button" class="btn btn-default btn-xs contract-term" data-years="5">5年</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</script>


<script>
    $(function () {
        // 初始化Amaze UI选项卡
        
        /**
         * 审核操作
         */
        $('.j-audit').click(function () {
            var $this = $(this);
            layer.open({
                type: 1
                , title: '代理审核'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-dealer-withdraw1', $this.data())
                , success: function (layero) {
                    // 注册radio组件
                    // 注册日历组件
                    
                    // 处理合约期限快捷按钮点击事件
                    layero.find('.contract-term').on('click', function () {
                        var years = parseInt($(this).data('years'));
                        var today = new Date();
                        var endDate = new Date(today.getFullYear() + years, today.getMonth(), today.getDate());
                        
                        // 格式化日期为YYYY-MM-DD
                        var formattedDate = endDate.getFullYear() + '-' + 
                                           String(endDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                           String(endDate.getDate()).padStart(2, '0');
                        
                        // 填充到输入框
                        layero.find('input[name=contract_date]').val(formattedDate);
                    });
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-dealer-withdraw').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });

        /**
         * 显示驳回原因
         */
        $('.j-show-reason').click(function () {
            var $this = $(this);
            layer.alert($this.data('reason'), {title: '驳回原因'});
        });

        /**
         * 修改合约
         */
        $('.j-edit-contract').click(function () {
            var $this = $(this);
            layer.open({
                type: 1
                , title: '修改合约'
                , area: '340px'
                , offset: 'auto'
                , anim: 1
                , closeBtn: 1
                , shade: 0.3
                , btn: ['确定', '取消']
                , content: template('tpl-edit-contract', $this.data())
                , success: function (layero) {
                    // 注册日历组件
                    
                    // 处理合约期限快捷按钮点击事件
                    layero.find('.contract-term').on('click', function () {
                        var years = parseInt($(this).data('years'));
                        var today = new Date();
                        var endDate = new Date(today.getFullYear() + years, today.getMonth(), today.getDate());
                        
                        // 格式化日期为YYYY-MM-DD
                        var formattedDate = endDate.getFullYear() + '-' + 
                                           String(endDate.getMonth() + 1).padStart(2, '0') + '-' + 
                                           String(endDate.getDate()).padStart(2, '0');
                        
                        // 填充到输入框
                        layero.find('input[name=contract_date]').val(formattedDate);
                    });
                }
                , yes: function (index, layero) {
                    // 表单提交
                    layero.find('.form-edit-contract').ajaxSubmit({
                        type: 'post',
                        dataType: 'json',
                        success: function (result) {
                            result.code === 1 ? $.show_success(result.msg, result.url)
                                : $.show_error(result.msg);
                        }
                    });
                    layer.close(index);
                }
            });
        });

        /**
         * 确认打款
         */
        $('.j-money').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('user.balance/money') ?>";
            layer.confirm('确定已打款吗？', {title: '友情提示'}, function (index) {
                $.post(url, {id: id}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        /**
         * 微信付款
         */
        $('.j-wechat-pay').click(function () {
            var id = $(this).data('id');
            var url = "<?= url('user.balance/wechat_pay') ?>";
            layer.confirm('该操作 将使用微信支付企业付款到零钱功能，确定打款吗？', {title: '友情提示'}, function (index) {
                $.post(url, {id: id}, function (result) {
                    result.code === 1 ? $.show_success(result.msg, result.url)
                        : $.show_error(result.msg);
                });
                layer.close(index);
            });
        });

        // 保存代理商费用设置
        $('#saveFeeBtn').click(function () {
            var province_fee = $('#province_fee').val();
            var city_fee = $('#city_fee').val();
            var district_fee = $('#district_fee').val();
            
            // 验证数据
            if (province_fee === '' || city_fee === '' || district_fee === '') {
                $.show_error('请填写所有费用字段');
                return;
            }
            
            // 发送AJAX请求
            $.ajax({
                url: "<?= url('market.regionagent/saveFee') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    province_fee: province_fee,
                    city_fee: city_fee,
                    district_fee: district_fee
                },
                success: function (result) {
                    if (result.code === 1) {
                        $.show_success(result.msg, result.url);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function () {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
        
        // 保存提现税率设置
        $('#saveTaxRateBtn').click(function () {
            var withdraw_tax_rate = $('#withdraw_tax_rate').val();
            
            // 验证数据
            if (withdraw_tax_rate === '') {
                $.show_error('请填写税率');
                return;
            }
            
            // 验证税率范围
            var taxRate = parseFloat(withdraw_tax_rate);
            if (isNaN(taxRate) || taxRate < 0 || taxRate > 100) {
                $.show_error('税率必须在0-100之间');
                return;
            }
            
            // 发送AJAX请求
            $.ajax({
                url: "<?= url('market.regionagent/saveTaxRate') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    withdraw_tax_rate: withdraw_tax_rate
                },
                success: function (result) {
                    if (result.code === 1) {
                        $.show_success(result.msg, result.url);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function () {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
        
        // 保存代理商可申请数量设置
        $('#saveApplyCountBtn').click(function () {
            var province_count = $('#province_count').val();
            var city_count = $('#city_count').val();
            var district_count = $('#district_count').val();
            
            // 验证数据
            if (province_count === '' || city_count === '' || district_count === '') {
                $.show_error('请填写所有申请数量字段');
                return;
            }
            
            // 验证数据是否为非负整数
            if (parseInt(province_count) < 0 || parseInt(city_count) < 0 || parseInt(district_count) < 0) {
                $.show_error('申请数量不能为负数');
                return;
            }
            
            // 发送AJAX请求
            $.ajax({
                url: "<?= url('market.regionagent/saveApplyCount') ?>",
                type: 'POST',
                dataType: 'json',
                data: {
                    province_count: province_count,
                    city_count: city_count,
                    district_count: district_count
                },
                success: function (result) {
                    if (result.code === 1) {
                        $.show_success(result.msg, result.url);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function () {
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });

        /**
         * 显示代理商订单列表
         */
        $('.j-show-orders').click(function () {
            var $this = $(this);
            var agentId = $this.data('agent-id');
            var userId = $this.data('user-id');
            
            // 显示加载中
            var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
            
            // 获取订单列表
            $.ajax({
                url: "<?= url('market.regionagent/getAgentOrders') ?>",
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    agent_id: agentId,
                    _t: new Date().getTime()
                },
                success: function (result) {
                    layer.close(loadingIndex);
                    if (result.code === 1) {
                        var orders = result.data || [];
                        // 使用Handlebars模板渲染（如果没有Handlebars，使用简单的字符串拼接）
                        var html = '<div class="pt-3"><div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">';
                        html += '<table class="table-base   whitespace-nowrap">';
                        html += '<thead><tr><th>订单号</th><th>订单金额</th><th>收入</th><th>结算状态</th></tr></thead>';
                        html += '<tbody>';
                        if (orders.length > 0) {
                            $.each(orders, function(i, order) {
                                html += '<tr>';
                                html += '<td>' + (order.order_no || '-') + '</td>';
                                html += '<td>¥' + (order.pay_price || '0.00') + '</td>';
                                html += '<td style="color: #52C41A; font-weight: bold;">¥' + (order.income || '0.00') + '</td>';
                                html += '<td>' + (order.is_settle_text || '-') + '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="4" class="text-center">暂无订单数据</td></tr>';
                        }
                        html += '</tbody></table></div></div>';
                        
                        layer.open({
                            type: 1,
                            title: '代理商订单列表',
                            area: ['1100px', '600px'],
                            content: html,
                            btn: ['关闭'],
                            yes: function(index) {
                                layer.close(index);
                            }
                        });
                    } else {
                        $.show_error(result.msg || '获取订单列表失败');
                    }
                },
                error: function () {
                    layer.close(loadingIndex);
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });

        /**
         * 显示代理商收入订单列表
         */
        $('.j-show-income-orders').click(function () {
            var $this = $(this);
            var agentId = $this.data('agent-id');
            var userId = $this.data('user-id');
            
            // 显示加载中
            var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
            
            // 获取收入订单列表
            $.ajax({
                url: "<?= url('market.regionagent/getAgentIncomeOrders') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    agent_id: agentId
                },
                success: function (result) {
                    layer.close(loadingIndex);
                    if (result.code === 1) {
                        var data = result.data || {};
                        var orders = data.list || [];
                        var total = data.total || 0;
                        
                        // 使用简单的字符串拼接渲染
                        var html = '<div class="pt-3"><div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">';
                        html += '<table class="table-base   whitespace-nowrap">';
                        html += '<thead><tr><th>订单号</th><th>收货人</th><th>联系电话</th><th>订单金额</th><th>分润收入</th><th>结算状态</th><th>订单状态</th><th>创建时间</th></tr></thead>';
                        html += '<tbody>';
                        if (orders.length > 0) {
                            $.each(orders, function(i, order) {
                                html += '<tr>';
                                html += '<td>' + (order.order_no || '-') + '</td>';
                                html += '<td>' + (order.name || '-') + '</td>';
                                html += '<td>' + (order.phone || '-') + '</td>';
                                html += '<td>¥' + (order.pay_price || '0.00') + '</td>';
                                html += '<td class="text-red-600">¥' + (order.income || '0.00') + '</td>';
                                html += '<td>' + (order.is_settle_text || '-') + '</td>';
                                html += '<td>' + (order.order_status_text || '-') + '</td>';
                                html += '<td>' + (order.create_time_text || '-') + '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="8" class="text-center">暂无收入订单数据</td></tr>';
                        }
                        html += '</tbody></table>';
                        if (total > 0) {
                            html += '<div class="mt-3 text-center"><div class="inline-flex items-center gap-1">共 ' + total + ' 条记录</div></div>';
                        }
                        html += '</div></div>';
                        
                        layer.open({
                            type: 1,
                            title: '代理商收入订单列表',
                            area: ['1000px', '600px'],
                            content: html,
                            btn: ['关闭'],
                            yes: function(index) {
                                layer.close(index);
                            }
                        });
                    } else {
                        $.show_error(result.msg || '获取收入订单列表失败');
                    }
                },
                error: function () {
                    layer.close(loadingIndex);
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });

        /**
         * 显示代理商订单列表（支持筛选）
         */
        $('.j-show-orders-filter').click(function () {
            var $this = $(this);
            var agentId = $this.data('agent-id');
            
            // 显示加载中
            var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
            
            // 获取订单列表
            $.ajax({
                url: "<?= url('market.regionagent/getAgentOrdersWithFilter') ?>",
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    agent_id: agentId,
                    _t: new Date().getTime()
                },
                success: function (result) {
                    layer.close(loadingIndex);
                    if (result.code === 1) {
                        var orders = result.data || [];
                        // 使用Handlebars模板渲染（如果没有Handlebars，使用简单的字符串拼接）
                        var html = '<div class="pt-3"><div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">';
                        html += '<table class="table-base   whitespace-nowrap">';
                        html += '<thead><tr><th>订单号</th><th>订单金额</th><th>收入</th><th>结算状态</th><th>筛选条件</th></tr></thead>';
                        html += '<tbody>';
                        if (orders.length > 0) {
                            $.each(orders, function(i, order) {
                                html += '<tr>';
                                html += '<td>' + (order.order_no || '-') + '</td>';
                                html += '<td>¥' + (order.pay_price || '0.00') + '</td>';
                                html += '<td style="color: #52C41A; font-weight: bold;">¥' + (order.income || '0.00') + '</td>';
                                html += '<td>' + (order.is_settle_text || '-') + '</td>';
                                html += '<td>' + (order.filter_text || '-') + '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="4" class="text-center">暂无订单数据</td></tr>';
                        }
                        html += '</tbody></table></div></div>';
                        
                        layer.open({
                            type: 1,
                            title: '代理商订单列表',
                            area: ['1100px', '600px'],
                            content: html,
                            btn: ['关闭'],
                            yes: function(index) {
                                layer.close(index);
                            }
                        });
                    } else {
                        $.show_error(result.msg || '获取订单列表失败');
                    }
                },
                error: function () {
                    layer.close(loadingIndex);
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
        
        /**
         * 重置筛选条件
         */
        function resetAgentOrderFilter() {
            $('.j-show-orders-filter select').val('');
            $('.j-show-orders').click();
        }
        
        /**
         * 筛选订单
         */
        function filterAgentOrders(agentId, filter) {
            var $this = $(this);
            // 显示加载中
            var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
            
            // 获取订单列表
            $.ajax({
                url: "<?= url('market.regionagent/getAgentOrdersWithFilter') ?>",
                type: 'GET',
                dataType: 'json',
                cache: false,
                data: {
                    agent_id: agentId,
                    filter: filter,
                    _t: new Date().getTime()
                },
                success: function (result) {
                    layer.close(loadingIndex);
                    if (result.code === 1) {
                        var orders = result.data || [];
                        // 使用Handlebars模板渲染（如果没有Handlebars，使用简单的字符串拼接）
                        var html = '<div class="pt-3"><div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">';
                        html += '<table class="table-base   whitespace-nowrap">';
                        html += '<thead><tr><th>订单号</th><th>订单金额</th><th>收入</th><th>结算状态</th><th>筛选条件</th></tr></thead>';
                        html += '<tbody>';
                        if (orders.length > 0) {
                            $.each(orders, function(i, order) {
                                html += '<tr>';
                                html += '<td>' + (order.order_no || '-') + '</td>';
                                html += '<td>¥' + (order.pay_price || '0.00') + '</td>';
                                html += '<td style="color: #52C41A; font-weight: bold;">¥' + (order.income || '0.00') + '</td>';
                                html += '<td>' + (order.is_settle_text || '-') + '</td>';
                                html += '<td>' + (order.filter_text || '-') + '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="4" class="text-center">暂无订单数据</td></tr>';
                        }
                        html += '</tbody></table></div></div>';
                        
                        layer.open({
                            type: 1,
                            title: '代理商订单列表',
                            area: ['1100px', '600px'],
                            content: html,
                            btn: ['关闭'],
                            yes: function(index) {
                                layer.close(index);
                            }
                        });
                    } else {
                        $.show_error(result.msg || '获取订单列表失败');
                    }
                },
                error: function () {
                    layer.close(loadingIndex);
                    $.show_error('请求失败，请稍后重试');
                }
            });
        }
        
        /**
         * 显示代理商提现信息（微信支付）
         */
        $('.j-show-withdraw-list').click(function () {
            var $this = $(this);
            var userId = $this.data('user-id');
            
            // 显示加载中
            var loadingIndex = layer.load(1, {shade: [0.3, '#000']});
            
            // 获取提现信息
            $.ajax({
                url: "<?= url('market.regionagent/getAgentWithdrawInfo') ?>",
                type: 'GET',
                dataType: 'json',
                data: {
                    user_id: userId
                },
                success: function (result) {
                    layer.close(loadingIndex);
                    if (result.code === 1) {
                        var data = result.data || {};
                        var user = data.user || {};
                        var withdrawList = data.withdraw_list || [];
                        
                        // 构建HTML内容
                        var html = '<div class="pt-3">';
                        
                        // 用户基本信息
                        html += '<div class="mb-4" style="border-bottom: 1px solid #eee; padding-bottom: 15px;">';
                        html += '<h3 style="margin-bottom: 10px;">用户信息</h3>';
                        html += '<p><strong>用户ID：</strong>' + (user.user_id || '-') + '</p>';
                        html += '<p><strong>用户昵称：</strong>' + (user.nickName || '-') + '</p>';
                        html += '<p><strong>当前余额：</strong>¥' + (user.balance || '0.00') + '</p>';
                        html += '</div>';
                        
                        // 提现记录列表
                        html += '<div class="overflow-x-auto" style="max-height: 400px; overflow-y: auto;">';
                        html += '<h3 style="margin-bottom: 10px;">提现记录（共' + (data.withdraw_count || 0) + '条）</h3>';
                        html += '<table class="table-base   whitespace-nowrap">';
                        html += '<thead><tr><th>提现金额</th><th>支付方式</th><th>收款信息</th><th>税费</th><th>实际到账</th><th>审核状态</th><th>申请时间</th><th>审核时间</th><th>操作</th></tr></thead>';
                        html += '<tbody>';
                        if (withdrawList.length > 0) {
                            $.each(withdrawList, function(i, withdraw) {
                                html += '<tr>';
                                html += '<td>¥' + (withdraw.money || '0.00') + '</td>';
                                html += '<td>' + (withdraw.pay_type_text || '-') + '</td>';
                                html += '<td>';
                                if (withdraw.pay_type == 10) {
                                    html += '<span>微信</span>';
                                } else if (withdraw.pay_type == 30) {
                                    html += '<p>姓名：' + (withdraw.bank_name || '-') + '</p>';
                                    html += '<p>开户行：' + (withdraw.bank_account || '-') + '</p>';
                                    html += '<p>卡号：' + (withdraw.bank_card || '-') + '</p>';
                                } else {
                                    html += '<span>-</span>';
                                }
                                html += '</td>';
                                html += '<td>¥' + (withdraw.tax_amount || '0.00') + '</td>';
                                html += '<td>¥' + (withdraw.actual_money || '0.00') + '</td>';
                                html += '<td>' + (withdraw.apply_status_text || '-') + '</td>';
                                html += '<td>' + (withdraw.create_time_text || '-') + '</td>';
                                html += '<td>' + (withdraw.audit_time_text || '-') + '</td>';
                                html += '<td>';
                                if (withdraw.apply_status == 20 && withdraw.pay_type == 10 && withdraw.pay_status == 10) {
                                    html += '<a href="javascript:void(0);" class="j-wechat-pay-agent" data-id="' + withdraw.id + '" data-money="' + withdraw.money + '"><i class="icon-money"></i> 微信打款</a>';
                                } else if (withdraw.pay_status == 20) {
                                    html += '<span class="text-green-600">已打款</span>';
                                } else {
                                    html += '<span>-</span>';
                                }
                                html += '</td>';
                                html += '</tr>';
                            });
                        } else {
                            html += '<tr><td colspan="9" class="text-center">暂无提现记录</td></tr>';
                        }
                        html += '</tbody></table>';
                        html += '</div>';
                        html += '</div>';
                        
                        layer.open({
                            type: 1,
                            title: '代理商提现信息',
                            area: ['900px', '600px'],
                            content: html,
                            btn: ['关闭'],
                            yes: function(index) {
                                layer.close(index);
                            }
                        });
                    } else {
                        $.show_error(result.msg || '获取提现信息失败');
                    }
                },
                error: function () {
                    layer.close(loadingIndex);
                    $.show_error('请求失败，请稍后重试');
                }
            });
        });
    });
    
    /**
     * 代理商提现微信打款
     */
    $('.j-wechat-pay-agent').click(function () {
        var $this = $(this);
        var id = $this.data('id');
        var money = $this.data('money');
        
        layer.confirm('该操作 将使用微信支付企业付款到零钱功能，确定打款吗？', {title: '友情提示'}, function (index) {
            $.ajax({
                url: "<?= url('market.regionagent/withdraw_wechat_pay') ?>",
                type: 'post',
                dataType: 'json',
                data: {id: id},
                success: function (result) {
                    if (result.code === 1) {
                        $.show_success(result.msg);
                        setTimeout(function () {
                            location.reload();
                        }, 1500);
                    } else {
                        $.show_error(result.msg);
                    }
                },
                error: function () {
                    $.show_error('请求失败，请稍后重试');
                }
            });
            layer.close(index);
        });
    });
</script>

