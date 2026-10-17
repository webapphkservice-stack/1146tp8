<?php
namespace app\common\service\wechat;

use app\common\library\wechat\WxBase;
use app\common\model\Setting as SettingModel;
use app\common\model\Wxapp as WxappModel;
use app\common\exception\BaseException;

/**
 * 微信小程序虚拟支付（xpay）服务端封装
 * 文档：https://developers.weixin.qq.com/miniprogram/dev/platform-capabilities/business-capabilities/virtual-payment.html
 *
 * 适用场景：售卖虚拟商品（VIP 会员 / 代币）。下单不在服务端完成，
 * 由前端 wx.requestVirtualPayment 内部拉起；本类负责：
 *   1) 生成 wx.requestVirtualPayment 所需的签名参数（paySig / signature）
 *   2) 调用服务端接口（道具注册 / 查单 / 退款）
 *
 * TP8 适配说明（对比 TP5）：
 *   - TP8 的 WxBase 无构造函数且 getAccessToken($appId, $appSecret) 需要参数、
 *     也没有 post() 方法；因此本类自行保存 appId/appSecret，并私有实现 post()，
 *     getAccessToken() 转调父类（参数由本类持有）。
 *   - HTTP 请求复用 TP8 全局函数 curl($url, $params, $isPost, $header)。
 */
class VirtualPayment extends WxBase
{
    // 虚拟支付 AppKey（MP 后台「虚拟支付 -> 基本配置」获取，区别于 app_secret）
    private $appKey;

    // 报价 ID（offerId）
    private $offerId;

    // 环境：0 正式 / 1 沙箱
    private $env;

    // 小程序凭据（用于换取 access_token）
    private $appId;
    private $appSecret;

    /**
     * @param int|null $wxappId
     * @throws BaseException
     */
    public function __construct($wxappId = null)
    {
        $wxapp = WxappModel::getWxappCache($wxappId);
        if (empty($wxapp)) {
            throw new BaseException(['msg' => '未找到小程序配置']);
        }
        $this->appId     = $wxapp['app_id'] ?? '';
        $this->appSecret = $wxapp['app_secret'] ?? '';

        $vp            = SettingModel::getItem('virtual_payment');
        $this->appKey  = $vp['app_key'] ?? '';
        $this->offerId = $vp['offer_id'] ?? '';
        $this->env     = isset($vp['env']) ? (int)$vp['env'] : 0;
    }

