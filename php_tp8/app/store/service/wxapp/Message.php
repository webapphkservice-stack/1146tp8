<?php

namespace app\store\service\wxapp;

/**
 * 推送模板消息服务类
 *
 * 说明：微信小程序「模板消息」接口已于 2020 年 1 月废弃，配套的 WxTplMsg / FormId 服务
 * 在本项目 TP8 迁移中未同步。此处仅保留后台页面与发送状态记录，不再实际调用已废弃的
 * 微信接口，避免运行时 fatal。如后续改用「订阅消息」(subscribeMessage)，可在此处重写。
 *
 * Class Message
 * @package app\store\service\wxapp
 */
class Message
{
    // 分割符号
    const SEPARATOR = ',';

    /** @var array $stateSet 状态集 */
    private $stateSet = [];

    /**
     * 执行发送
     * @param $data
     * @return bool
     */
    public function send($data)
    {
        // 用户id集
        $userIdsArr = !strstr($data['user_id'], self::SEPARATOR) ? [$data['user_id']]
            : explode(self::SEPARATOR, $data['user_id']);
        // 批量发送（实际接口已废弃，仅记录状态）
        foreach ($userIdsArr as $userId) {
            $this->sendTemplateMessage($userId, $data);
        }
        return true;
    }

    /**
     * 发送模板消息（接口已废弃，仅记录状态）
     * @param $userId
     * @param $data
     * @return bool
     */
    private function sendTemplateMessage($userId, $data)
    {
        $this->recordState("用户[ID:$userId] 微信模板消息接口已废弃，未实际发送！");
        return false;
    }

    /**
     * 获取状态集
     * @return array
     */
    public function getStateSet()
    {
        return $this->stateSet;
    }

    /**
     * 记录状态集
     * @param $content
     */
    private function recordState($content)
    {
        $this->stateSet[] = $content;
    }

}
