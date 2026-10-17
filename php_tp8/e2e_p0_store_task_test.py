#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
P0 端到端测试：store 发货 + task 定时维护

覆盖：
  A. store 商家后台
    T1 商家登录（yoshop_hash 密码兼容）
    T2 错误密码被拒
    T3 未登录访问后台接口被拦截
    T4 待发货订单列表 + 物流公司列表
    T5 订单发货成功（物流信息落库 + 状态机改发货状态）
    T6 重复发货被拒（幂等保护）
    T7 未付款订单发货被拒（发货条件守卫）
  B. task 定时维护（php think order:cron）
    T8  未支付订单自动关闭 + 回退库存
    T9  已发货订单自动确认收货
    T10 已完成订单结算并发放分销佣金
    T11 销量回退不出现 unsigned 下溢（sales_actual 为 0 时不会 500）

用法：python3 e2e_p0_store_task_test.py
前置：
  1. php think run --host 127.0.0.1 --port 8081
  2. mysql -u root yoshop_tp8 < database/seed_p0_test.sql（脚本会自动重跑以保证可重复）
"""

import json
import subprocess
import urllib.request
import urllib.error
import urllib.parse

import os
BASE = os.environ.get('TP8_BASE', 'http://127.0.0.1:8081')

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


def request(path, data=None, token=None, method=None):
    url = BASE + path
    body = None
    if data is not None:
        body = urllib.parse.urlencode(data).encode('utf-8')
        method = method or 'POST'
    req = urllib.request.Request(url, data=body, method=method or 'GET')
    if body:
        req.add_header('Content-Type', 'application/x-www-form-urlencoded')
    if token:
        req.add_header('Store-Token', token)
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return resp.status, json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode('utf-8'))


def mysql(sql):
    return subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8', '-e', sql],
                          capture_output=True, text=True).stdout


def scalar(sql):
    """查询单个数值（跳过表头行）。测试需重复运行，故一律做增量校验而非写死绝对值。"""
    out = mysql(sql).strip().split('\n')
    if len(out) < 2:
        return None
    try:
        return float(out[-1].strip())
    except ValueError:
        return out[-1].strip()


def reseed():
    """重跑种子数据，保证测试可重复执行"""
    subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8'],
                   stdin=open('database/seed_p0_test.sql', 'r'),
                   capture_output=True, text=True)


def run_cron(wxapp=10001):
    p = subprocess.run(['php', 'think', 'order:cron', f'--wxapp={wxapp}'],
                       capture_output=True, text=True)
    return p.stdout + p.stderr


def main():
    print('=== P0 store 发货 + task 定时维护端到端测试 ===\n')
    reseed()

    # ---------- A. store 商家后台 ----------
    print('A. store 商家后台')

    # T1 登录
    code, res = request('/store/passport/login', {'user_name': 'p0admin', 'password': '123456'})
    ok = code == 200 and res.get('code') == 1 and res.get('data', {}).get('token')
    token = res.get('data', {}).get('token', '') if ok else ''
    report('T1 商家登录成功', ok, f'code={res.get("code")}')

    # T2 错误密码
    code, res = request('/store/passport/login', {'user_name': 'p0admin', 'password': 'wrong'})
    report('T2 错误密码被拒', code == 200 and res.get('code') == 0, res.get('msg', ''))

    # T3 未登录拦截
    code, res = request('/store/order/lists')
    report('T3 未登录访问被拦截', res.get('code') == -1, res.get('msg', ''))

    # T4 列表
    code, res = request('/store/order/lists?dataType=delivery', token=token)
    items = res.get('data', {}).get('list', []) if res.get('code') == 1 else []
    report('T4 待发货订单列表', code == 200 and len(items) >= 1, f'{len(items)} 条')

    code, res = request('/store/express/lists', token=token)
    express = res.get('data', {}).get('list', []) if res.get('code') == 1 else []
    report('T4b 物流公司列表', len(express) > 0, f'{len(express)} 个')

    # T5 发货
    code, res = request('/store/order/delivery',
                        {'order_id': 900001, 'express_id': 10001, 'express_no': 'SF_E2E_001'},
                        token=token)
    report('T5 订单发货成功', res.get('code') == 1, res.get('msg', ''))

    row = mysql("SELECT delivery_status, express_company, express_no FROM yoshop_order "
                "WHERE order_id=900001;")
    report('T5b 物流信息落库正确',
           '20' in row and '顺丰速运' in row and 'SF_E2E_001' in row, row.replace('\n', ' | ').strip())

    # T6 重复发货
    code, res = request('/store/order/delivery',
                        {'order_id': 900001, 'express_id': 10001, 'express_no': 'SF_E2E_002'},
                        token=token)
    report('T6 重复发货被拒', res.get('code') == 0 and '已发货' in res.get('msg', ''), res.get('msg', ''))

    # T7 未付款订单发货
    code, res = request('/store/order/delivery',
                        {'order_id': 900003, 'express_id': 10001, 'express_no': 'SF_E2E_003'},
                        token=token)
    report('T7 未付款订单发货被拒', res.get('code') == 0 and '未付款' in res.get('msg', ''), res.get('msg', ''))

    # ---------- B. task 定时维护 ----------
    print('\nB. task 定时维护（php think order:cron）')
    # 采样 cron 执行前的基准值（测试可重复运行，故一律校验增量）
    stock_before = scalar("SELECT stock_num FROM yoshop_goods_sku WHERE goods_sku_id=10001;") or 0
    money1_before = scalar("SELECT money FROM yoshop_dealer_user WHERE user_id=10011;") or 0
    money2_before = scalar("SELECT money FROM yoshop_dealer_user WHERE user_id=10012;") or 0

    out = run_cron()

    # T8 自动关闭（订单 900003 为 5 天前未支付）
    row = mysql("SELECT order_status FROM yoshop_order WHERE order_id=900003;")
    report('T8 未支付订单自动关闭', '20' in row, row.strip())

    # T8b 库存回退 2 件（订单 900003 购买 2 件）
    stock_after = scalar("SELECT stock_num FROM yoshop_goods_sku WHERE goods_sku_id=10001;") or 0
    report('T8b 库存已回退 2 件', abs((stock_after - stock_before) - 2) < 0.01,
           f'{stock_before} -> {stock_after}')

    # T9 自动确认收货（订单 900002 为 20 天前发货）
    row = mysql("SELECT receipt_status, order_status FROM yoshop_order WHERE order_id=900002;")
    report('T9 已发货订单自动确认收货', '20' in row.split('\t')[0] or '30' in row, row.replace('\n', ' ').strip())

    # T10 佣金结算（订单 900004 已完成 10 天前）
    row = mysql("SELECT is_settled FROM yoshop_order WHERE order_id=900004;")
    report('T10 已完成订单已结算', '1' in row, row.strip())

    # T10b 佣金发放：一级 +30、二级 +10
    m1 = (scalar("SELECT money FROM yoshop_dealer_user WHERE user_id=10011;") or 0) - money1_before
    m2 = (scalar("SELECT money FROM yoshop_dealer_user WHERE user_id=10012;") or 0) - money2_before
    report('T10b 分销佣金已发放（一级+30 二级+10）',
           abs(m1 - 30) < 0.01 and abs(m2 - 10) < 0.01, f'一级+{m1} 二级+{m2}')

    # T11 unsigned 下溢防护：销量为 0 的订单关闭不应报错
    # 这是本次迁移修复的真实缺陷：sales_actual 为 unsigned，用 ->dec() 自减会触发
    # 「BIGINT UNSIGNED value is out of range」，导致整单关闭失败。
    row = mysql("SELECT sales_actual FROM yoshop_goods WHERE goods_id=10001;")
    cells = row.strip().split('\n')
    sales = cells[-1].strip() if len(cells) > 1 else '0'
    err = 'out of range' in out.lower() or 'Numeric value' in out
    report('T11 销量回退无 unsigned 下溢', (not err) and sales.isdigit() and int(sales) >= 0,
           f'sales_actual={sales}')

    print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
    return 0 if FAILED == 0 else 1


if __name__ == '__main__':
    raise SystemExit(main())
