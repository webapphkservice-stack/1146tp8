<?php
namespace app\api\controller;

use app\common\controller\Controller;
use app\api\service\OrderStateMachine;
use app\api\service\PayNotifyVerifier;
use app\api\service\VirtualPayDeliver;
use app\common\enum\order\PayType;
use app\common\model\Setting as SettingModel;
use think\facade\Db;
use think\facade\Log;

/**
 * 微信支付异步通知
 * 路由：POST /api/notify/order（无需登录态，网关直连，置于 Token 中间件分组之外）
 *
 * 同时支持两代协议：
 *  - v3（官方 SDK wechatpay/wechatpay）：JSON 回调，业务数据 AES-256-GCM 加密，RSA-SHA256 验签
 *  - v2（旧版兼容）：XML 回调，明文 + MD5 签名
 *
 * 【v3 实现要点】out_trade_no 位于加密报文 resource 内部，解密前无法得知订单所属 wxapp，
 * 而解密密钥（APIv3 密钥）按 wxapp 分散配置。因此这里枚举候选 wxapp 逐个「验签 + 解密」，
 * 首个成功者即为准；wxapp 数量通常只有 1~3 个，成本可忽略。
 */
class Notify extends Controller
{
    // 回调为网关直连，无 wxapp_id 上下文，跳过基类 wxapp_id 校验
    public function initialize()
    {
        $this->request = request();
    }

    public function order()
    {
        $body = trim((string)$this->request->getInput());
        if ($body === '') {
            return $this->wxReturn(false, '未收到通知数据');
        }
        // v3 为 JSON，v2 为 XML
        return strncmp($body, '{', 1) === 0 ? $this->handleV3($body) : $this->handleV2($body);
    }

    // -------------------- v3（官方 SDK） --------------------

    /**
     * 处理 v3 回调
     * @param string $body 原始请求体（必须原样传给验签，不可重新编码）
     * @return \think\Response
     */
    private function handleV3($body)
    {
        $headers = $this->requestHeaders();

        $data = null;
        foreach ($this->candidateWxappIds() as $wxappId) {
            $decrypted = PayNotifyVerifier::wechatV3($headers, $body, $wxappId);
            if (is_array($decrypted)) {
                $data = $decrypted;
                break;
            }
        }
        if ($data === null) {
            Log::write('微信v3回调验签或解密失败：' . $body, 'error');
            return $this->wxV3Return(false, '签名校验失败');
        }

        // 好友代付提交的是临时单号，这里还原成真实订单号
        $orderNo = $this->resolveOrderNo($data['out_trade_no'] ?? '', $data['attach'] ?? '');
        if ($orderNo === '') {
            return $this->wxV3Return(false, '缺少商户订单号');
        }
        // 非支付成功态（如已退款/已关闭）直接回成功，避免网关无限重试
        if (($data['trade_state'] ?? '') !== 'SUCCESS') {
            Log::write('微信v3回调非成功态，忽略：' . ($data['trade_state'] ?? '') . ' ' . $orderNo, 'info');
            return $this->wxV3Return(true);
        }

        [$ok, $msg] = $this->dispatch($orderNo, $data['transaction_id'] ?? '');
        return $this->wxV3Return($ok, $msg);
    }

    /**
     * v3 要求的 JSON 回执：成功 2xx / 失败 5xx
     * @param bool   $success
     * @param string $message
     * @return \think\Response
     */
    private function wxV3Return($success, $message = '成功')
    {
        // 注意：TP8 的 response() 助手签名为 (data, code, header, type)，第 4 个参数才是输出类型
        return response([
            'code'    => $success ? 'SUCCESS' : 'FAIL',
            'message' => $success ? '成功' : $message,
        ], $success ? 200 : 500, [], 'json');
    }

    // -------------------- v2（旧版兼容） --------------------

    /**
     * 处理 v2 XML 回调
     * @param string $body
     * @return \think\Response
     */
    private function handleV2($body)
    {
        $data = $this->fromXml($body);
        if (empty($data)) {
            return $this->wxReturn(false, 'XML 解析失败');
        }
        if (($data['return_code'] ?? '') !== 'SUCCESS'
            || ($data['result_code'] ?? '') !== 'SUCCESS') {
            return $this->wxReturn(false, '支付未成功');
        }
        // 好友代付提交的是临时单号，这里先还原真实订单号，
        // 否则 resolveWxappId() 按临时号查不到订单，会返回 0 导致验签失败
        $orderNo = $this->resolveOrderNo($data['out_trade_no'] ?? '', $data['attach'] ?? '');
        if ($orderNo === '') {
            return $this->wxReturn(false, '缺少商户订单号');
        }
        $wxappId = PayNotifyVerifier::resolveWxappId($orderNo);
        if (!PayNotifyVerifier::wxPay($data, $wxappId)) {
            return $this->wxReturn(false, '签名校验失败');
        }

        [$ok, $msg] = $this->dispatch($orderNo, $data['transaction_id'] ?? '');
        return $this->wxReturn($ok, $msg);
    }

