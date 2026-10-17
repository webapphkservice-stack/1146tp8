#!/usr/bin/env python3
"""第 6 批：user.order/pay 端到端测试（微信/支付宝/Stripe/余额/好友代付 + 支付后改单状态）"""
import json
import urllib.request
import urllib.parse

BASE = "http://127.0.0.1:8081/api"
TOKEN = "testtoken123"
WXAPP_ID = "10001"

# 测试订单 order_id 映射
ORD = {"bal": 25, "wx": 26, "ali": 27, "str": 28, "can": 29}

passed = []
failed = []


def call(action, params=None, method="GET"):
    params = params or {}
    params.setdefault("token", TOKEN)
    params.setdefault("wxapp_id", WXAPP_ID)
    url = f"{BASE}/{action}"
    if method == "GET":
        url += "?" + urllib.parse.urlencode(params)
        req = urllib.request.Request(url)
    else:
        data = urllib.parse.urlencode(params).encode()
        req = urllib.request.Request(url, data=data, method="POST")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return json.loads(r.read().decode())
    except urllib.error.HTTPError as e:
        try:
            return json.loads(e.read().decode())
        except Exception:
            return {"code": -999, "msg": e.read().decode()[:200]}


def ok(r):
    return r.get("code") == 1


def err(r):
    return r.get("code") == 0


def check(name, cond, extra=""):
    if cond:
        passed.append(name)
        print(f"  [PASS] {name}")
    else:
        failed.append(name)
        print(f"  [FAIL] {name}  {extra}")


def reset():
    # 复位测试订单与余额，保证可重复执行
    import subprocess
    sql = (
        "UPDATE yoshop_order SET pay_status=10, order_status=10, pay_time=0, "
        "transaction_id='', pay_type=20 WHERE order_no LIKE 'TEST%'; "
        "UPDATE yoshop_user SET balance=200.00, expend_money=0 WHERE user_id=10022;"
    )
    subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-e", sql],
                   capture_output=True)


print("== A. 未登录拦截 ==")
r = call("user.order/pay", {"order_id": ORD["bal"], "pay_type": 10, "token": ""})
check("A 未登录返回 code=-1", r.get("code") == -1, json.dumps(r, ensure_ascii=False))

print("== B. 余额支付成功 + 改单状态 ==")
reset()
r = call("user.order/pay", {"order_id": ORD["bal"], "pay_type": 10})
check("B 返回成功 code=1", ok(r), json.dumps(r, ensure_ascii=False))
check("B pay_type=10", r.get("data", {}).get("pay_type") == 10)
check("B payment 为空数组", r.get("data", {}).get("payment") == [])
d = call("user.order/detail", {"order_id": ORD["bal"]}).get("data", {}).get("order", {})
check("B 支付后 pay_status=20", d.get("pay_status", {}).get("value") == 20, json.dumps(d, ensure_ascii=False))
check("B 支付后 order_status=10", d.get("order_status", {}).get("value") == 10)
check("B state_text=已付款，待发货", d.get("state_text") == "已付款，待发货", d.get("state_text"))

print("== C. 已支付订单再次支付被拒 ==")
r = call("user.order/pay", {"order_id": ORD["bal"], "pay_type": 10})
check("C 返回错误 code=0", err(r), json.dumps(r, ensure_ascii=False))
check("C 提示已支付", "已支付" in (r.get("msg") or ""), r.get("msg"))

print("== D. 微信支付参数结构 ==")
r = call("user.order/pay", {"order_id": ORD["wx"], "pay_type": 20})
check("D 返回成功", ok(r), json.dumps(r, ensure_ascii=False))
pay = r.get("data", {}).get("payment", {})
wc = pay.get("wxConfig", {})
check("D pay_type=20", r.get("data", {}).get("pay_type") == 20)
check("D wxConfig 含必填字段", all(k in wc for k in ["appId", "timeStamp", "nonceStr", "package", "signType", "paySign"]), json.dumps(wc, ensure_ascii=False))
check("D out_trade_no=TESTWX002", pay.get("out_trade_no") == "TESTWX002")
d = call("user.order/detail", {"order_id": ORD["wx"]}).get("data", {}).get("order", {})
check("D 网关支付不立即改单(pay_status=10)", d.get("pay_status", {}).get("value") == 10, json.dumps(d, ensure_ascii=False))

print("== E. 支付宝支付参数结构 ==")
r = call("user.order/pay", {"order_id": ORD["ali"], "pay_type": 40})
pay = r.get("data", {}).get("payment", {})
check("E 返回成功", ok(r), json.dumps(r, ensure_ascii=False))
check("E 含 order_string/trade_no/out_trade_no", all(k in pay for k in ["order_string", "trade_no", "out_trade_no"]), json.dumps(pay, ensure_ascii=False))

print("== F. Stripe 支付参数结构 ==")
r = call("user.order/pay", {"order_id": ORD["str"], "pay_type": 50})
pay = r.get("data", {}).get("payment", {})
check("F 返回成功", ok(r), json.dumps(r, ensure_ascii=False))
check("F 含 clientSecret/publishableKey/merchantName", all(k in pay for k in ["clientSecret", "publishableKey", "merchantName"]), json.dumps(pay, ensure_ascii=False))

print("== G. 不支持的支付方式 ==")
r = call("user.order/pay", {"order_id": ORD["str"], "pay_type": 999})
check("G 返回错误", err(r), json.dumps(r, ensure_ascii=False))

print("== H. 订单不存在 ==")
r = call("user.order/pay", {"order_id": 999999, "pay_type": 20})
check("H 返回错误且提示订单不存在", err(r) and "订单不存在" in (r.get("msg") or ""), json.dumps(r, ensure_ascii=False))

print("== I. 已取消订单不可支付 ==")
reset()
c = call("user.order/cancel", {"order_id": ORD["can"]}, method="POST")
check("I 取消成功", ok(c), json.dumps(c, ensure_ascii=False))
r = call("user.order/pay", {"order_id": ORD["can"], "pay_type": 20})
check("I 取消后支付被拒", err(r) and "不可支付" in (r.get("msg") or ""), json.dumps(r, ensure_ascii=False))

print(f"\n结果：通过 {len(passed)} / 失败 {len(failed)}")
if failed:
    print("失败项：", failed)
    raise SystemExit(1)
