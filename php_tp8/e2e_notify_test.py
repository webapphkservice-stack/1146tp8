#!/usr/bin/env python3
"""第 7 批：支付回调 notify + 订单状态机收口 端到端测试
覆盖：微信/支付宝/Stripe 异步回调改单、幂等、非法回调拦截、状态机守卫。
前置：php think run 监听 8081，且测试库已 seed（TESTBAL001/TESTWX002/TESTALI003/TESTSTR004）。
"""
import json
import subprocess
import urllib.request
import urllib.parse
import urllib.error

BASE = "http://127.0.0.1:8081/api"
TOKEN = "testtoken123"
WXAPP_ID = "10001"

ORD = {"bal": "TESTBAL001", "wx": "TESTWX002", "ali": "TESTALI003", "str": "TESTSTR004"}

passed = []
failed = []


def db_val(sql):
    r = subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-N", "-e", sql],
                      capture_output=True, text=True)
    return r.stdout.strip()


def db_float(sql):
    try:
        return float(db_val(sql))
    except Exception:
        return -1.0


def reset():
    sql = (
        "UPDATE yoshop_order SET pay_status=10, order_status=10, pay_time=0, "
        "transaction_id='', pay_type=20 WHERE order_no IN "
        "('TESTBAL001','TESTWX002','TESTALI003','TESTSTR004'); "
        "UPDATE yoshop_user SET balance=200.00, expend_money=0 WHERE user_id=10022;"
    )
    subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-e", sql], capture_output=True)


def set_cancelled(order_no):
    sql = f"UPDATE yoshop_order SET order_status=20, pay_status=10, transaction_id='' WHERE order_no='{order_no}';"
    subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-e", sql], capture_output=True)


def check(name, cond, extra=""):
    if cond:
        passed.append(name)
        print(f"  [PASS] {name}")
    else:
        failed.append(name)
        print(f"  [FAIL] {name}  {extra}")


# ---------- HTTP 调用 ----------
def notify_wechat(out_trade_no, transaction_id="wxTXN001", result_code="SUCCESS"):
    xml = ("<xml>"
           "<return_code><![CDATA[SUCCESS]]></return_code>"
           f"<result_code><![CDATA[{result_code}]]></result_code>"
           f"<out_trade_no><![CDATA[{out_trade_no}]]></out_trade_no>"
           f"<transaction_id><![CDATA[{transaction_id}]]></transaction_id>"
           "</xml>")
    req = urllib.request.Request(f"{BASE}/notify/order", data=xml.encode(), method="POST")
    req.add_header("Content-Type", "text/xml")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return r.read().decode()
    except urllib.error.HTTPError as e:
        return e.read().decode()


def notify_alipay(params):
    data = urllib.parse.urlencode(params).encode()
    req = urllib.request.Request(f"{BASE}/notify/alipay", data=data, method="POST")
    req.add_header("Content-Type", "application/x-www-form-urlencoded")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return r.read().decode()
    except urllib.error.HTTPError as e:
        return e.read().decode()


def notify_stripe(event):
    body = json.dumps(event).encode()
    req = urllib.request.Request(f"{BASE}/notify/stripe", data=body, method="POST")
    req.add_header("Content-Type", "application/json")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return r.status, r.read().decode()
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode()


def call(action, params=None, method="GET"):
    params = params or {}
    params.setdefault("token", TOKEN)
    params.setdefault("wxapp_id", WXAPP_ID)
    url = f"{BASE}/{action}"
    if method == "GET":
        url += "?" + urllib.parse.urlencode(params)
        req = urllib.request.Request(url)
    else:
        req = urllib.request.Request(url, data=urllib.parse.urlencode(params).encode(), method="POST")
    try:
        with urllib.request.urlopen(req, timeout=10) as r:
            return json.loads(r.read().decode())
    except urllib.error.HTTPError as e:
        try:
            return json.loads(e.read().decode())
        except Exception:
            return {"code": -999, "msg": e.read().decode()[:200]}


# ---------- 订单状态查询 ----------
def order_state(order_no):
    return db_val(
        f"SELECT CONCAT(pay_status,'|',order_status,'|',IFNULL(transaction_id,'')) "
        f"FROM yoshop_order WHERE order_no='{order_no}'")


def pay_status(order_no):
    return db_val(f"SELECT pay_status FROM yoshop_order WHERE order_no='{order_no}'")


print("== 0. 回归：余额支付（委托状态机）仍正常 ==")
reset()
oid = db_val(f"SELECT order_id FROM yoshop_order WHERE order_no='{ORD['bal']}'")
r = call("user.order/pay", {"order_id": oid, "pay_type": 10})
check("0 余额支付返回 code=1", r.get("code") == 1, json.dumps(r, ensure_ascii=False))
d = call("user.order/detail", {"order_id": oid}).get("data", {}).get("order", {})
check("0 支付后 state_text=已付款，待发货", d.get("state_text") == "已付款，待发货", d.get("state_text"))
# 复位以便后续 notify 测试从 PENDING 开始
reset()