    /**
     * 微信 XML 转数组（禁用外部实体，防 XXE）
     * @param string $xml
     * @return array
     */
    private function fromXml($xml)
    {
        $obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        if ($obj === false) {
            return [];
        }
        return json_decode(json_encode($obj), true) ?: [];
    }

    /**
     * 返回微信 v2 要求的 XML 回执
     * @param bool   $success
     * @param string $msg
     * @return \think\Response
     */
    private function wxReturn($success, $msg = 'OK')
    {
        $xml = '<xml>'
            . '<return_code><![CDATA[' . ($success ? 'SUCCESS' : 'FAIL') . ']]></return_code>'
            . '<return_msg><![CDATA[' . $msg . ']]></return_msg>'
            . '</xml>';
        return response($xml)->contentType('text/xml; charset=utf-8');
    }

    // -------------------- 公共分发 --------------------

    /**
     * 按订单号分发到充值 / VIP / 商城三套支付成功收口
     * @param string $orderNo
     * @param string $transactionId 微信支付订单号
     * @return array [是否成功, 失败原因]
     */
    private function dispatch($orderNo, $transactionId)
    {
        // 充值订单（RC 前缀）：余额到账 + 流水
        if (strpos($orderNo, 'RC') === 0) {
            $rp = new \app\api\service\RechargePaySuccess();
            if (!$rp->paySuccess($orderNo, $transactionId)) {
                return [false, $rp->getError() ?: '充值处理失败'];
            }
            return [true, ''];
        }
        // VIP 开通订单：开通会员 + 赠送运费券
        if (Db::name('vip_order')->where('order_no', $orderNo)->count()) {
            $vp = new \app\api\service\VipPaySuccess();
            if (!$vp->paySuccess($orderNo, $transactionId)) {
                return [false, $vp->getError() ?: 'VIP处理失败'];
            }
            return [true, ''];
        }
        $sm = new OrderStateMachine;
        if (!$sm->paySuccess($orderNo, PayType::WECHAT, $transactionId)) {
            return [false, $sm->getError() ?: '处理失败'];
        }
        return [true, ''];
    }

    /**
     * 还原真实订单号（好友代付兜底）
     *
     * 代付下单时提交给微信的是「临时单号」= 真实单号 + 'F' + 随机，
     * 直接用它查订单会查不到。按三级兜底还原：
     *   1. out_trade_no 本身就是真实订单号（普通支付）
     *   2. 剥离代付后缀（真实单号 + 'F' + 数字）
     *   3. 回调报文里的 attach（下单时写入的就是真实订单号）
     *
     * @param string $orderNo 回调中的 out_trade_no
     * @param string $attach  回调中的 attach
     * @return string 真实订单号（都命中不了时原样返回，由后续流程报「订单不存在」）
     */
    private function resolveOrderNo($orderNo, $attach = '')
    {
        if ($orderNo === '') {
            return '';
        }
        // 1) 普通支付：直接命中
        if ($this->orderExists($orderNo)) {
            return $orderNo;
        }
        // 2) 剥离代付临时后缀
        if (preg_match('/^(.+)F\d+$/', $orderNo, $m) && $this->orderExists($m[1])) {
            return $m[1];
        }
        // 3) 用 attach（下单时写入的真实订单号）
        if ($attach !== '' && $this->orderExists($attach)) {
            return $attach;
        }
        return $orderNo;
    }

    /**
     * 订单号是否存在于订单表
     * @param string $orderNo
     * @return bool
     */
    private function orderExists($orderNo)
    {
        if ($orderNo === '') {
            return false;
        }
        return (bool)Db::name('order')->where('order_no', $orderNo)->count();
    }

    /**
     * 候选小程序列表：v3 需先解密才知道订单归属，故需逐个尝试
     * @return array
     */
    private function candidateWxappIds()
    {
        return Db::name('wxapp')->where('is_recycle', 0)->column('wxapp_id');
    }

