#!/usr/bin/env python3
# 把 amazeui JS 组件(dropdown/tabs)与 DIY 动态列 迁移为 Tailwind/原生,
# 从而移除对 amazeui.min.js 的依赖。配合 layouts/layout.php 注入的 .js-tabs 初始化脚本。
import sys

def edit_user_index(s):
    old = (
        '            <div class="j-tabs am-tabs">\n'
        '\n'
        '                <ul class="am-tabs-nav am-nav am-nav-tabs">\n'
        '                    <li class="am-active"><a href="#tab1">充值余额</a></li>\n'
        '                    <li><a href="#tab2">充值积分</a></li>\n'
        '                </ul>\n'
        '\n'
        '                <div class="am-tabs-bd p-1">\n'
        '\n'
        '                    <div class="am-tab-panel p-0 am-active" id="tab1">'
    )
    new = (
        '            <div class="j-tabs js-tabs">\n'
        '\n'
        '                <ul class="flex flex-wrap border-b border-gray-200 mb-4">\n'
        '                    <li class="mr-1 -mb-px"><a class="inline-block py-2 px-4 border-b-2 border-blue-500 text-blue-600" href="javascript:;" data-target="#tab1">充值余额</a></li>\n'
        '                    <li class="mr-1 -mb-px"><a class="inline-block py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700" href="javascript:;" data-target="#tab2">充值积分</a></li>\n'
        '                </ul>\n'
        '\n'
        '                <div class="p-1">\n'
        '\n'
        '                    <div class="js-tab-panel p-0" id="tab1">'
    )
    assert old in s, 'user/index tabs block not found'
    s = s.replace(old, new)
    s = s.replace('                    <div class="am-tab-panel p-0" id="tab2">',
                  '                    <div class="js-tab-panel p-0 hidden" id="tab2">')
    # dropdown
    s = s.replace('                                            <div class="j-opSelect operation-select am-dropdown">',
                  '                                            <div class="j-opSelect operation-select relative inline-block group">')
    s = s.replace('                                                        class="am-dropdown-toggle btn btn-sm btn-secondary">',
                  '                                                        class="btn btn-sm btn-secondary">')
    s = s.replace('class="am-dropdown-content"',
                  'class="dropdown-menu absolute right-0 z-10 mt-1 hidden w-44 rounded border border-gray-200 bg-white shadow-lg group-hover:block"')
    s = s.replace('class="am-dropdown-item"', 'class=""')
    return s

def edit_dealer_user(s):
    s = s.replace(' am-dropdown"', ' relative inline-block group"')
    s = s.replace('am-dropdown-toggle ', '')
    s = s.replace('class="am-dropdown-content"',
                  'class="dropdown-menu absolute right-0 z-10 mt-1 hidden w-44 rounded border border-gray-200 bg-white shadow-lg group-hover:block"')
    return s

def edit_dealer_setting(s):
    s = s.replace('<div class="am-tabs mt-4" data-am-tabs="{noSwipe: 1}">', '<div class="js-tabs mt-4">')
    s = s.replace('<ul class="am-tabs-nav am-nav am-nav-tabs">', '<ul class="flex flex-wrap border-b border-gray-200 mb-4">')
    s = s.replace('<div class="am-tabs-bd">', '<div>')
    nav = [('tab1', '基础设置', True), ('tab2', '分销商条件', False), ('tab3', '佣金设置', False),
           ('tab4', '结算', False), ('tab5', '自定义文字', False), ('tab6', '申请协议', False),
           ('tab7', '页面背景图', False)]
    for tid, txt, act in nav:
        old = ('<li class="am-active"><a href="#%s">%s</a></li>' % (tid, txt)) if act \
              else ('<li><a href="#%s">%s</a></li>' % (tid, txt))
        acls = 'inline-block py-2 px-4 border-b-2 border-blue-500 text-blue-600' if act \
               else 'inline-block py-2 px-4 border-b-2 border-transparent text-gray-500 hover:text-gray-700'
        new = '<li class="mr-1 -mb-px"><a class="%s" href="javascript:;" data-target="#%s">%s</a></li>' % (acls, tid, txt)
        assert old in s, 'dealer/setting nav not found: ' + old
        s = s.replace(old, new)
    panels = [('tab1', 'mt-6', False), ('tab2', 'mt-6', True), ('tab3', 'mt-6', True),
              ('tab4', 'mt-6', True), ('tab5', '', True), ('tab6', 'mt-6', True), ('tab7', 'mt-6', True)]
    for tid, extra, hide in panels:
        old = ('<div class="am-tab-panel am-active %s" id="%s">' % (extra, tid)) if tid == 'tab1' \
              else ('<div class="am-tab-panel" id="%s">' % tid) if extra == '' \
              else ('<div class="am-tab-panel %s" id="%s">' % (extra, tid))
        new = ('<div class="js-tab-panel %s" id="%s">' % (extra, tid)) if tid == 'tab1' \
              else ('<div class="js-tab-panel hidden" id="%s">' % tid) if extra == '' \
              else ('<div class="js-tab-panel %s hidden" id="%s">' % (extra, tid))
        assert old in s, 'dealer/setting panel not found: ' + old
        s = s.replace(old, new)
    return s

def edit_diy(s):
    s = s.replace('class="am-avg-sm-{{ style.rowsNum }}"',
                  'class="grid" style="grid-template-columns: repeat({{ style.rowsNum }}, minmax(0, 1fr)); gap: 1rem;"')
    s = s.replace('class="data-list am-avg-sm-{{ style.layout }}"',
                  'class="data-list grid" style="grid-template-columns: repeat({{ style.layout }}, minmax(0, 1fr)); gap: 1rem;"')
    return s

def edit_edit(s):
    s = s.replace(":class=\"'am-avg-sm-' + item.style.layout\">",
                  ":class=\"'grid'\" :style=\"{ gridTemplateColumns: 'repeat(' + item.style.layout + ', minmax(0, 1fr))', gap: '1rem' }\"")
    return s

handlers = {
    'app/store/view/user/index.php': edit_user_index,
    'app/store/view/apps/dealer/user/index.php': edit_dealer_user,
    'app/store/view/apps/dealer/setting/index.php': edit_dealer_setting,
    'app/store/view/wxapp/page/tpl/diy.php': edit_diy,
    'app/store/view/wxapp/page/edit.php': edit_edit,
}

if __name__ == '__main__':
    files = sys.argv[1:] or list(handlers.keys())
    for f in files:
        if f not in handlers:
            print('跳过(无处理器):', f); continue
        with open(f, 'r', encoding='utf-8') as fh:
            s = fh.read()
        s2 = handlers[f](s)
        if s2 != s:
            with open(f, 'w', encoding='utf-8') as fh:
                fh.write(s2)
            print('已更新:', f)
        else:
            print('无变化:', f)
