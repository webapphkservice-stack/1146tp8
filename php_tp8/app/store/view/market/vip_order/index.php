<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title ">VIP开通订单</div>
                </div>
                <div class="widget-body float-right">
                    <!-- 筛选 -->
                    <div class="page_toolbar mb-1 ">
                        <form id="form-search" class="toolbar-form toolbar-form--inline" action="">
                            <div class="sm:w-full md:w-3/4">
                                    <div class="form-group ">
                                        <?php $payStatus = $request->get('pay_status'); ?>
                                        <select name="pay_status" class="form-select">
                                            <option value="0" <?= empty($payStatus) ? 'selected' : '' ?>>全部状态</option>
                                            <option value="10" <?= $payStatus == 10 ? 'selected' : '' ?>>待支付</option>
                                            <option value="20" <?= $payStatus == 20 ? 'selected' : '' ?>>已支付</option>
                                        </select>
                                    <div class="form-group ">
                                        <?php $payType = $request->get('pay_type'); ?>
                                        <select name="pay_type" class="form-select">
                                            <option value="0" <?= empty($payType) ? 'selected' : '' ?>>全部方式</option>
                                            <option value="10" <?= $payType == 10 ? 'selected' : '' ?>>余额支付</option>
                                            <option value="20" <?= $payType == 20 ? 'selected' : '' ?>>微信支付</option>
                                            <option value="40" <?= $payType == 40 ? 'selected' : '' ?>>支付宝</option>
                                            <option value="60" <?= $payType == 60 ? 'selected' : '' ?>>虚拟支付</option>
                                            <option value="70" <?= $payType == 70 ? 'selected' : '' ?>>代币支付</option>
                                        </select>
                                    </div>
                                    <div class="form-group ">
                                        <div class="input-group input-group-sm tpl-form-border-form">
                                            <input type="text" class="form-input" name="keyword"
                                                   placeholder="订单号/用户昵称" value="<?= $request->get('keyword') ?>">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="sm:w-full">
                        <table width="100%" class="table-base text-center whitespace-nowrap mb-1">
                            <thead>
                            <tr>
                                <th>订单号</th>
                                <th>用户</th>
                                <th>VIP套餐</th>
                                <th>支付金额</th>
                                <th>支付方式</th>
                                <th>支付状态</th>
                                <th>VIP到期时间</th>
                                <th>下单时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item):
                                // TP8 的 PayType 枚举只有常量、没有 data() 方法，改用映射表（60/70 为业务扩展支付方式）
                                $payTypeMap = [
                                    \app\common\enum\order\PayType::BALANCE => '余额支付',
                                    \app\common\enum\order\PayType::WECHAT  => '微信支付',
                                    \app\common\enum\order\PayType::FRIEND  => '好友代付',
                                    \app\common\enum\order\PayType::ALIPAY  => '支付宝',
                                    \app\common\enum\order\PayType::STRIPE  => '银行卡',
                                    60 => '虚拟支付',
                                    70 => '代币支付',
                                ];
                                $payTypeName = $payTypeMap[(int)$item['pay_type']] ?? '未知';
                                ?>
                                <tr>
                                    <td class="align-middle"><?= $item['order_no'] ?></td>
                                    <td class="align-middle"><?= $item['nickName'] ?: '游客' ?></td>
                                    <td class="align-middle"><?= $item['plan_name'] ?></td>
                                    <td class="align-middle">￥<?= $item['pay_price'] ?></td>
                                    <td class="align-middle"><?= $payTypeName ?></td>
                                    <td class="align-middle">
                                        <?php if ($item['pay_status'] == 20): ?>
                                            <span class="badge badge-success">已支付</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">待支付</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php
                                    // 时间字段经模型 toArray() 可能已是 'Y-m-d H:i:s' 字符串，
                                    // date() 第二参要求 int，故先做数值/字符串兼容转换
                                    $expireTs = empty($item['vip_expire_time']) ? 0
                                        : (is_numeric($item['vip_expire_time']) ? (int)$item['vip_expire_time'] : strtotime($item['vip_expire_time']));
                                    $createTs = is_numeric($item['create_time']) ? (int)$item['create_time'] : strtotime($item['create_time']);
                                    ?>
                                    <td class="align-middle"><?= $expireTs ? date('Y-m-d', $expireTs) : '-' ?></td>
                                    <td class="align-middle"><?= date('Y-m-d H:i', $createTs) ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="sm:w-full flex justify-center">
                            <?= $list->render() ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
