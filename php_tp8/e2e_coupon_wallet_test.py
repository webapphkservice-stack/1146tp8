#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
第 8 批端到端测试：优惠券 + 钱包/余额流水 + 充值（含回调到账）
对比目标：php_tp8 接口返回结构与 php_my 契约一致，且充值回调能正确到账。
"""
import subprocess, json, sys, time
import requests

BASE = "http://127.0.0.1:8081/api"
WXAPP = 10001
TOKEN = "testtoken123"
USER = 10022
COUPON_ID = 1

def db(sql):
    r = subprocess.run(["mysql", "-uroot", "-pw2e3r4t5", "yoshop_tp8", "-N", "-e", sql],
                       capture_output=True, text=True)
    return r.stdout.strip()

def setup():
    db(f"UPDATE yoshop_user SET balance=200 WHERE user_id={USER}")
    db(f"DELETE FROM yoshop_user_coupon WHERE user_id={USER} AND coupon_id={COUPON_ID}")
    db(f"DELETE FROM yoshop_recharge_order WHERE user_id={USER}")
    db(f"DELETE FROM yoshop_user_balance_log WHERE user_id={USER} AND scene=10")

def check(name, cond, extra=""):
    print(("PASS " if cond else "FAIL ") + name + (("  -> " + extra) if extra else ""))
    if not cond:
        global FAILED
        FAILED += 1

FAILED = 0
setup()

s = requests.Session()

# A. 领券中心（免登录）
r = s.get(f"{BASE}/coupon/lists", params={"wxapp_id": WXAPP})
j = r.json()
check("A coupon/lists code=1", j.get("code") == 1, str(j)[:120])
lst = j["data"]["list"]
item = next((x for x in lst if x["coupon_id"] == COUPON_ID), None)
check("A 找到测试券", item is not None)
if item:
    check("A color 为 {value,text}", isinstance(item["color"], dict) and "value" in item["color"])
    check("A coupon_type 为 {value,text}", isinstance(item["coupon_type"], dict))
    check("A is_receive=0", item["is_receive"] == 0)

# B. 我的优惠券（初始无）
r = s.get(f"{BASE}/user.coupon/lists", params={"data_type": "all", "wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("B user.coupon/lists code=1", j.get("code") == 1)
before = [x for x in j["data"]["list"] if x["coupon_id"] == COUPON_ID]
check("B 领取前不含该券", len(before) == 0)

# C. 领取优惠券
r = s.post(f"{BASE}/user.coupon/receive", data={"coupon_id": COUPON_ID, "wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("C receive code=1", j.get("code") == 1, str(j)[:120])

# D. 领取后出现在 not_use
r = s.get(f"{BASE}/user.coupon/lists", params={"data_type": "not_use", "wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
got = [x for x in j["data"]["list"] if x["coupon_id"] == COUPON_ID]
check("D 领取后出现且未使用", len(got) == 1 and got[0]["is_use"] == 0 and got[0]["is_expire"] == 0)

# D2. 重复领取应失败
r = s.post(f"{BASE}/user.coupon/receive", data={"coupon_id": COUPON_ID, "wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("D2 重复领取被拒", j.get("code") != 1, str(j)[:120])

# E. 钱包
r = s.get(f"{BASE}/user.wallet/index", params={"wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("E wallet code=1 且 balance=200", j.get("code") == 1 and float(j["data"]["balance"]) == 200.0, str(j)[:120])

# F. 余额流水
r = s.get(f"{BASE}/balance/log/lists", params={"wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("F balance/log/lists code=1", j.get("code") == 1)

# G. 充值配置
r = s.get(f"{BASE}/recharge/index", params={"wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("G recharge/index code=1", j.get("code") == 1)
check("G plan_list 为数组", isinstance(j["data"]["plan_list"], list))

# H. 充值提交（自定义金额 10，微信）
r = s.post(f"{BASE}/recharge/submit", data={"planId": 0, "customMoney": 10, "payType": 20, "wxapp_id": WXAPP, "token": TOKEN})
j = r.json()
check("H recharge/submit code=1", j.get("code") == 1, str(j)[:160])
order_no = j["data"].get("out_trade_no", "")
check("H 返回 RC 订单号", order_no.startswith("RC"), order_no)

# I. 模拟微信回调到账
xml = ("<xml><return_code><![CDATA[SUCCESS]]></return_code>"
       "<result_code><![CDATA[SUCCESS]]></result_code>"
       f"<out_trade_no><![CDATA[{order_no}]]></out_trade_no>"
       "<transaction_id><![CDATA[TXN123456]]></transaction_id></xml>")
r = s.post(f"{BASE}/notify/order", data=xml, headers={"Content-Type": "text/xml"})
body = r.text
check("I 微信回执 SUCCESS", "SUCCESS" in body, body[:80])

# I2. 幂等：再次回调不应重复加余额
s.post(f"{BASE}/notify/order", data=xml, headers={"Content-Type": "text/xml"})

# J. 余额到账校验
bal = float(db(f"SELECT balance FROM yoshop_user WHERE user_id={USER}"))
check("J 余额到账 +10 (=210)", abs(bal - 210.0) < 1e-6, f"balance={bal}")
logcnt = db(f"SELECT count(*) FROM yoshop_user_balance_log WHERE user_id={USER} AND scene=10 AND money=10")
check("J 充值流水已写入", logcnt == "1", f"logcnt={logcnt}")
ord_status = db(f"SELECT pay_status FROM yoshop_recharge_order WHERE order_no='{order_no}'")
check("J 充值订单已支付", ord_status == "20", f"pay_status={ord_status}")

print("\n==== 结果 ====")
print("FAILED =", FAILED)
sys.exit(1 if FAILED else 0)
