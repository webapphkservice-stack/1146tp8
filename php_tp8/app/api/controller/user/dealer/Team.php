<?php
namespace app\api\controller\user\dealer;

use app\api\controller\Controller;
use app\api\model\dealer\User as DealerUser;
use app\api\model\dealer\Referee as DealerReferee;
use app\api\model\dealer\Setting as SettingModel;

/**
 * 分销团队（对齐 RN dealer.ts teamList）
 */
class Team extends Controller
{
    public function lists()
    {
        $user = $this->getUser();
        $level = $this->request->param('level', -1);
        $dealer = DealerUser::detail($user['user_id']);
        $list = DealerReferee::getList($user['user_id'], $level);
        $setting = SettingModel::getItem('basic');
        $words = SettingModel::getItem('words');
        return $this->renderSuccess(compact('dealer', 'list', 'setting', 'words'));
    }
}
