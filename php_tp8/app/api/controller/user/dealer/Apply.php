<?php
namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\Apply as DealerApply;

/**
 * 分销申请提交（对齐 RN dealer.ts submitApply）
 */
class Apply extends Controller
{
    public function submit()
    {
        $user = $this->getUser();
        $name = $this->request->param('name');
        $mobile = $this->request->param('mobile');
        if (empty($name) || empty($mobile)) {
            return $this->renderError('请填写姓名和手机号');
        }
        if (DealerApply::submit($user, $name, $mobile)) {
            return $this->renderSuccess([], '提交成功');
        }
        return $this->renderError('提交失败');
    }
}