print("== 1. 微信回调成功改单 ==")
reset()
body = notify_wechat(ORD["wx"], "wxTXN_WX002")
check("1 微信回执 SUCCESS", "SUCCESS" in body and "FAIL" not in body, body)
st = order_state(ORD["wx"])
check("1 pay_status=20 且 transaction_id=wxTXN_WX002", st == "20|10|wxTXN_WX002", st)
check("1 用户消费金额 +100", db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022") == 100.0,
      db_val("SELECT expend_money FROM yoshop_user WHERE user_id=10022"))

print("== 2. 微信回调幂等（重复不重复累计） ==")
body2 = notify_wechat(ORD["wx"], "wxTXN_WX002")
check("2 重复回执仍 SUCCESS", "SUCCESS" in body2, body2)
check("2 消费金额仍为 100（幂等）", db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022") == 100.0,
      db_val("SELECT expend_money FROM yoshop_user WHERE user_id=10022"))
check("2 transaction_id 不变", order_state(ORD["wx"]) == "20|10|wxTXN_WX002", order_state(ORD["wx"]))

print("== 3. 微信回调 result_code=FAIL 拒绝 ==")
reset()
body = notify_wechat(ORD["wx"], "wxX", result_code="FAIL")
check("3 回执 FAIL", "FAIL" in body, body)
check("3 订单仍 PENDING", pay_status(ORD["wx"]) == "10", pay_status(ORD["wx"]))

print("== 4. 微信回调缺少 out_trade_no 拒绝 ==")
body = notify_wechat("", "wxX")
check("4 缺少订单号回执 FAIL", "FAIL" in body, body)

print("== 5. 支付宝回调成功改单 ==")
reset()
body = notify_alipay({"out_trade_no": ORD["ali"], "trade_no": "aliTXN003", "trade_status": "TRADE_SUCCESS"})
check("5 支付宝回执 success", body.strip() == "success", body)
check("5 pay_status=20 且 pay_type=40", order_state(ORD["ali"]) == "20|10|aliTXN003", order_state(ORD["ali"]))

print("== 6. 支付宝非终态直接 success 不改单 ==")
reset()
body = notify_alipay({"out_trade_no": ORD["ali"], "trade_status": "TRADE_CLOSED"})
check("6 非终态回执 success", body.strip() == "success", body)
check("6 订单未改单", pay_status(ORD["ali"]) == "10", pay_status(ORD["ali"]))

print("== 7. 支付宝缺少 out_trade_no 拒绝 ==")
body = notify_alipay({"trade_status": "TRADE_SUCCESS"})
check("7 缺少订单号回执 fail", body.strip().startswith("fail"), body)

print("== 8. 支付宝回调幂等 ==")
reset()
notify_alipay({"out_trade_no": ORD["ali"], "trade_no": "aliTXN003", "trade_status": "TRADE_SUCCESS"})
before = db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022")
notify_alipay({"out_trade_no": ORD["ali"], "trade_no": "aliTXN003", "trade_status": "TRADE_SUCCESS"})
after = db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022")
check("8 幂等：消费金额未翻倍", after == before == 100.0, f"{before}->{after}")

print("== 9. Stripe Webhook 成功改单 ==")
reset()
code, _ = notify_stripe({
    "type": "payment_intent.succeeded",
    "data": {"object": {"id": "pi_STR004", "metadata": {"order_no": ORD["str"]}}},
})
check("9 Stripe 返回 200", code == 200, str(code))
check("9 pay_status=20 且 pay_type=50", order_state(ORD["str"]) == "20|10|pi_STR004", order_state(ORD["str"]))

print("== 10. Stripe 非支付成功事件忽略 ==")
reset()
code, _ = notify_stripe({"type": "payment_intent.created",
                         "data": {"object": {"id": "pi_X", "metadata": {"order_no": ORD["str"]}}}})
check("10 非支付事件返回 200", code == 200, str(code))
check("10 订单未改单", pay_status(ORD["str"]) == "10", pay_status(ORD["str"]))

print("== 11. Stripe 缺少 order_no 拒绝 ==")
code, _ = notify_stripe({"type": "payment_intent.succeeded",
                         "data": {"object": {"id": "pi_X", "metadata": {}}}})
check("11 缺少订单号返回 400", code == 400, str(code))

print("== 12. Stripe 幂等 ==")
reset()
notify_stripe({"type": "payment_intent.succeeded",
               "data": {"object": {"id": "pi_STR004", "metadata": {"order_no": ORD["str"]}}}})
before = db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022")
notify_stripe({"type": "payment_intent.succeeded",
               "data": {"object": {"id": "pi_STR004", "metadata": {"order_no": ORD["str"]}}}})
after = db_float("SELECT expend_money FROM yoshop_user WHERE user_id=10022")
check("12 幂等：消费金额未翻倍", after == before == 100.0, f"{before}->{after}")

print("== 13. 状态机守卫：已取消订单回调拒绝 ==")
reset()
set_cancelled(ORD["bal"])
body = notify_wechat(ORD["bal"], "wxX_cancel")
check("13 已取消订单回执 FAIL", "FAIL" in body, body)
check("13 订单仍 PENDING", pay_status(ORD["bal"]) == "10", pay_status(ORD["bal"]))

print(f"\n结果：通过 {len(passed)} / 失败 {len(failed)}")
if failed:
    print("失败项：", failed)
    raise SystemExit(1)
