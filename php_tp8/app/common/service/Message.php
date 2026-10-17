<?php
namespace app\common\service;

/**
 * 消息通知分发器
 *
 * 对应 php common/service/Message.php（TP5 里由 service/Basics 提供基类，
 * TP8 未迁移该基类，这里做成静态分发器，保持调用方式一致：
 *   \app\common\service\Message::send('order.refund', [...]);
 *
 * 设计取舍：
 *   - 场景类缺失 / 抛异常一律记录日志并返回 false，不阻断调用方的主流程
 *     （例如退款已成功，不能因为通知失败而回滚）。
 *   - 模板未配置（template_id 为空）时静默跳过，属于正常的「未开启通知」。
 */
class Message
{
    /**
     * 场景 -> 处理类
     * @var array
     */
    private static $sceneList = [
        // 订单售后结果
        'order.refund' => \app\common\service\message\order\Refund::class,
    ];

    /**
     * 发送场景消息
     * @param string $scene 场景名，如 order.refund
     * @param array  $param 场景参数
     * @return bool
     */
    public static function send($scene, array $param = [])
    {
        if (!isset(self::$sceneList[$scene])) {
            log_write('消息通知：未注册的场景 ' . $scene);
            return false;
        }
        $class = self::$sceneList[$scene];
        if (!class_exists($class)) {
            log_write('消息通知：场景类不存在 ' . $class);
            return false;
        }
        try {
            $service = new $class;
            return (bool)$service->send($param);
        } catch (\Throwable $e) {
            log_write('消息通知发送失败[' . $scene . ']：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 注册新场景（供后续模块扩展）
     * @param string $scene
     * @param string $class
     */
    public static function register($scene, $class)
    {
        self::$sceneList[$scene] = $class;
    }
}
