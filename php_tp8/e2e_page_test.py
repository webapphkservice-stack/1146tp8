#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
首页装修（page/index）端到端测试

背景：HomeScreen 完全依赖 page/index 渲染首页；该接口 500 时前端 catch 静默，
      表现为「首页一片空白」。因此本测试同时校验：
      1) 接口可用且组件齐全
      2) 动态组件（goods / article）有数据
      3) 所有图片 URL 为完整地址（缺失 APP_HOST 时会退化成相对路径，图片全挂）

用法：python3 e2e_page_test.py
前置：php think run --host 127.0.0.1 --port 8082（或设置 TP8_BASE）
"""

import json
import os
import urllib.request
import urllib.error

BASE = os.environ.get('TP8_BASE', 'http://127.0.0.1:8082')
WXAPP = 10001

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


def get(path):
    req = urllib.request.Request(BASE + path, method='GET')
    try:
        with urllib.request.urlopen(req, timeout=20) as resp:
            return json.loads(resp.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode('utf-8'))


def is_abs_url(u):
    return isinstance(u, str) and u.startswith(('http://', 'https://'))


def main():
    print('=== 首页装修（page/index）端到端测试 ===\n')

    # P1 免登录可访问 + 返回成功
    r = get(f'/api/page/index?wxapp_id={WXAPP}&page_id=0')
    report('P1 首页接口返回成功', r.get('code') == 1, r.get('msg', ''))
    if r.get('code') != 1:
        print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
        return 1

    d = r.get('data') or {}
    items = d.get('items') or {}

    # P2 组件齐全（含 page 顶部导航，前端会剔除）
    types = [(v or {}).get('type') for v in items.values()]
    report('P2 返回装修组件', len(items) > 0, f'{len(items)} 个：{types}')
    report('P2b 含顶部导航 page 段', bool(d.get('page')), '有' if d.get('page') else '无')

    # P3 动态组件：商品
    goods = next(((v or {}).get('data') or [] for v in items.values()
                  if (v or {}).get('type') == 'goods'), None)
    if goods is None:
        report('P3 goods 组件存在', False, '装修配置中没有 goods 组件')
    else:
        report('P3 goods 组件有商品', len(goods) > 0, f'{len(goods)} 件')
        g = goods[0]
        report('P3b 商品字段完整',
               all(k in g for k in ('goods_id', 'goods_name', 'goods_price', 'image')),
               f"{g.get('goods_name')} / {g.get('goods_price')}")
        report('P3c 商品图片为完整 URL', is_abs_url(g.get('image')), str(g.get('image'))[:60])

    # P4 动态组件：文章
    article = next(((v or {}).get('data') or [] for v in items.values()
                    if (v or {}).get('type') == 'article'), None)
    if article is None:
        report('P4 article 组件存在', False, '装修配置中没有 article 组件')
    else:
        report('P4 article 组件有文章', len(article) > 0, f'{len(article)} 篇')
        if article:
            a = article[0]
            report('P4b 文章字段完整',
                   all(k in a for k in ('article_id', 'article_title', 'image', 'show_views')),
                   str(a.get('article_title'))[:30])
            report('P4c 文章封面为完整 URL', is_abs_url(a.get('image')), str(a.get('image'))[:60])

    # P5 静态组件数据保留（banner / navBar 的数据来自 page_data，不应被清空）
    banner = next(((v or {}).get('data') or [] for v in items.values()
                   if (v or {}).get('type') == 'banner'), None)
    navbar = next(((v or {}).get('data') or [] for v in items.values()
                   if (v or {}).get('type') == 'navBar'), None)
    report('P5 banner 静态数据保留', bool(banner), f'{len(banner or [])} 张')
    report('P5b navBar 静态数据保留', bool(navbar), f'{len(navbar or [])} 项')

    # P6 图标接口
    r2 = get(f'/api/page/icon?wxapp_id={WXAPP}')
    logo = ((r2.get('data') or {}).get('login_logo') or '')
    report('P6 page/icon 返回 logo', r2.get('code') == 1, logo[:60] if logo else '(空)')

    print(f'\n=== 结果：PASS {PASSED} / FAIL {FAILED} ===')
    return 0 if FAILED == 0 else 1


if __name__ == '__main__':
    raise SystemExit(main())
