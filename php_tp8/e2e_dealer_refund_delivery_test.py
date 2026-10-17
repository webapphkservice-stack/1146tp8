#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
第 9~12 批端到端测试：分销 / 退款+好友代付 / 配送 / 第三方 SDK 验签
覆盖目标：php_tp8 新接口返回结构与 php_my 契约一致，且微信/支付宝/Stripe 验签收口生效。
"""
import subprocess, json, sys, hashlib
import requests

BASE = "http://127.0.0.1:8081/api"
WXAPP = 10001
TOKEN = "testtoken123"
USER = 10022
APIKEY = "jANkJPm6L3Hlew9ePiH0hSS1KeezxHXL"  # wxapp 10001 微信支付密钥

def db(sql):
    r = subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-N", "-e", sql],
                       capture_output=True, text=True)
    return r.stdout.strip()

def setup():
    db(f"UPDATE yoshop_user SET balance=200 WHERE user_id={USER}")
    db(f"DELETE FROM yoshop_dealer_apply WHERE user_id={USER}")
    db(f"DELETE FROM yoshop_user_balance_log WHERE user_id={USER} AND scene=10")
    # 退款夹具：先清理再创建最小订单 + 订单商品（供 R2/R3/R4 正向验证）
    db(f"DELETE FROM yoshop_order_refund WHERE user_id={USER}")
    db(f"DELETE og FROM yoshop_order_goods og JOIN yoshop_order o ON o.order_id=og.order_id WHERE o.order_no='TESTREFUND{USER}'")
    db(f"DELETE FROM yoshop_order WHERE order_no='TESTREFUND{USER}'")
    db(f"INSERT INTO yoshop_order (order_no,user_id,wxapp_id,total_price,order_price,pay_price,pay_status,order_status,receipt_status,create_time,update_time,pay_time) VALUES ('TESTREFUND{USER}',{USER},10001,10.00,10.00,10.00,20,10,20,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),UNIX_TIMESTAMP())")
    oid = db(f"SELECT order_id FROM yoshop_order WHERE order_no='TESTREFUND{USER}'")
    db(f"INSERT INTO yoshop_order_goods (goods_id,goods_name,image_id,content,total_num,total_price,total_pay_price,order_id,user_id,wxapp_id,create_time) VALUES (1,'退款测试商品',10001,'',1,10.00,10.00,{oid},{USER},10001,UNIX_TIMESTAMP())")

def check(name, cond, extra=""):
    print(("PASS " if cond else "FAIL ") + name + (("  -> " + extra) if extra else ""))
    if not cond:
        global FAILED
        FAILED += 1

def wx_sign(params, apikey):
    tmp = {k: v for k, v in params.items() if k != "sign" and v != "" and not isinstance(v, (list, dict))}
    s = "&".join(f"{k}={tmp[k]}" for k in sorted(tmp.keys()))
    s += "&key=" + apikey
    return hashlib.md5(s.encode("utf-8")).hexdigest().upper()

FAILED = 0
setup()
s = requests.Session()
def g(path, **kw):
    kw.setdefault("params", {})
    kw["params"].update({"wxapp_id": WXAPP, "token": TOKEN})
    return s.get(f"{BASE}/{path}", **kw)
def p(path, **kw):
    kw.setdefault("data", {})
    kw["data"].update({"wxapp_id": WXAPP, "token": TOKEN})
    return s.post(f"{BASE}/{path}", **kw)

# ============ 第 9 批 分销 ============
r = g("user.dealer/center"); j = r.json()
check("D1 center code=1", j.get("code") == 1, str(j)[:120])
check("D1 center 含 setting/words", "setting" in j.get("data", {}) and "words" in j.get("data", {}))

r = g("user.dealer/apply"); j = r.json()
check("D2 apply code=1", j.get("code") == 1, str(j)[:120])
check("D2 apply 含 is_applying", "is_applying" in j.get("data", {}))

r = g("user.dealer/withdraw"); j = r.json()
check("D3 withdraw code=1", j.get("code") == 1)
check("D3 withdraw 含 settlement", "setting" in j.get("data", {}))

r = g("user.dealer/team/lists"); j = r.json()
check("D4 team/lists code=1", j.get("code") == 1, str(j)[:120])
check("D4 team/lists 含 list", "list" in j.get("data", {}))

r = g("user.dealer/order/lists"); j = r.json()
check("D5 order/lists code=1", j.get("code") == 1)

r = g("user.dealer/qrcode/poster"); j = r.json()
check("D6 qrcode/poster code=1", j.get("code") == 1, str(j)[:120])
check("D6 qrcode 返回分享链接", "qrcode" in j.get("data", {}))

# 提交申请
r = p("user.dealer/apply/submit", data={"name": "测试", "mobile": "13800000000"}); j = r.json()
check("D7 apply/submit code=1", j.get("code") == 1, str(j)[:120])
r = g("user.dealer/apply"); j = r.json()
check("D7 提交后 is_applying=true", j.get("data", {}).get("is_applying") is True)

# 提现（非分销商应被拒）
r = p("user.dealer/withdraw/submit", data={"data": json.dumps({"money": 1, "pay_type": 10})}); j = r.json()
check("D8 withdraw/submit 非分销商被拒", j.get("code") != 1, str(j)[:120])

r = g("user.dealer/withdraw/lists"); j = r.json()
check("D9 withdraw/lists code=1", j.get("code") == 1)

# ============ 第 10 批 退款 + 好友代付 ============
r = g("user.refund/lists"); j = r.json()
check("R1 refund/lists code=1", j.get("code") == 1, str(j)[:120])

# 取该用户的退款夹具订单商品用于申请退款
og = db(f"SELECT order_goods_id FROM yoshop_order_goods og JOIN yoshop_order o ON o.order_id=og.order_id WHERE o.order_no='TESTREFUND{USER}'")
if og:
    r = p("user.refund/apply", data={"post": json.dumps({"order_goods_id": int(og), "type": 10, "apply_desc": "不想买了", "refund_money": 1, "images": []})}); j = r.json()
    check("R2 refund/apply code=1", j.get("code") == 1, str(j)[:160])
    rid = j.get("data", {}).get("refund_id")
    if rid:
        r = g("user.refund/detail", params={"order_refund_id": rid}); j = r.json()
        check("R3 refund/detail code=1 含 expressList", j.get("code") == 1 and "expressList" in j.get("data", {}), str(j)[:120])
        r = p("user.refund/delivery", data={"post": json.dumps({"order_refund_id": rid, "express_id": "SF", "express_no": "123"})}); j = r.json()
        check("R4 refund/delivery code=1", j.get("code") == 1, str(j)[:120])
else:
    print("SKIP R2/R3/R4 无该用户订单商品")

# 好友代付：找一笔他人未支付订单
fo = db(f"SELECT order_id FROM yoshop_order WHERE user_id!={USER} AND pay_status=10 LIMIT 1")
if fo:
    r = g("order.friendpay/detail", params={"order_id": int(fo)}); j = r.json()
    check("F1 friendpay/detail code=1", j.get("code") == 1, str(j)[:120])
    r = p("order.friendpay/pay", data={"order_id": int(fo)}); j = r.json()
    check("F2 friendpay/pay code=1 含 payment", j.get("code") == 1 and "payment" in j.get("data", {}), str(j)[:160])
else:
    print("SKIP F1/F2 无他人未支付订单")

# ============ 第 11 批 配送 ============
r = g("delivery/lists"); j = r.json()
check("L1 delivery/lists code=1 非空", j.get("code") == 1 and len(j.get("data", {}).get("list", [])) > 0, str(j)[:120])

r = g("delivery/detail", params={"delivery_id": 3}); j = r.json()
check("L2 delivery/detail code=1", j.get("code") == 1, str(j)[:120])
check("L2 detail 含 shop", "shop" in j.get("data", {}).get("detail", {}))

r = g("delivery/shop"); j = r.json()
check("L3 delivery/shop code=1 非空", j.get("code") == 1 and len(j.get("data", {}).get("list", [])) > 0)

r = g("delivery/goods", params={"shop_id": 1}); j = r.json()
check("L4 delivery/goods code=1 含 detail", j.get("code") == 1 and "detail" in j.get("data", {}), str(j)[:120])

r = g("delivery/checkCountry"); j = r.json()
check("L5 delivery/checkCountry code=1 含 country", j.get("code") == 1 and "country" in j.get("data", {}), str(j)[:120])

# ============ 第 12 批 第三方 SDK 验签 ============
# 微信：构造一笔 RC 充值单用于验签
r = p("recharge/submit", data={"planId": 0, "customMoney": 5, "payType": 20}); j = r.json()
rc = j.get("data", {}).get("out_trade_no", "")
check("S0 生成 RC 订单", rc.startswith("RC"), rc)

# 负向：错误签名 -> 签名校验失败
bad = ("<xml><return_code><![CDATA[SUCCESS]]></return_code>"
       "<result_code><![CDATA[SUCCESS]]></result_code>"
       f"<out_trade_no><![CDATA[{rc}]]></out_trade_no>"
       "<transaction_id><![CDATA[TXNBAD]]></transaction_id>"
       "<sign><![CDATA[DEADBEEF]]></sign></xml>")
r = s.post(f"{BASE}/notify/order", data=bad, headers={"Content-Type": "text/xml"})
check("S1 微信错误签名被拒", "签名校验失败" in r.text, r.text[:80])

# 正向：用真实 apikey 计算签名
params = {
    "return_code": "SUCCESS", "result_code": "SUCCESS",
    "out_trade_no": rc, "transaction_id": "TXNWXOK",
    "appid": "wxappid", "mch_id": "mchid", "nonce_str": "abc",
}
sign = wx_sign(params, APIKEY)
good = "<xml>" + "".join(f"<{k}><![CDATA[{v}]]></{k}>" for k, v in params.items()) + f"<sign><![CDATA[{sign}]]></sign></xml>"
r = s.post(f"{BASE}/notify/order", data=good, headers={"Content-Type": "text/xml"})
check("S2 微信正确签名通过(SUCCESS)", "SUCCESS" in r.text, r.text[:80])

# 支付宝：负向错误签名
r = s.post(f"{BASE}/notify/alipay", data={
    "trade_status": "TRADE_SUCCESS", "out_trade_no": rc,
    "trade_no": "ALITEST", "sign": "BADSIGN", "sign_type": "RSA2"})
check("S3 支付宝错误签名被拒", "签名校验失败" in r.text, r.text[:80])

# Stripe：无 SDK 应返回 400 签名校验失败
r = s.post(f"{BASE}/notify/stripe", json={"type": "payment_intent.succeeded",
            "data": {"object": {"id": "pi_1", "metadata": {"order_no": rc}}}})
check("S4 Stripe 无 SDK 验签失败(400)", r.status_code == 400, f"[{r.status_code}]{r.text[:80]}")

print("\n==== 结果 ====")
print("FAILED =", FAILED)
sys.exit(1 if FAILED else 0)
