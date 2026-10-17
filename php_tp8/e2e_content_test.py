#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
内容类（文章 / 签到 / 帮助中心）端到端测试

背景：用户选择「先做内容类」。这三个模块有真实数据（文章 7 篇、帮助 3 条），
迁移后立即可见。本测试覆盖：
  - 文章：index（分类）/ lists（列表分页）/ detail（详情，阅读量 +1）
  - 签到：index（兼容未登录）/ submit（登录签到，+10 积分不翻倍）/ 重复签到拦截
  - 帮助：wxapp/help → Wxapp 控制器 help 方法（路径首段必须是控制器名，否则 404）

注意：signin/submit 会写入签到记录与积分，测试末尾自动清理，不污染用户数据。

用法：python3 e2e_content_test.py
前置：
  1) php think run --host 127.0.0.1 --port 8082（或设置 TP8_BASE）
  2) php seed_p0_session.php（注入 token=p0token123 -> user_id=10011）
"""

import json
import os
import subprocess
import urllib.request
import urllib.error

BASE = os.environ.get('TP8_BASE', 'http://127.0.0.1:8082')
WXAPP = 10001
TOKEN = 'p0token123'
USER_ID = 10011

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


def get(path, token=None):
    url = BASE + path
    req = urllib.request.Request(url, method='GET')
    if token:
        req.add_header('token', token)
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode('utf-8'))


def post(path, token=None):
    url = BASE + path
    req = urllib.request.Request(url, data=b'', method='POST')
    req.add_header('Content-Type', 'application/x-www-form-urlencoded')
    if token:
        req.add_header('token', token)
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode('utf-8'))


def mysql(sql):
    out = subprocess.run(['mysql', '-u', 'root', 'yoshop_tp8', '-N', '-e', sql],
                        capture_output=True, text=True).stdout.strip()
    return out


def main():
    print('=== 内容类（文章/签到/帮助）端到端测试 ===\n')
    # 确保登录态存在
    subprocess.run(['php', 'seed_p0_session.php'], capture_output=True, text=True)
    # 幂等：清掉今日已有的签到记录与流水，保证可重复运行（避免历史测试残留导致「今日已签到」）
    mysql(f"DELETE FROM yoshop_signin WHERE user_id={USER_ID} AND signin_date=CURDATE();")
    mysql(f"DELETE FROM yoshop_user_points_log WHERE user_id={USER_ID} "
          f"AND `describe`='每日签到奖励' AND create_time >= UNIX_TIMESTAMP() - 86400;")

    # ---------- 文章 ----------
    r = get(f'/api/article/index?wxapp_id={WXAPP}')
    report('文章分类列表', r.get('code') == 1 and len(r.get('data', {}).get('categoryList', [])) > 0,
           f"{len(r.get('data', {}).get('categoryList', []))} 个分类")

    r = get(f'/api/article/lists?wxapp_id={WXAPP}&category_id=0&page=1')
    list_data = (r.get('data', {}).get('list', {}) or {})
    report('文章列表（分页）', r.get('code') == 1 and int(list_data.get('total', 0)) > 0,
           f"total={list_data.get('total')} 当前页={len(list_data.get('data', []))} 篇")
    aid = (list_data.get('data') or [{}])[0].get('article_id')

    r = get(f'/api/article/detail?wxapp_id={WXAPP}&article_id={aid}')
    d = (r.get('data', {}) or {}).get('detail', {})
    report('文章详情字段完整',
           all(k in d for k in ('article_id', 'article_title', 'image', 'article_content')),
           d.get('article_title', '')[:20])
    report('文章详情图片为完整 URL', str(d.get('image', '')).startswith('http'),
           str(d.get('image', ''))[:50])

    # ---------- 帮助中心 ----------
    r = get(f'/api/wxapp/help?wxapp_id={WXAPP}')
    report('帮助中心列表', r.get('code') == 1 and len(r.get('data', {}).get('list', [])) > 0,
           f"{len(r.get('data', {}).get('list', []))} 条")

    # ---------- 签到（未登录兼容） ----------
    r = get(f'/api/signin/index?wxapp_id={WXAPP}')
    report('签到状态（未登录兼容）',
           r.get('code') == 1 and r.get('data', {}).get('is_signin_open') == 1,
           f"is_signin_open={r.get('data', {}).get('is_signin_open')}")

    # ---------- 签到（登录提交） ----------
    before = int(mysql(f'SELECT points FROM yoshop_user WHERE user_id={USER_ID};') or 0)
    r = post(f'/api/signin/submit?wxapp_id={WXAPP}&token={TOKEN}')
    after = int(mysql(f'SELECT points FROM yoshop_user WHERE user_id={USER_ID};') or 0)
    report('签到提交成功', r.get('code') == 1, r.get('msg', ''))
    report('签到积分 +10 不翻倍', after - before == 10,
           f"{before} -> {after} (Δ={after - before})")
    report('签到流水已记录',
           mysql(f"SELECT COUNT(*) FROM yoshop_user_points_log WHERE user_id={USER_ID} "
                 f"AND `describe`='每日签到奖励';") == '1', '1 条')

    # 重复签到应被拦截，积分不再变化
    r = post(f'/api/signin/submit?wxapp_id={WXAPP}&token={TOKEN}')
    after2 = int(mysql(f'SELECT points FROM yoshop_user WHERE user_id={USER_ID};') or 0)
    report('重复签到被拦截', r.get('code') == 0 and after2 == after,
           f"msg={r.get('msg')}, 积分仍={after2}")

    # 已登录再次查看状态应反映今日已签
    r = get(f'/api/signin/index?wxapp_id={WXAPP}&token={TOKEN}')
    report('已登录签到状态正确',
           r.get('data', {}).get('is_signin_today') is True,
           f"is_signin_today={r.get('data', {}).get('is_signin_today')}")

    # ---------- 清理测试脏数据（不污染用户 10011） ----------
    mysql(f"DELETE FROM yoshop_signin WHERE user_id={USER_ID} AND signin_date=CURDATE();")
    mysql(f"DELETE FROM yoshop_user_points_log WHERE user_id={USER_ID} "
          f"AND `describe`='每日签到奖励' AND create_time >= UNIX_TIMESTAMP() - 600;")
    mysql(f"UPDATE yoshop_user SET points = points - 10 WHERE user_id={USER_ID} "
          f"AND points >= 10;")
    print('\n（已清理本次签到的测试数据）')

    print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
    return 0 if FAILED == 0 else 1


if __name__ == '__main__':
    raise SystemExit(main())
