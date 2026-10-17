#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import json, requests, re, subprocess
BASE = "http://127.0.0.1:8081/api"
WXAPP = 10001; TOKEN = "testtoken123"; USER = 10022
def db(sql):
    r = subprocess.run(["mysql","-uroot","-pw2e3r4t5","yoshop_tp8","-N","-e",sql],capture_output=True,text=True)
    return r.stdout.strip()
og = db(f"SELECT order_goods_id FROM yoshop_order_goods og JOIN yoshop_order o ON o.order_id=og.order_id WHERE o.order_no='TESTREFUND{USER}'")
s = requests.Session()
payload = {"wxapp_id":WXAPP,"token":TOKEN,"post": json.dumps({"order_goods_id":int(og),"type":10,"apply_desc":"不想买了","refund_money":1,"images":[]})}
r = s.post(f"{BASE}/user.refund/apply", data=payload)
text = r.text
# strip tags crudely
plain = re.sub(r'<style.*?</style>', '', text, flags=re.S)
plain = re.sub(r'<script.*?</script>', '', plain, flags=re.S)
plain = re.sub(r'<[^>]+>', ' ', plain)
plain = re.sub(r'\s+', ' ', plain)
print(plain[:2500])
