#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
P1 优惠券 / 积分抵扣接入结算 —— 端到端测试

覆盖：
  D1  结算页：可用券列表（最低消费门槛过滤 + 适用范围标记）
  D2  代金券抵扣（-20）
  D3  折扣券抵扣（9折 = -20）
  D4  积分抵扣（30 积分 = -0.30）
  D5  优惠券 + 积分叠加
  D6  不适用券被拒绝（抛业务异常，不静默忽略）
  D7  下单金额与结算页完全一致（防止两处计算漂移导致多扣款）
  D8  商品分摊总额 == 订单抵扣额（分摊无损失，核心正确性）
  D9  下单后优惠券被核销
  D10 下单后积分被扣除并写流水
  D11 取消订单后优惠券退还为未使用
  D12 取消订单后积分原额退还
  D13 用户积分为 0 时不开启积分抵扣
  D14 未达最低消费门槛的券不出现在可用列表

用法：python3 e2e_p1_discount_test.py
前置：
  1. php think run --host 127.0.0.1 --port 8082（端口见 BASE）
  2. 数据库可无密码访问（脚本会自动导入种子并注入登录态）
"""

import json
import subprocess
import urllib.request
import urllib.error
import urllib.parse

BASE = 'http://127.0.0.1:8082'
TOKEN = 'p0token123'      # seed_p0_session.php 注入，对应用户 10011
WXAPP = 10001
GOODS_ID = 10002          # DurianBB，售价 200.00，sku 10013
GOODS_SKU_ID = 0

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


def reseed():
    subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8'],
                   stdin=open('database/seed_p1_discount_test.sql', 'r'),
                   capture_output=True, text=True)
    subprocess.run(['php', 'seed_p0_session.php'], capture_output=True, text=True)


def mysql(sql):
    return subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8', '-N', '-e', sql],
                          capture_output=True, text=True).stdout.strip()


def api(path, data=None, method=None):
    url = f'{BASE}{path}'
    body = None
    if data is not None:
        body = urllib.parse.urlencode(data).encode('utf-8')
        method = method or 'POST'
    req = urllib.request.Request(url, data=body, method=method or 'GET')
    if body:
        req.add_header('Content-Type', 'application/x-www-form-urlencoded')
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode('utf-8'))


def checkout(**extra):
    """GET order/buyNow 结算页"""
    params = {
        'wxapp_id': WXAPP, 'token': TOKEN,
        'goods_id': GOODS_ID, 'goods_num': 1, 'goods_sku_id': GOODS_SKU_ID,
    }
    params.update(extra)
    return api('/api/order/buyNow?' + urllib.parse.urlencode(params))


def submit(**extra):
    """POST order/buyNow 提交订单"""
    data = {
        'wxapp_id': WXAPP, 'token': TOKEN,
        'goods_id': GOODS_ID, 'goods_num': 1, 'goods_sku_id': GOODS_SKU_ID,
    }
    data.update(extra)
    return api('/api/order/buyNow', data)


def main():
    print('=== P1 优惠券 / 积分抵扣端到端测试 ===\n')
    reseed()

    # ---------- D1 可用券列表 ----------
    r = checkout()
    d = r.get('data') or {}
    cl = d.get('coupon_list', [])
    ids = {c['user_coupon_id'] for c in cl}
    report('D1 可用券列表返回', r.get('code') == 1 and len(cl) > 0, f'{len(cl)} 张')
    # D14 满1000减100（91003）订单 200 元不满足门槛
    report('D14 未达门槛的券被过滤', 91003 not in ids, f'列表含 {sorted(ids)}')
    # D13 积分开关
    report('D1b 积分抵扣已开启', d.get('is_allow_points') is True,
           f"points_num={d.get('points_num')} money={d.get('points_money')}")

    # ---------- D2 / D3 / D4 / D5 抵扣金额 ----------
    r = checkout(coupon_id=91001)
    d1 = r.get('data') or {}
    report('D2 代金券减 20', d1.get('coupon_money') == '20.00' and d1.get('order_pay_price') == '180.00',
           f"券抵{d1.get('coupon_money')} 实付{d1.get('order_pay_price')}")

    r = checkout(coupon_id=91002)
    d2 = r.get('data') or {}
    report('D3 折扣券 9 折减 20', d2.get('coupon_money') == '20.00' and d2.get('order_pay_price') == '180.00',
           f"券抵{d2.get('coupon_money')} 实付{d2.get('order_pay_price')}")

    r = checkout(is_use_points=1)
    d3 = r.get('data') or {}
    report('D4 积分抵扣 0.30', d3.get('points_money') == '0.30' and d3.get('order_pay_price') == '199.70',
           f"积分{d3.get('points_num')}分 抵{d3.get('points_money')} 实付{d3.get('order_pay_price')}")

    r = checkout(coupon_id=91001, is_use_points=1)
    d4 = r.get('data') or {}
    report('D5 券+积分叠加', d4.get('order_pay_price') == '179.70',
           f"实付{d4.get('order_pay_price')}（券{d4.get('coupon_money')}+积分{d4.get('points_money')}）")

    # ---------- D6 不适用券 ----------
    r = checkout(coupon_id=91004)
    report('D6 不适用券被拒绝', r.get('code') == 0 and '不支持当前商品' in r.get('msg', ''),
           r.get('msg', ''))

    # ---------- D7~D10 提交订单 ----------
    before_points = int(mysql('SELECT points FROM yoshop_user WHERE user_id=10011;') or 0)

    r = submit(coupon_id=91001, is_use_points=1)
    ok = r.get('code') == 1
    order_id = (r.get('data') or {}).get('order_id') if ok else 0
    report('D7 提交订单成功', ok, r.get('msg', '') if not ok else f'order_id={order_id}')
    if not ok:
        print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
        return 1

    # 注意：订单表字段为 total_price（优惠前）/ order_price（优惠后）/ pay_price（实付），
    #       结算接口返回的 order_total_price 只是响应体的别名，落库时对应 total_price。
    row = mysql(f"SELECT total_price, order_price, pay_price, coupon_id, coupon_money, "
                f"points_num, points_money, points_bonus FROM yoshop_order WHERE order_id={order_id};")
    cols = row.split('\t')
    if len(cols) < 8:
        report('D7 落库查询', False, f'返回列数不足：{row!r}')
    else:
        total_price, order_price, pay_price = cols[0], cols[1], cols[2]
        db_coupon_money = cols[4]
        report('D7 落库金额与结算页一致', pay_price == '179.70',
               f'结算页179.70 vs 落库{pay_price}')
        report('D7b 优惠前后金额落库', total_price == '200.00' and order_price == '179.70',
               f'total={total_price} order={order_price}')
        report('D7c 优惠券金额落库', db_coupon_money == '20.00', f'coupon_money={db_coupon_money}')
        report('D7d 积分抵扣落库', cols[5] == '30' and cols[6] == '0.30',
               f"points_num={cols[5]} points_money={cols[6]}")

    # D8 分摊无损失：order_goods.coupon_money 之和 == 订单 coupon_money
    goods_coupon = mysql(f"SELECT IFNULL(SUM(coupon_money),0) FROM yoshop_order_goods WHERE order_id={order_id};")
    report('D8 商品优惠券分摊无损失', float(goods_coupon) == 20.00, f'分摊合计{goods_coupon} vs 订单20.00')

    goods_pay = mysql(f"SELECT IFNULL(SUM(total_pay_price),0) FROM yoshop_order_goods WHERE order_id={order_id};")
    report('D8b 商品实付合计 == 订单优惠后金额', float(goods_pay) == 179.70,
           f'商品合计{goods_pay} vs 订单179.70')

    # D9 优惠券核销
    is_use = mysql('SELECT is_use FROM yoshop_user_coupon WHERE user_coupon_id=91001;')
    report('D9 优惠券已核销', is_use == '1', f'is_use={is_use}')

    # D10 积分扣除
    after_points = int(mysql('SELECT points FROM yoshop_user WHERE user_id=10011;') or 0)
    report('D10 积分已扣除 30', after_points == before_points - 30,
           f'{before_points} -> {after_points}')
    log_cnt = mysql("SELECT COUNT(*) FROM yoshop_user_points_log WHERE user_id=10011 "
                    "AND value=-30;")
    report('D10b 积分流水已记录', log_cnt == '1', f'{log_cnt} 条')

    # ---------- D11 / D12 取消订单退还 ----------
    r = api('/api/user.order/cancel', {'wxapp_id': WXAPP, 'token': TOKEN, 'order_id': order_id})
    report('D11 取消订单成功', r.get('code') == 1, r.get('msg', ''))

    is_use = mysql('SELECT is_use FROM yoshop_user_coupon WHERE user_coupon_id=91001;')
    report('D11b 优惠券已退还为未使用', is_use == '0', f'is_use={is_use}')

    final_points = int(mysql('SELECT points FROM yoshop_user WHERE user_id=10011;') or 0)
    report('D12 积分已原额退还', final_points == before_points,
           f'{after_points} -> {final_points}（初始{before_points}）')

    # ---------- D13 积分为 0 时不开启 ----------
    mysql('UPDATE yoshop_user SET points=0 WHERE user_id=10011;')
    r = checkout()
    d13 = r.get('data') or {}
    report('D13 积分为 0 时不开积分抵扣', d13.get('is_allow_points') is False,
           f"is_allow_points={d13.get('is_allow_points')}")

    # ---------- D15 多商品分摊 ----------
    # 单商品场景无法暴露分摊 bug（唯一商品必然拿到全部抵扣额），
    # 必须构造「不同价格的多商品」才能真正验证权重分摊 + 余数补齐。
    print('\nD15 多商品优惠券分摊')
    q = {'wxapp_id': WXAPP, 'token': TOKEN}
    api('/api/cart/delete', dict(q))  # 清空购物车
    api('/api/cart/add', dict(q, goods_id=10002, goods_num=1, goods_sku_id=0))  # 200.00
    api('/api/cart/add', dict(q, goods_id=10003, goods_num=1, goods_sku_id=0))  # 266.00
    r = api('/api/order/cart?' + urllib.parse.urlencode(dict(q, coupon_id=91001)))
    d15 = r.get('data') or {}
    gl = d15.get('goods_list', [])
    report('D15 多商品结算返回', r.get('code') == 1 and len(gl) == 2,
           f'{len(gl)} 件商品 总额{d15.get("order_total_price")}')
    if len(gl) == 2:
        total_coupon = round(sum(float(g.get('coupon_money', 0)) for g in gl), 2)
        report('D15b 各商品分摊合计 == 券面额', abs(total_coupon - 20.00) < 0.001,
               f'分摊合计{total_coupon} vs 券面额20.00')
        report('D15c 各商品均参与分摊', all(float(g.get('coupon_money', 0)) > 0 for g in gl),
               ' / '.join(f"{g['goods_id']}:{g.get('coupon_money')}" for g in gl))
        report('D15d 订单抵扣额 == 券面额', d15.get('coupon_money') == '20.00',
               f"coupon_money={d15.get('coupon_money')}")
        report('D15e 实付 = 总额 - 券', d15.get('order_pay_price') == '446.00',
               f"466.00 - 20.00 = {d15.get('order_pay_price')}")
    # 清理购物车
    api('/api/cart/delete', dict(q))

    # ---------- D16 超时关闭订单：退还优惠券与积分 ----------
    # 这是针对缺陷 D5 的回归用例：task/command/Order.php 曾误用 where('id', ...)
    # 查询 user_coupon（主键实为 user_coupon_id），导致券回退静默失败、永久占用。
    print('\nD16 超时关闭订单退还券与积分')
    mysql("DELETE FROM yoshop_order WHERE order_id=910002;")
    mysql("UPDATE yoshop_user SET points=0 WHERE user_id=10011;")                 # 下单时已扣 30
    mysql("UPDATE yoshop_user_coupon SET is_use=1 WHERE user_coupon_id=91001;")   # 下单时已核销
    mysql("""
        INSERT INTO yoshop_order
          (order_id, order_no, total_price, order_price, coupon_id, coupon_money, points_money,
           points_num, pay_price, update_price, buyer_remark, pay_type, pay_status, pay_time,
           delivery_type, goods_type, express_price, express_id, express_company, delivery_id,
           express_no, delivery_status, delivery_time, receipt_status, receipt_time, order_status,
           points_bonus, is_settled, transaction_id, is_comment, order_source, order_source_id,
           user_id, is_delete, wxapp_id, create_time, update_time, is_vip)
        VALUES
          (910002,'P1TESTCLOSE0002',200.00,179.70,91001,20.00,0.30,30,179.70,0,'',20,10,0,10,10,
           0,0,'',0,'',10,0,10,0,10,200,0,'',0,10,0,10011,0,10001,
           UNIX_TIMESTAMP()-5*86400, UNIX_TIMESTAMP()-5*86400, 0);
    """)
    before_use = mysql('SELECT is_use FROM yoshop_user_coupon WHERE user_coupon_id=91001;')
    subprocess.run(['php', 'think', 'order:cron', f'--wxapp={WXAPP}'],
                   capture_output=True, text=True)
    after_use = mysql('SELECT is_use FROM yoshop_user_coupon WHERE user_coupon_id=91001;')
    points_now = mysql('SELECT points FROM yoshop_user WHERE user_id=10011;')
    order_status = mysql('SELECT order_status FROM yoshop_order WHERE order_id=910002;')
    report('D16 超时订单已关闭', order_status == '20', f'order_status={order_status}')
    report('D16b 优惠券已退还（D5 回归）', before_use == '1' and after_use == '0',
           f'is_use {before_use} -> {after_use}')
    report('D16c 积分已退还', points_now == '30', f'0 -> {points_now}')
    bonus_log = mysql("SELECT COUNT(*) FROM yoshop_user_points_log WHERE user_id=10011 "
                      "AND `describe` LIKE '订单超时关闭退还%';")
    report('D16d 退还流水已记录', bonus_log == '1', f'{bonus_log} 条')
    mysql("DELETE FROM yoshop_order WHERE order_id=910002;")

    print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
    return 0 if FAILED == 0 else 1


if __name__ == '__main__':
    raise SystemExit(main())
