<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\dealer\Withdraw as WithdrawModel;

/**
 * 分销商提现申请
 * Class Withdraw
 * @package app\store\controller\apps\dealer
 */
class Withdraw extends Controller
{
    /**
     * 提现记录列表
     * @param int $user_id
     * @param int $apply_status
     * @param int $pay_type
     * @param string $search
     * @return mixed
     */
    public function index($user_id = null, $apply_status = -1, $pay_type = -1, $search = '')
    {
        $model = new WithdrawModel;
        return $this->fetch('index', [
            'list' => $model->getList($user_id, $apply_status, $pay_type, $search)
        ]);
    }

    /**
     * 提现审核
     * @param $id
     * @return array
     */
    public function submit($id)
    {
        $model = WithdrawModel::detail($id);
        if ($model->submit($this->postData('withdraw'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 确认打款
     * @param $id
     * @return array
     */
    public function money($id)
    {
        $model = WithdrawModel::detail($id);
        if ($model->money()) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 微信支付企业付款V2(已废弃)
     * @param $id
     * @return array|bool
     */
    public function wechat_pay($id)
    {
        $model = WithdrawModel::detail($id);
        if ($model->wechatPay()) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 微信支付商家转账到零钱V3
     * @param $id
     * @return array|bool
     */
    public function wechat_pay_v3($id)
    {
        $model = WithdrawModel::detail($id);
        if ($model->wechatPayV3()) {
            return $this->renderSuccess('转账成功，请稍后查询状态');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 查询微信转账状态
     * @param $id
     * @return array
     */
    public function query_transfer($id)
    {
        $model = WithdrawModel::detail($id);
        $result = $model->queryWechatTransfer();

        if (isset($result['error'])) {
            return $this->renderError($result['error']);
        }

        $stateText = isset($result['state']) ? $result['state'] : '';
        return $this->renderSuccess('查询成功', ['state_text' => $stateText, 'data' => $result]);
    }

}