    /**
     * 收集请求头（v3 验签依赖 Wechatpay-Serial/Signature/Timestamp/Nonce）
     * @return array
     */
    private function requestHeaders()
    {
        $headers = [];
        foreach ($this->request->header() as $key => $value) {
            $headers[$key] = $value;
        }
        // 部分环境仅 $_SERVER 中存在 HTTP_WECHATPAY_*，补齐以兼容
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'HTTP_WECHATPAY_') === 0) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }

    // -------------------- 微信小程序虚拟支付（xpay）消息推送 --------------------

    /**
     * 微信虚拟支付消息推送入口
     * 路由：/api/notify/virtual
     *
     * GET  ：MP 后台配置回调 URL 时的一键验证（返回 echostr 明文）
     * POST ：xpay_goods_deliver_notify 等事件推送 → 幂等发货/充值
     */
    public function virtual()
    {
        if ($this->request->isGet()) {
            $this->virtualVerifyEcho();
        }
        return $this->virtualHandlePush();
    }

    /**
     * 接入验证（GET）
     * 微信发送 signature=sha1(字典序[token,timestamp,nonce]) & timestamp & nonce & echostr
     * 校验通过需原样返回 echostr 明文（不能返回 JSON/HTML）
     *
     * 注意：本回调无 wxapp_id 上下文（initialize 跳过了基类设置），
     * 不能用 SettingModel::getItem，需枚举所有候选 wxapp 取 token。
     */
    private function virtualVerifyEcho()
    {
        $token = $this->virtualResolveToken();
        if ($token === '') {
            echo 'fail'; exit;
        }
        $signature = $this->request->get('signature', '');
        $timestamp = $this->request->get('timestamp', '');
        $nonce     = $this->request->get('nonce', '');
        $echostr   = $this->request->get('echostr', '');

        $tmp  = [$token, $timestamp, $nonce];
        sort($tmp, SORT_STRING);
        $calc = sha1(implode($tmp));

        echo $calc === $signature ? $echostr : 'fail';
        exit;
    }

    /**
     * 处理虚拟支付消息推送（POST）
     */
    private function virtualHandlePush()
    {
        // 1. 签名校验（枚举所有 wxapp 候选 token，哪个通过用哪个）
        $tokens = $this->virtualResolveAllTokens();
        if (empty($tokens)) {
            Log::write('[virtual] 所有 wxapp 均未配置 token，拒绝推送', 'warning');
            return $this->virtualReply(false, 'missing token');
        }
        $signature = $this->request->get('signature', '');
        $timestamp = $this->request->get('timestamp', '');
        $nonce     = $this->request->get('nonce', '');
        if ($signature === '') {
            $signature = $this->request->param('signature', '');
            $timestamp = $this->request->param('timestamp', '');
            $nonce     = $this->request->param('nonce', '');
        }
        // 尝试所有候选 token，任一通过即视为合法
        $matchedToken = '';
        foreach ($tokens as $t) {
            $tmp  = [$t, $timestamp, $nonce];
            sort($tmp, SORT_STRING);
            $calc = sha1(implode($tmp));
            if ($calc === $signature) {
                $matchedToken = $t;
                break;
            }
        }
        if ($matchedToken === '') {
            Log::write('[virtual] 签名校验失败 sig=' . $signature, 'warning');
            return $this->virtualReply(false, 'invalid signature');
        }

        // 2. 解析请求体
        $raw  = trim((string)$this->request->getInput());
        if ($raw === '') {
            return $this->virtualReply(false, 'empty body');
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $this->virtualReply(false, 'invalid json');
        }

        // 3. 安全模式解密（body.Encrypt 为 AES-256-CBC 密文，用签名校验通过的 token 解密）
        if (!empty($data['Encrypt'])) {
            $decrypted = $this->virtualDecryptMessage($data['Encrypt'], $matchedToken);
            if ($decrypted === null) {
                return $this->virtualReply(false, 'decrypt failed');
            }
            $data = $decrypted;
        }

        Log::write('[virtual] 推送数据：' . json_encode($data, JSON_UNESCAPED_UNICODE), 'info');

        // 4. 2.0 协议：MiniGame.Payload 是 JSON 字符串，二次解码
        $payload = $data;
        if (isset($data['MiniGame']) && is_string($data['MiniGame']['Payload'] ?? null)) {
            $decoded = json_decode($data['MiniGame']['Payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        // 5. 事件白名单（兼容游戏/商城/旧版三类命名）
        $event = strtolower($data['Event'] ?? '');
        $deliverEvents = [
            'minigame_game_pay_goods_deliver_notify',
            'minigame_h5_goods_deliver_notify',
            'minigame_deliver_h5_pay_products',
            'xpay_goods_deliver_notify',
        ];
        if (!in_array($event, $deliverEvents, true)) {
            Log::write('[virtual] 非发货事件，忽略：' . $event, 'info');
            return $this->virtualReply(true);
        }

        // 6. 模拟推送 / 沙箱环境跳过
        $isMock = $payload['IsMock'] ?? $data['IsMock'] ?? false;
        if ($isMock === true || $isMock === 'true' || (int)$isMock === 1) {
            Log::write('[virtual] 模拟推送，跳过发货：' . json_encode($payload, JSON_UNESCAPED_UNICODE), 'info');
            return $this->virtualReply(true);
        }
        if ((int)($payload['Env'] ?? 0) === 1) {
            Log::write('[virtual] 沙箱环境，跳过发货', 'info');
            return $this->virtualReply(true);
        }

        // 7. 提取关键字段
        $orderNo       = $payload['OutTradeNo'] ?? $data['OutTradeNo'] ?? '';
        $wechatPayInfo = $payload['WeChatPayInfo'] ?? $data['WeChatPayInfo'] ?? [];
        $transactionId = $wechatPayInfo['MchOrderNo'] ?? $wechatPayInfo['TransactionId'] ?? '';
        $goodsInfo     = $payload['GoodsInfo'] ?? $data['GoodsInfo'] ?? [];
        $paidAmount    = (int)($goodsInfo['ActualPrice'] ?? $goodsInfo['OrigPrice'] ?? 0);

        if ($orderNo === '') {
            return $this->virtualReply(false, 'missing out_trade_no');
        }

        // 8. 统一分发到 VirtualPayDeliver（回调和主动查询共用同一套发货逻辑）
        [$ok, $msg] = (new VirtualPayDeliver)->dispatch($orderNo, (string)$transactionId, $paidAmount);
        return $this->virtualReply($ok, $msg);
    }

    /**
     * 安全模式解密：AES-256-CBC，key = md5(token, true)（32 字节），iv 为前 16 字节
     */
    private function virtualDecryptMessage($encrypt, $token)
    {
        $key = md5($token, true);
        $ciphertext = base64_decode($encrypt);
        if ($ciphertext === false || strlen($ciphertext) < 16) {
            return null;
        }
        $iv         = substr($ciphertext, 0, 16);
        $rest       = substr($ciphertext, 16);
        $plaintext  = openssl_decrypt($rest, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plaintext === false) {
            return null;
        }
        $json = json_decode($plaintext, true);
        return is_array($json) ? $json : null;
    }

    /**
     * 虚拟支付回执（HTTP 200 即视为成功，微信不会再重试）
     */
    private function virtualReply($success, $msg = 'ok')
    {
        $body = json_encode(['errcode' => $success ? 0 : -1, 'errmsg' => $success ? 'ok' : $msg]);
        return response($body, $success ? 200 : 400, [], 'json');
    }

    /**
     * 枚举所有 wxapp，取第一个有 token 的 virtual_payment 配置（供 GET 接入验证用）
     *
     * 本回调无 wxapp_id 上下文（initialize 跳过了基类设置），
     * SettingModel::getItem 会因 self::getWxappId() === 0 查不到记录。
     * 直接枚举所有候选 wxapp 即可，数量通常只有 1~3 个。
     */
    private function virtualResolveToken()
    {
        foreach ($this->virtualResolveAllTokens() as $t) {
            return $t;
        }
        return '';
    }

    /**
     * 枚举所有 wxapp，收集所有非空 token（供 POST 推送签名校验用）
     * 微信只会用某个特定小程序的 token 签名，我们尝试所有候选即可。
     *
     * @return string[] 非空 token 数组
     */
    private function virtualResolveAllTokens()
    {
        $rows = Db::name('setting')
            ->where('key', 'virtual_payment')
            ->field('values')
            ->select();
        $tokens = [];
        foreach ($rows as $row) {
            $cfg = json_decode($row['values'] ?? '', true);
            $t   = $cfg['token'] ?? '';
            if ($t !== '' && !in_array($t, $tokens, true)) {
                $tokens[] = $t;
            }
        }
        return $tokens;
    }
}