    /**
     * 生成前端 wx.requestVirtualPayment 所需的全部参数
     * @param array $user 当前用户（需含 user_id）
     * @param array $plan 套餐（需含 plan_id / plan_name / price）
     * @param string $outTradeNo 商户订单号
     * @param string $platform android / ios
     * @param string $sessionKey 当前用户的 session_key
     * @param string $mode goods=道具直购 / currency=代币充值
     * @param string $goodsPrefix 道具id前缀：vip_ / coin_
     * @return array
     * @throws BaseException
     */
    public function buildClientParams($user, $plan, $outTradeNo, $platform, $sessionKey, $mode = 'goods', $goodsPrefix = 'vip_')
    {
        $signData = [
            'offerId'      => $this->offerId,
            'buyQuantity'  => 1,
            'env'          => $this->env,
            'currencyType' => 'CNY',
            'platform'     => $platform,
            'productId'    => $goodsPrefix . $plan['plan_id'],
            'goodsPrice'   => (int)round($plan['price'] * 100),
            'outTradeNo'   => $outTradeNo,
            'attach'       => (string)$user['user_id'],
        ];
        $signDataJson = json_encode($signData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (empty($this->appKey)) {
            throw new BaseException(['msg' => '未配置虚拟支付 AppKey（请到 [后台-小程序设置 / 虚拟支付配置] 填写）']);
        }
        if (empty($sessionKey)) {
            throw new BaseException(['msg' => '用户登录态已失效，请重新登录后再支付']);
        }

        // 支付签名：paySig = HMAC-SHA256(appKey, 'requestVirtualPayment&' + signData)
        $paySig = hash_hmac('sha256', 'requestVirtualPayment&' . $signDataJson, $this->appKey);
        // 用户态签名：signature = HMAC-SHA256(sessionKey, signData)
        $signature = hash_hmac('sha256', $signDataJson, $sessionKey);

        return [
            'mode'         => $mode,
            'productId'    => $signData['productId'],
            'goodsPrice'   => $signData['goodsPrice'],
            'goodsName'    => $plan['plan_name'],
            'buyQuantity'  => 1,
            'outTradeNo'   => $outTradeNo,
            'currencyType' => 'CNY',
            'platform'     => $platform,
            'env'          => $this->env,
            'attach'       => $signData['attach'],
            'nonceStr'     => $this->generateNonce(),
            'timeStamp'    => (string)time(),
            'signature'    => $signature,
            'paySig'       => $paySig,
            'signData'     => $signDataJson,
        ];
    }

    /** 上传道具（一个套餐 = 一个道具） */
    public function uploadGoods($plan)
    {
        $params = [
            'env'         => $this->env,
            'upload_item' => [[
                'id'       => 'vip_' . $plan['plan_id'],
                'name'     => $plan['plan_name'],
                'price'    => (int)round($plan['price'] * 100),
                'remark'   => 'VIP会员-' . $plan['plan_name'],
                'item_url' => '',
            ]],
        ];
        return $this->apiCall('/xpay/start_upload_goods', $params);
    }

    /** 发布道具 */
    public function publishGoods($planId)
    {
        return $this->apiCall('/xpay/start_publish_goods', [
            'env'          => $this->env,
            'publish_item' => [['id' => 'vip_' . $planId]],
        ]);
    }

    /** 查询道具上传任务 */
    public function queryUploadGoods($planId)
    {
        return $this->apiCall('/xpay/query_upload_goods', [
            'env'         => $this->env,
            'upload_item' => [['id' => 'vip_' . $planId]],
        ]);
    }

    /** 查询道具发布任务 */
    public function queryPublishGoods($planId)
    {
        return $this->apiCall('/xpay/query_publish_goods', [
            'env'          => $this->env,
            'publish_item' => [['id' => 'vip_' . $planId]],
        ]);
    }

    /** 上传代币充值道具（productId = coin_{plan_id}） */
    public function uploadCoinGoods($plan)
    {
        $params = [
            'env'         => $this->env,
            'upload_item' => [[
                'id'       => 'coin_' . $plan['plan_id'],
                'name'     => $plan['plan_name'] ?? ('代币' . $plan['money']),
                'price'    => (int)round($plan['money'] * 100),
                'remark'   => '代币充值-' . ($plan['plan_name'] ?? $plan['money']),
                'item_url' => '',
            ]],
        ];
        return $this->apiCall('/xpay/start_upload_goods', $params);
    }

    /** 发布代币充值道具 */
    public function publishCoinGoods($planId)
    {
        return $this->apiCall('/xpay/start_publish_goods', [
            'env'          => $this->env,
            'publish_item' => [['id' => 'coin_' . $planId]],
        ]);
    }

    /** 查询代币充值道具上传任务 */
    public function queryUploadCoinGoods($planId)
    {
        return $this->apiCall('/xpay/query_upload_goods', [
            'env'         => $this->env,
            'upload_item' => [['id' => 'coin_' . $planId]],
        ]);
    }

    /**
     * 代币扣减（服务端调用）
     * @throws BaseException
     */
    public function currencyPay($openid, $orderNo, $amountFen, $sessionKey)
    {
        if (empty($sessionKey)) {
            throw new BaseException(['msg' => '用户登录态已失效，请重新登录后再支付']);
        }
        return $this->apiCall('/xpay/currency_pay', [
            'openid'   => $openid,
            'env'      => $this->env,
            'order_id' => $orderNo,
            'fee'      => (int)$amountFen,
            'remark'   => '代币支付',
        ], $sessionKey);
    }

    /** 查询订单（现金单） */
    public function queryOrder($openid, $orderId)
    {
        return $this->apiCall('/xpay/query_order', [
            'openid'   => $openid,
            'env'      => $this->env,
            'order_id' => $orderId,
        ]);
    }

    /** 发起退款（现金单） */
    public function refundOrder($openid, $refundOrderId, $orderId, $wxOrderId, $leftFee, $refundFee, $refundReason = 0, $reqFrom = 1)
    {
        return $this->apiCall('/xpay/refund_order', [
            'openid'          => $openid,
            'env'             => $this->env,
            'order_id'        => $orderId,
            'wx_order_id'     => $wxOrderId,
            'refund_order_id' => $refundOrderId,
            'left_fee'        => (int)$leftFee,
            'refund_fee'      => (int)$refundFee,
            'refund_reason'   => (int)$refundReason,
            'req_from'        => (int)$reqFrom,
        ]);
    }

    /**
     * 调用 xpay 服务端接口
     * @param string $uri
     * @param array $params
     * @param string|null $sessionKey 需要用户态签名时传入
     * @return array|false
     * @throws BaseException
     */
    private function apiCall($uri, $params, $sessionKey = null)
    {
        $bodyStr   = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $token     = parent::getAccessToken($this->appId, $this->appSecret);
        if (empty($token)) {
            $this->error = '获取 access_token 失败';
            return false;
        }
        $url = 'https://api.weixin.qq.com' . $uri . '?access_token=' . $token;

        $send             = $params;
        $send['pay_sig'] = hash_hmac('sha256', $uri . '&' . $bodyStr, $this->appKey);
        if ($sessionKey !== null) {
            $send['signature'] = hash_hmac('sha256', $bodyStr, $sessionKey);
        }
        $sendStr = json_encode($send, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $result = $this->post($url, $sendStr);
        $resp   = json_decode($result, true);
        if (!is_array($resp)) {
            $this->error = '虚拟支付接口返回异常：' . $result;
            return false;
        }
        if (!empty($resp['errcode'])) {
            $this->error = '虚拟支付接口错误：' . ($resp['errmsg'] ?? ('errcode ' . $resp['errcode']));
            return $resp;
        }
        return $resp;
    }

    /** POST JSON（TP8 的 WxBase 未提供 post，这里用全局 curl 实现） */
    private function post($url, $bodyStr)
    {
        return curl($url, $bodyStr, true, ['Content-Type: application/json']);
    }

    /** 生成随机字符串 */
    private function generateNonce()
    {
        return md5(uniqid(microtime(true), true));
    }
}
