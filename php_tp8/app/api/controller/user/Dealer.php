<?php
namespace app\api\controller\user;

use app\api\controller\Controller;
use app\api\model\dealer\Setting as SettingModel;
use app\api\model\dealer\User as DealerUser;
use app\api\model\dealer\Apply as DealerApply;
use app\api\model\dealer\Withdraw as DealerWithdraw;
use app\api\model\dealer\Referee as DealerReferee;
use app\api\model\dealer\Order as DealerOrder;

/**
 * 分销中心（对齐 RN dealer.ts 全部接口）
 *
 * 路由说明：RN 使用带点的 3 段 URL（user.dealer/team/lists 等）。
 * TP8 把 "user.dealer" 解析为单级控制器 user\Dealer，第 3 段被当作多余路径，
 * 因此本控制器用 pathinfo 的第 3 段区分 submit / lists / poster，
 * 与项目既有约定（balance/log/lists → 单级 Balance 控制器）一致。
 */
class Dealer extends Controller
{
    /** 取子操作段（submit|lists|poster），兼容点号 URL（user.dealer/apply/submit）与斜杠 URL（user/dealer/apply/submit） */
    private function sub()
    {
        $pi = trim($this->request->pathinfo(), '/');
        if (preg_match('#^(?:user\.dealer|user/dealer)(?:/(.+))?$#', $pi, $m)) {
            $rest = trim($m[1] ?? '', '/');
            if ($rest === '') {
                return '';
            }
            $parts = explode('/', $rest);
            return $parts[1] ?? '';
        }
        $parts = explode('/', $pi);
        return $parts[2] ?? '';
    }

    /** 从 getUser() 安全取 user_id，未登录时返回 0 */
    private function uid()
    {
        $u = $this->getUser();
        return $u ? (int)($u['user_id'] ?? 0) : 0;
    }

    /** 分销中心 */
    public function center()
    {
        $uid = $this->uid();
        return $this->renderSuccess([
            'setting' => SettingModel::getItem('basic'),
            'dealer'  => $uid ? DealerUser::detail($uid) : null,
            'words'   => SettingModel::getItem('words'),
        ]);
    }

    /** 申请页 / 提交申请（user.dealer/apply、user.dealer/apply/submit） */
    public function apply()
    {
        $uid = $this->uid();
        if ($this->sub() === 'submit') {
            if (!$uid) {
                return $this->renderError('请先登录');
            }
            $name = $this->request->param('name');
            $mobile = $this->request->param('mobile');
            if (empty($name) || empty($mobile)) {
                return $this->renderError('请填写姓名和手机号');
            }
            $user = $this->getUser();
            if (DealerApply::submit($user, $name, $mobile)) {
                return $this->renderSuccess([], '提交成功');
            }
            return $this->renderError('提交失败');
        }
        return $this->renderSuccess([
            'setting'    => SettingModel::getItem('condition'),
            'is_applying' => $uid ? DealerApply::isApplying($uid) : false,
            'dealer'     => $uid ? DealerUser::detail($uid) : null,
        ]);
    }

    /** 提现页 / 提现明细 / 提交提现（user.dealer/withdraw[/lists|/submit]） */
    public function withdraw()
    {
        $uid = $this->uid();
        if (!$uid) {
            return $this->renderError('请先登录');
        }
        $user = $this->getUser();
        $sub = $this->sub();
        if ($sub === 'submit') {
            $dealer = DealerUser::detail($uid);
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
        if ($sub === 'lists') {
            $status = $this->request->param('status', -1);
            return $this->renderSuccess([
                'list'  => DealerWithdraw::getList($uid, $status),
                'words' => SettingModel::getItem('words'),
            ]);
        }
        return $this->renderSuccess([
            'setting' => SettingModel::getItem('settlement'),
            'dealer'  => DealerUser::detail($uid),
            'words'   => SettingModel::getItem('words'),
        ]);
    }

    /** 团队列表（user.dealer/team/lists） */
    public function team()
    {
        $uid = $this->uid();
        if (!$uid) {
            return $this->renderError('请先登录');
        }
        $level = $this->request->param('level', -1);
        return $this->renderSuccess([
            'dealer'  => DealerUser::detail($uid),
            'list'    => DealerReferee::getList($uid, $level),
            'setting' => SettingModel::getItem('basic'),
            'words'   => SettingModel::getItem('words'),
        ]);
    }

    /** 分销订单列表（user.dealer/order/lists） */
    public function order()
    {
        $uid = $this->uid();
        if (!$uid) {
            return $this->renderError('请先登录');
        }
        $settled = $this->request->param('settled', -1);
        return $this->renderSuccess([
            'list'  => DealerOrder::getList($uid, $settled),
            'words' => SettingModel::getItem('words'),
        ]);
    }

    /** 分销海报 / 二维码（user.dealer/qrcode/poster） */
    public function qrcode()
    {
        $uid = $this->uid();
        if (!$uid) {
            return $this->renderError('请先登录');
        }
        return $this->renderSuccess([
            'qrcode' => 'pages/dealer/apply?referee_id=' . $uid,
            'words'  => SettingModel::getItem('words'),
        ]);
    }
}
