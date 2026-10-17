<?php
namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\User as DealerUser;
use app\api\model\dealer\Withdraw as DealerWithdraw;
use app\api\model\dealer\Setting as SettingModel;

/**
 * 分销提现（对齐 RN dealer.ts submitWithdraw / withdrawList）
 */
class Withdraw extends Controller
{
    /** 提交提现 */
    public function submit()
    {
        $user = $this->getUser();
        $dealer = DealerUser::detail($user['user_id']);
        if (empty($dealer)) {
            return $this->renderError('您还不是分销商');
        }
        $data = $this->request->param('data');
        $formData = $data ? json_decode(htmlspecialchars_decode($data), true) : $this->request->post();
        if (empty($formData['money']) || (float)$formData['money'] <= 0) {
            return $this->renderError('请输入正确的提现金额');
        }
        if ((float)$formData['money'] > (float)$dealer['money']) {
            return $this->renderError('提现金额不能大于可提现佣金');
        }
        $setting = SettingModel::getItem('settlement');
        $payTypes = $setting['pay_type'] ?? [];
        $payType = (int)($formData['pay_type'] ?? 10);
        if (!empty($payTypes) && !in_array((string)$payType, array_map('strval', $payTypes), true)) {
            return $this->renderError('提现方式不正确');
        }
        if ((float)$formData['money'] < (float)($setting['min_money'] ?? 0)) {
            return $this->renderError('提现金额不能小于最低提现金额');
        }
        if (DealerWithdraw::submit($dealer, $formData)) {
            return $this->renderSuccess([], '提交成功');
        }
        return $this->renderError('提交失败');
    }

    /** 提现明细列表 */
    public function lists()
    {
        $user = $this->getUser();
        $status = $this->request->param('status', -1);
        $list = DealerWithdraw::getList($user['user_id'], $status);
        $words = SettingModel::getItem('words');
        return $this->renderSuccess(compact('list', 'words'));
    }
}
