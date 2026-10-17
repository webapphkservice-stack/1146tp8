#!/usr/bin/env python3
# 把业务图标类 am-icon-* 改名为 icon-*（与远程 iconfont 体系统一命名）。
# 配套 public/assets/common/css/iconfont-business.css 用本地 FontAwesome 兜底 8 个远程集缺失字形。
import sys, glob, os

NAMES = ['trash', 'cloud-upload', 'pencil', 'plus', 'search', 'caret-down',
         'undo', 'refresh', 'list', 'calendar-check-o',
         # 第二批（后期模块 regionagent / delivery / flight / transfer / water 等）
         'bar-chart', 'building', 'calculator', 'calendar', 'check', 'check-circle',
         'clock-o', 'close', 'cny', 'credit-card', 'database', 'exclamation-circle',
         'file-text-o', 'home', 'inbox', 'info-circle', 'lightbulb-o', 'map-marker',
         'money', 'percent', 'rmb', 'save', 'truck', 'user', 'users', 'warning', 'wechat']

def rename_in_file(path):
    with open(path, 'r', encoding='utf-8') as fh:
        s = fh.read()
    changed = 0
    for n in NAMES:
        old = 'am-icon-' + n
        new = 'icon-' + n
        c = s.count(old)
        if c:
            s = s.replace(old, new)
            changed += c
    if changed:
        with open(path, 'w', encoding='utf-8') as fh:
            fh.write(s)
    return changed

if __name__ == '__main__':
    roots = sys.argv[1:] or ['app/store/view']
    total = 0
    for root in roots:
        for f in glob.glob(os.path.join(root, '**', '*.php'), recursive=True):
            c = rename_in_file(f)
            if c:
                print('  %-70s %d' % (f, c))
                total += c
    print('合计替换 am-icon-* -> icon-* : %d 处' % total)
