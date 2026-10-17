<?php

namespace app\store\controller\apps\dealer;

use app\store\controller\Controller;
use app\store\model\dealer\User as UserModel;
use app\store\model\dealer\Referee as RefereeModel;
use app\store\model\dealer\Setting as SettingModel;

/**
 * 分销商管理
 * Class User
 * @package app\store\controller\apps\dealer
 */
class User extends Controller
{
    /**
     * 分销商用户列表
     * @param string $search
     * @return mixed
     */
    public function index($search = '')
    {
        $model = new UserModel;
        return $this->fetch('index', [
            'list' => $model->getList($search),
            'basicSetting' => SettingModel::getItem('basic'),
        ]);
    }

    /**
     * 下级粉丝列表
     * @param string $user_id
     * @param int $level
     * @return mixed
     */
    public function fans($user_id, $level = -1)
    {
        $model = new RefereeModel;
        return $this->fetch('fans', [
            'list' => $model->getList($user_id, $level),
            'basicSetting' => SettingModel::getItem('basic'),
        ]);
    }

    /**
     * 编辑分销商
     * @param $dealer_id
     * @return mixed
     */
    public function edit($dealer_id)
    {
        $model = UserModel::detail($dealer_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->postData('model'))) {
            return $this->renderSuccess('更新成功', url('apps.dealer.user/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除分销商
     * @param $dealer_id
     * @return array|bool
     */
    public function delete($dealer_id)
    {
        $model = UserModel::detail($dealer_id);
        if (!$model->setDelete()) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 分销商二维码
     * @param $dealer_id
     */
    public function qrcode($dealer_id)
    {
        if (!class_exists('app\common\service\qrcode\Poster')) {
            return $this->renderError('分销海报服务未配置');
        }
        $model = UserModel::detail($dealer_id);
        $Qrcode = new \app\common\service\qrcode\Poster($model);
        $this->redirect($Qrcode->getImage());
    }

}
