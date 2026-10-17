<?php
namespace app\common\service\message\order;

use think\facade\Db;
use app\common\model\Setting as SettingModel;
use app\common\model\Wxapp as WxappModel;
use app\common\library\wechat\WxSubMsg;

/**
 * 消息通知 [订单售后结果]
 *
 * 移植自 php common/service/message/order/Refund.php。
 * 模板读取：setting 表 submsg.order.refund（后台「小程序设置-订阅消息」配置，
 * 视图 store/view/wxapp/submsg/index.php 已有该字段）。
 *
 * 触发点：store/model/OrderRefund::receipt()（商家确认收货并退款）
 */
class Refund
{
    /** @var array */
    private $param = [];

    /** @var array 小程序页面路径 */
    private $pageUrl = 'pages/order/refund/index';

    /**
     * 发送通知
     * @param array $param order / refund / money
     * @return bool
     */
    public function send(array $param = [])
    {
        $this->param = $param;
        return $this->onSendWxSubMsg();
    }

    /**
     * 微信订阅消息通知用户
     * @return bool
     */
    private function onSendWxSubMsg()
    {
        $order  = $this->param['order'] ?? [];
        $refund = $this->param['refund'] ?? [];
        if (empty($order)) {
            return false;
        }
        $wxappId = (int)($order['wxapp_id'] ?? 0);

        // 未配置模板 = 未开启通知，静默跳过
        $submsg   = SettingModel::getItem('submsg');
        $template = $submsg['order']['refund'] ?? [];
        if (empty($template['template_id'])) {
            return false;
        }

        // 用户 openid
        $openId = Db::name('user')->where('user_id', $order['user_id'])->value('open_id');
        if (empty($openId)) {
            return false;
        }
        $wxapp = WxappModel::getWxappCache($wxappId);
        if (empty($wxapp['app_id'])) {
            return false;
        }

        $keywords = $template['keywords'] ?? [];
        $data     = [];
        $values   = [
            $this->typeText($refund['type'] ?? 10),                       // 售后类型
            $this->statusText($refund['status'] ?? 20),                   // 处理状态
            $order['order_no'] ?? '',                                     // 订单编号
            date('Y-m-d H:i', $refund['create_time'] ?: time()),          // 申请时间
            $refund['apply_desc'] ?? '',                                  // 申请原因
        ];
        foreach ($values as $index => $value) {
            if (!isset($keywords[$index])) {
                continue;
            }
            $data[$keywords[$index]] = ['value' => $this->getSubstr($value)];
        }
        if (empty($data)) {
            return false;
        }

        $wxSubMsg = new WxSubMsg($wxapp['app_id'], $wxapp['app_secret']);
        return $wxSubMsg->sendTemplateMessage([
            'touser'      => $openId,
            'template_id' => $template['template_id'],
            'page'        => $this->pageUrl,
            'data'        => $data,
        ]);
    }

    private function typeText($type)
    {
        return ((int)$type === 20) ? '换货' : '退货退款';
    }

    private function statusText($status)
    {
        $map = [0 => '进行中', 10 => '已拒绝', 20 => '已完成', 30 => '已取消'];
        return $map[(int)$status] ?? '处理中';
    }

    /** 字符串截取前20字符（兼容 thing 数据类型） */
    private function getSubstr($content, $length = 20)
    {
        $content = (string)$content;
        return mb_substr($content, 0, $length, 'utf-8');
    }
}
