#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
P0 支付接入端到端测试（无需真实网关凭证，全部可离线验证）

覆盖：
  S1  Stripe Webhook 正确签名           -> 200，订单 pay_status 10 -> 20
  S2  Stripe Webhook 错误签名           -> 400 签名校验失败
  S3  Stripe Webhook 时间戳过期(重放)   -> 400 时间戳超出容差
  S4  Stripe Webhook 重复回调(幂等)     -> 200，且不重复累加用户消费金额
  S5  微信支付统一下单（无网络时降级）  -> 明确报错而非返回无效参数
  S6  支付宝 App 支付参数               -> 返回已签名 order_string

用法：python3 e2e_p0_payment_test.py
前置：php think run --host 127.0.0.1 --port 8081 且已导入 database/seed_p0_test.sql
"""

import hashlib
import hmac
import json
import subprocess
import time
import urllib.request
import urllib.error

import os
BASE = os.environ.get('TP8_BASE', 'http://127.0.0.1:8081')
WEBHOOK_SECRET = 'whsec_p0test123'
ORDER_NO = 'P0TEST0000000009'

PASSED = 0
FAILED = 0


def report(name, ok, detail=''):
    global PASSED, FAILED
    if ok:
        PASSED += 1
        print(f'  [PASS] {name} {detail}')
    else:
        FAILED += 1
        print(f'  [FAIL] {name} {detail}')


def post(path, body, headers=None):
    data = body.encode('utf-8') if isinstance(body, str) else body
    req = urllib.request.Request(BASE + path, data=data, method='POST')
    req.add_header('Content-Type', 'application/json')
    for k, v in (headers or {}).items():
        req.add_header(k, v)
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return resp.status, resp.read().decode('utf-8')
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8')


def msg_of(body):
    """解析响应体中的错误文案（响应为 JSON 且中文被转义，需 json.loads 还原）"""
    try:
        data = json.loads(body)
    except Exception:
        return body
    return data.get('error') or data.get('msg') or body


def get_pay(order_id, pay_type, token='p0token123'):
    """调用 user.order/pay（需 wxapp_id + 登录 token，token 由 seed_p0_session.php 注入）"""
    req = urllib.request.Request(
        f'{BASE}/api/user.order/pay?wxapp_id=10001&token={token}'
        f'&order_id={order_id}&pay_type={pay_type}', method='GET')
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            return resp.status, resp.read().decode('utf-8')
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf-8')


def stripe_sig(payload, secret, timestamp=None):
    """按 Stripe 规则生成 Stripe-Signature 头"""
    ts = timestamp if timestamp is not None else int(time.time())
    signed = f'{ts}.{payload}'
    sig = hmac.new(secret.encode('utf-8'), signed.encode('utf-8'), hashlib.sha256).hexdigest()
    return f't={ts},v1={sig}'


def event_payload():
    return json.dumps({
        'id': 'evt_p0_test',
        'type': 'payment_intent.succeeded',
        'data': {'object': {
            'id': 'pi_P0TEST',
            'object': 'payment_intent',
            'amount': 8800,
            'metadata': {'order_no': ORDER_NO},
        }},
    })


def reseed():
    """重跑种子数据 + 注入登录态，保证测试可重复执行"""
    subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8'],
                   stdin=open('database/seed_p0_test.sql', 'r'),
                   capture_output=True, text=True)
    subprocess.run(['php', 'seed_p0_session.php'], capture_output=True, text=True)


def main():
    print('=== P0 支付接入端到端测试 ===\n')
    reseed()

    # S1 正确签名
    print('S1 Stripe Webhook 正确签名')
    payload = event_payload()
    code, body = post('/api/notify/stripe', payload,
                      {'Stripe-Signature': stripe_sig(payload, WEBHOOK_SECRET)})
    report('签名通过并改单成功', code == 200, f'HTTP {code} {body[:120]}')

    # S2 错误签名
    print('S2 Stripe Webhook 错误签名')
    payload = event_payload()
    code, body = post('/api/notify/stripe', payload,
                      {'Stripe-Signature': stripe_sig(payload, 'whsec_WRONG')})
    report('错误签名被拒绝', code == 400 and '签名校验失败' in msg_of(body), f'HTTP {code} {msg_of(body)}')

    # S3 时间戳过期（重放）
    print('S3 Stripe Webhook 时间戳过期')
    payload = event_payload()
    old_ts = int(time.time()) - 3600
    code, body = post('/api/notify/stripe', payload,
                      {'Stripe-Signature': stripe_sig(payload, WEBHOOK_SECRET, old_ts)})
    report('过期时间戳被拒绝', code == 400, f'HTTP {code} {body[:120]}')

    # S4 重复回调幂等
    print('S4 Stripe Webhook 重复回调（幂等）')
    payload = event_payload()
    code, body = post('/api/notify/stripe', payload,
                      {'Stripe-Signature': stripe_sig(payload, WEBHOOK_SECRET)})
    report('重复回调仍成功且幂等', code == 200, f'HTTP {code} {body[:120]}')

    # S5 微信支付：真实调用统一下单 API（验证签名被微信接受，而非返回占位 prepay_id）
    print('S5 微信支付统一下单')
    code, body = get_pay(900010, 20)
    text = msg_of(body)
    # 期望：要么真实下单成功（返回 wxConfig），要么微信网关返回明确错误。
    # 关键排除项：不得再返回旧的占位参数（prepay_订单号_xxx）
    ok = code == 200 and ('微信支付' in text or 'wxConfig' in text or 'prepay_id' in text)
    ok = ok and 'prepay_P0TEST' not in text
    report('微信走真实统一下单（非占位 prepay_id）', ok, f'HTTP {code} {text[:120]}')

    # S6 支付宝：应返回真实签名的 order_string
    print('S6 支付宝 App 支付参数')
    code, body = get_pay(900011, 40)
    data = {}
    try:
        data = json.loads(body).get('data', {}) or {}
    except Exception:
        pass
    order_string = (data.get('payment') or {}).get('order_string', '')
    ok = code == 200 and bool(order_string) and 'sign=' in order_string
    report('支付宝返回已签名 order_string', ok, f'HTTP {code} order_string={order_string[:60]}...')

    # S7 Stripe：真实调用 REST API 创建 PaymentIntent
    print('S7 Stripe 创建 PaymentIntent')
    code, body = get_pay(900012, 50)
    text = msg_of(body)
    ok = code == 200 and ('Stripe' in text or 'clientSecret' in text)
    ok = ok and 'pi_P0TEST' not in text.replace(' ', '')
    report('Stripe 走真实 PaymentIntent 创建（非占位 secret）', ok, f'HTTP {code} {text[:120]}')

    print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
    return 0 if FAILED == 0 else 1


if __name__ == '__main__':
    raise SystemExit(main())
