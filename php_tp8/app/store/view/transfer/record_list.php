<div class="row-content">
    <div class="row">
        <div class="w-full">
            <div class="widget">
                <div class="widget-head">
                    <div class="widget-title">转账记录列表</div>
                    <div class="widget-function float-right">
                        <a class="btn btn-success btn-xs" href="<?= url('transfer/scenes') ?>">场景管理</a>
                    </div>
                </div>
                <div class="widget-body float-right">
                    <!-- 搜索栏 -->
                    <form class=" " action="<?= url('transfer/records') ?>" method="get">
                        <div class="form-group ">
                            <select name="transfer_state" style="width:130px" class="form-select">
                                <option value="">— 转账状态 —</option>
                                <option value="INIT" <?= request()->param('transfer_state') === 'INIT' ? 'selected' : '' ?>>待处理</option>
                                <option value="PROCESSING" <?= request()->param('transfer_state') === 'PROCESSING' ? 'selected' : '' ?>>处理中</option>
                                <option value="SUCCESS" <?= request()->param('transfer_state') === 'SUCCESS' ? 'selected' : '' ?>>转账成功</option>
                                <option value="FAIL" <?= request()->param('transfer_state') === 'FAIL' ? 'selected' : '' ?>>转账失败</option>
                            </select>
                        </div>
                        <div class="form-group ">
                            <select name="apply_status" style="width:130px" class="form-select">
                                <option value="">— 审核状态 —</option>
                                <option value="10" <?= request()->param('apply_status') === '10' ? 'selected' : '' ?>>待审核</option>
                                <option value="20" <?= request()->param('apply_status') === '20' ? 'selected' : '' ?>>审核通过</option>
                                <option value="30" <?= request()->param('apply_status') === '30' ? 'selected' : '' ?>>驳回</option>
                                <option value="40" <?= request()->param('apply_status') === '40' ? 'selected' : '' ?>>已打款</option>
                            </select>
                        </div>
                        <div class="form-group ">
                            <input type="text" name="bill_no" placeholder="转账单号" value="<?= request()->param('bill_no') ?>" style="width:180px">
                        </div>
                        <div class="form-group ">
                            <input type="text" name="start_time" placeholder="开始日期" value="<?= request()->param('start_time') ?>" style="width:120px">
                        </div>
                        <div class="form-group ">
                            <input type="text" name="end_time" placeholder="结束日期" value="<?= request()->param('end_time') ?>" style="width:120px">
                        </div>
                        <div class="form-group ">
                            <button class="btn btn-default" type="submit">搜索</button>
                        </div>
                    </form>

                    <div class="w-full">
                        <div class="overflow-x-auto whitespace-nowrap">
                            <table class="table-base table-base-borderless  whitespace-nowrap">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>用户</th>
                                    <th>金额</th>
                                    <th>打款方式</th>
                                    <th>审核状态</th>
                                    <th>商家转账单号</th>
                                    <th>微信转账单号</th>
                                    <th>转账状态</th>
                                    <th>申请时间</th>
                                    <th>处理时间</th>
                                    <th>驳回原因</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td><?= $item['nickName'] ?: '用户'.$item['user_id'] ?></td>
                                        <td>¥<?= $item['money'] ?></td>
                                        <td>
                                            <?php
                                            $payTypeMap = [10 => '微信', 20 => '支付宝', 30 => '银行卡'];
                                            echo $payTypeMap[$item['pay_type']] ?? $item['pay_type'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $applyStatusMap = [
                                                10 => '<span class="badge badge-warning text-xs">待审核</span>',
                                                20 => '<span class="badge badge-primary text-xs">审核通过</span>',
                                                30 => '<span class="badge badge-danger text-xs">驳回</span>',
                                                40 => '<span class="badge badge-success text-xs">已打款</span>',
                                            ];
                                            echo $applyStatusMap[$item['apply_status']] ?? $item['apply_status'];
                                            ?>
                                        </td>
                                        <td><?= $item['wx_out_bill_no'] ?: '—' ?></td>
                                        <td><?= $item['wx_bill_id'] ?: '—' ?></td>
                                        <td>
                                            <?php if ($item['wx_transfer_state'] === 'SUCCESS'): ?>
                                                <span class="badge badge-success text-xs">转账成功</span>
                                            <?php elseif ($item['wx_transfer_state'] === 'PROCESSING'): ?>
                                                <span class="badge badge-primary text-xs">处理中</span>
                                            <?php elseif ($item['wx_transfer_state'] === 'FAIL'): ?>
                                                <span class="badge badge-danger text-xs">转账失败</span>
                                            <?php elseif ($item['wx_transfer_state'] === 'INIT'): ?>
                                                <span class="badge badge-warning text-xs">待处理</span>
                                            <?php else: ?>
                                                <span class="badge badge-default text-xs">未发起</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['create_time'] ? date('Y-m-d H:i:s', $item['create_time']) : '—' ?></td>
                                        <td><?= $item['audit_time'] ? date('Y-m-d H:i:s', $item['audit_time']) : '—' ?></td>
                                        <td><?= $item['reject_reason'] ?: '—' ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="11" class="text-center">暂无转账记录</td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="lg:w-full ">
                            <div class="float-right"><?= $list->render() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
