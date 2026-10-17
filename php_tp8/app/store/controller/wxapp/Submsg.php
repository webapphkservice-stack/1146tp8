<?php

namespace app\store\controller\wxapp;

use app\store\controller\Controller;
use app\store\model\Setting as SettingModel;
use app\store\service\wxapp\SubMsg as SubMsgService;

/**
 * 小程序订阅消息设置
 * Class Submsg
 * @package app\store\controller\wxapp
 */
class Submsg extends Controller
{
    /**
     * 订阅消息默认配置结构
     *
     * 视图 wxapp/submsg/index.php 直接取 $values['order']['payment']['template_id']，
     * 若 setting 表尚无当前 wxapp 的 submsg 记录（或结构缺键），getItem() 返回空数组会
     * 触发 "Undefined array key 'order'"。这里给出完整默认结构做兜底。
     */
    const DEFAULT_VALUES = '{"order":{"payment":{"template_id":"","keywords":["character_string1","time2","amount4","thing3"],"title":"新订单提醒"},"delivery":{"template_id":"","keywords":["character_string1","thing2","name12","thing11","thing17"],"title":"订单发货通知"},"refund":{"template_id":"","keywords":["phrase1","thing6","character_string2","date3","thing4"],"title":"售后状态通知"}},"sharing":{"active_status":{"template_id":"","keywords":["thing1","amount5","number7","thing3","thing6"],"title":"拼团进度通知"}},"dealer":{"apply":{"template_id":"","keywords":["date1","phrase2","date3","thing4"],"title":"代理商入驻审核通知"},"withdraw_01":{"template_id":"","keywords":["amount1","thing3","thing4"],"title":"提现成功通知"},"withdraw_02":{"template_id":"","keywords":["amount1","time3","thing4"],"title":"提现失败通知"}}}';

    /**
     * 小程序订阅消息设置
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        if (!$this->request->isAjax()) {
            // 用默认结构兜底，避免未配置过的 wxapp 打开页面时数组键不存在
            $default = json_decode(self::DEFAULT_VALUES, true);
            $values  = array_replace_recursive($default, SettingModel::getItem('submsg') ?: []);
            return $this->fetch('index', compact('values'));
        }
        $model = new SettingModel;
        if ($model->edit('submsg', $this->postData('submsg'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 一键添加订阅消息
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function shuttle()
    {
        $SubMsgService = new SubMsgService;
        if ($SubMsgService->shuttle()) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($SubMsgService->getError() ?: '操作失败');
    }

}