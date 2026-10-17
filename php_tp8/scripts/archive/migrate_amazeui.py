#!/usr/bin/env python3
"""
amazeui -> Tailwind 视图迁移脚本（基于 amazeui-to-tailwind-map.md 字典）。
用法: python3 scripts/migrate_amazeui.py <file1.php> [file2.php ...]
- 替换已知 am-* 布局/组件类为 Tailwind 等价
- 处理 JS 组件: data-am-ucheck(原生 accent) / data-am-selected(原生 form-select)
- 缩进无关: 用正则区分 spec-batch 内联表单组(直接 <input> 子元素) 与普通两列表单组
- 报告每个文件迁移后的「非图标 am-* 残留」(应为空)
"""
import re
import sys
import collections


def migrate(s: str) -> str:
    # 1) data-am-* 组件属性: 删除 (selected 增强为原生 form-select; widget/datepicker 触发 amazeui 增强, 移除不影响渲染)
    s = re.sub(r'\s*data-am-selected(="[^"]*")?', '', s)
    s = re.sub(r'\s*data-am-widget(="[^"]*")?', '', s)
    s = re.sub(r'\s*data-am-datepicker(="[^"]*")?', '', s)

    # 2) select 无 class 的加 form-select
    s = re.sub(
        r'(<select\s[^>]*?)(>)',
        lambda m: (m.group(1) + ' class="form-select"')
        if 'class=' not in m.group(1) else m.group(0),
        s,
    )

    # 3) radio: 删 data-am-ucheck 令牌; 在 <input type="radio" 后加 class
    #    注意: 不能用 <input ... data-am-ucheck ...> 整体正则, 否则标签内若含 <?= ?> ,
    #    [^>]*?> 会误吞 input 闭合 > 导致 PHP 解析失败。改为移除令牌 + 在开头插入 class。
    s = re.sub(r'\s*data-am-ucheck', '', s)
    if 'w-4 h-4 accent-blue-600' not in s:
        s = re.sub(r'<input type="radio"', '<input type="radio" class="w-4 h-4 accent-blue-600"', s)

    # 4) 容器/结构
    s = s.replace('row-content am-cf', 'row-content')
    s = s.replace('am-u-sm-12 am-u-md-12 am-u-lg-12', 'w-full')
    s = s.replace('am-u-sm-12 am-u-md-12', 'w-full')
    s = s.replace('widget am-cf', 'widget')
    s = s.replace('widget-head am-cf', 'widget-head')
    s = s.replace('widget-title am-fl', 'widget-title')
    s = s.replace('am-form tpl-form-line-form', 'tpl-form-line-form')

    # 5) form-group 分层（缩进无关）+ goods 规格区复合
    #    a) 已知复合（Vue 规格区）
    s = s.replace('goods-spec-many am-form-group',
                  'goods-spec-many am-form-group mb-4 flex flex-wrap items-start gap-x-2')
    s = s.replace('spec-group-add-item am-form-group',
                  'spec-group-add-item am-form-group mb-4 flex flex-col gap-1')
    #    b) spec-batch 内联组: 直接以 <input 为子元素的 form-group -> inline-flex
    s = re.sub(r'<div class="am-form-group">(\s*)<input',
               r'<div class="spec-form-inline inline-flex items-center gap-1">\1<input', s)
    #    c) 普通 form-group (am-form-group 位于 class 开头) -> 两列 flex
    s = s.replace('class="am-form-group"',
                  'class="am-form-group mb-4 flex flex-wrap items-start gap-x-2"')

    # 6) label
    s = s.replace('am-form-label form-require', 'form-require')
    s = s.replace('am-form-label', 'text-sm text-gray-600 shrink-0')

    # 7) 栅格（通用 am-u-{bp}-{n} / am-u-{bp}-push-{n} / am-u-end）
    _fracs = {1: '1/12', 2: '1/6', 3: '1/4', 4: '1/3', 5: '5/12', 6: '1/2',
              7: '7/12', 8: '2/3', 9: '3/4', 10: '5/6', 11: '11/12', 12: 'full'}

    def _col(bp, n):
        return (f'{bp}:w-full' if n == 12 else f'{bp}:w-{_fracs[n]}')

    def _ml(v):
        p = int(v) / 12 * 100
        return f'{p:.0f}' if p == int(p) else f'{p:.3f}'

    s = re.sub(r'am-u-(sm|md|lg)-(\d+)',
               lambda m: _col(m.group(1), int(m.group(2))), s)
    s = re.sub(r'am-u-(sm|md|lg)-push-(\d+)',
               lambda m: f"{m.group(1)}:ml-[{_ml(m.group(2))}%]", s)
    s = s.replace('am-u-end', '')

    # 8) radio label + btn（变体优先）
    s = s.replace('am-radio-inline', 'inline-flex items-center gap-2 mr-4')
    s = s.replace('am-btn-secondaryam-radius', 'btn-secondary rounded')
    s = s.replace('am-btn-secondary', 'btn-secondary')
    s = s.replace('am-btn-default', 'btn-default')
    s = s.replace('am-btn-primary', 'btn-primary')
    s = s.replace('am-btn-success', 'btn-success')
    s = s.replace('am-btn-xs', 'btn-xs')
    s = s.replace('am-btn-sm', 'btn-sm')
    s = s.replace('am-btn-lg', 'btn-lg')
    s = s.replace('am-radius', 'rounded')
    s = s.replace('am-btn', 'btn')
    s = s.replace('am-text-xs', 'text-xs')
    s = s.replace('am-text-right', 'text-right')
    s = s.replace('am-text-left', 'text-left')
    s = s.replace('am-text-muted', 'text-gray-500')
    s = s.replace('am-text-truncate', 'truncate')
    s = s.replace('am-btn-toolbar', 'flex items-center gap-2')

    # 9) 浮动/清除
    s = s.replace('am-fl', '')
    s = s.replace('am-cf', '')
    s = s.replace('am-fr', 'float-right')

    # 10) 表单组件
    s = s.replace('am-form-inline', 'flex flex-wrap items-center gap-3')
    s = s.replace('am-input-group-label', 'input-group-label')
    s = s.replace('am-input-group-sm', 'input-group-sm')
    s = s.replace('am-input-group-btn', 'input-group-btn')
    s = s.replace('am-input-group', 'input-group')
    s = s.replace('am-form-field', 'form-input')
    s = s.replace('am-form-file', 'block')
    s = s.replace('am-form--static', 'text-sm text-gray-700')

    # 11) 间距（长优先）
    s = s.replace('am-margin-top-lg', 'mt-6')
    s = s.replace('am-margin-top-sm', 'mt-3')
    s = s.replace('am-margin-top', 'mt-4')
    s = s.replace('am-margin-bottom-lg', 'mb-6')
    s = s.replace('am-margin-bottom-sm', 'mb-3')
    s = s.replace('am-margin-bottom-xs', 'mb-1')
    s = s.replace('am-margin-bottom', 'mb-4')
    s = s.replace('am-margin-left-xs', 'ml-1')
    s = s.replace('am-margin-right-lg', 'mr-6')
    s = s.replace('am-margin-right', 'mr-4')
    s = s.replace('am-padding-top-sm', 'pt-3')
    s = s.replace('am-padding-top-xs', 'pt-1')
    s = s.replace('am-padding-top', 'pt-4')

    # 12) 排版
    s = s.replace('am-text-middle', 'align-middle')
    s = s.replace('am-vertical-align-middle', 'align-middle')
    s = s.replace('am-text-nowrap', 'whitespace-nowrap')
    s = s.replace('am-text-center', 'text-center')
    s = s.replace('am-text-sm', 'text-sm')
    s = s.replace('am-link-muted', 'text-gray-500')
    s = s.replace('am-scrollable-horizontal', 'overflow-x-auto')
    s = s.replace('am-field-valid', 'border-green-500')
    s = s.replace('am-avg-sm-2', 'grid grid-cols-2 gap-4')
    # 12b) 其它罕见布局/排版类
    s = s.replace('am-padding-left', 'pl-4')
    s = s.replace('am-padding-0', 'p-0')
    s = s.replace('am-padding-xs', 'p-1')
    s = s.replace('am-padding-bottom-lg', 'pb-6')
    s = s.replace('am-padding-bottom-xl', 'pb-8')
    s = s.replace('am-padding-bottom', 'pb-4')
    s = s.replace('am-padding', 'p-4')
    s = s.replace('am-block', 'block')
    s = s.replace('am-hide', 'hidden')
    s = s.replace('am-margin-left-sm', 'ml-3')
    s = s.replace('am-text-success', 'text-green-600')
    s = s.replace('am-text-danger', 'text-red-600')
    s = s.replace('am-text-primary', 'text-blue-600')
    s = s.replace('am-divider-dashed', 'border-t border-dashed border-gray-200 my-4')
    s = s.replace('am-divider', 'border-t border-gray-200 my-4')
    s = s.replace('am-container', 'mx-auto max-w-7xl px-4')
    s = s.replace('am-scrollable-vertical', 'overflow-y-auto')
    s = s.replace('am-disabled', 'opacity-50 cursor-not-allowed')
    s = s.replace('am-checkbox-inline', 'inline-flex items-center gap-2 mr-4')
    s = re.sub(r'am-avg-sm-(\d+)', r'grid grid-cols-\1 gap-4', s)
    # 12d) 第二批补全：后期模块（regionagent / delivery / flight / transfer / water）残留
    #      栅格容器
    s = re.sub(r'(?<=["\s])am-g(?=["\s])', 'flex flex-wrap', s)
    #      输入/排版
    s = s.replace('am-input-sm', 'form-input text-sm py-1')
    s = s.replace('am-text-secondary', 'text-blue-600')
    s = s.replace('am-text-warning', 'text-amber-600')
    s = s.replace('am-text-gray', 'text-gray-500')
    s = s.replace('am-text-lg', 'text-lg')
    s = s.replace('am-form-control-static', 'text-sm text-gray-700')
    s = re.sub(r'(?<=["\s])am-form-horizontal(?=["\s])', '', s)
    #      提示条（变体优先于通用）
    s = s.replace('am-alert-danger', 'bg-red-50 text-red-700 border border-red-200 rounded px-4 py-3 mb-4')
    s = s.replace('am-alert-success', 'bg-green-50 text-green-700 border border-green-200 rounded px-4 py-3 mb-4')
    s = s.replace('am-alert-warning', 'bg-amber-50 text-amber-700 border border-amber-200 rounded px-4 py-3 mb-4')
    s = s.replace('am-alert', 'bg-blue-50 text-blue-700 border border-blue-200 rounded px-4 py-3 mb-4')
    #      分页（变体优先于通用）
    s = s.replace('am-pagination-centered', 'flex justify-center')
    s = s.replace('am-pagination-quick', 'inline-flex items-center')
    s = s.replace('am-pagination', 'inline-flex items-center gap-1')
    #      模态框（配合 public/assets/store/js/amz-modal.js 垫片；data-am-modal-* 属性保留作钩子）
    s = s.replace('am-modal-prompt-input', 'form-input w-full')
    s = s.replace('am-modal-hd', 'px-4 py-3 border-b font-medium')
    s = s.replace('am-modal-bd', 'p-4')
    s = s.replace('am-modal-footer', 'px-4 py-3 border-t flex justify-end gap-2')
    s = s.replace('am-modal-btn', 'btn')
    s = s.replace('am-modal-dialog', 'relative bg-white rounded-lg shadow-lg w-96 max-w-full')
    s = s.replace('am-modal-prompt', 'fixed inset-0 z-50 flex items-center justify-center hidden')
    s = s.replace('am-modal', 'fixed inset-0 z-50 flex items-center justify-center hidden')
    #      日期选择器容器/附加按钮（amazeui 已移除，改原生 date 或保留 input-group）
    s = s.replace('am-datepicker-date', '')
    s = s.replace('am-datepicker-add-on', 'input-group-btn')

    # 12c) am-datepicker 类令牌(属性已在 #1 删除) 边界移除
    s = re.sub(r'(?<=["\s])am-datepicker(?=["\s])', '', s)

    # 13) 表格（具体 token 优先于通用 am-table）
    s = re.sub(
        r'spec-sku-tabel am-table am-table-bordered am-table-centered\s+am-margin-bottom-xs am-text-nowrap',
        'spec-sku-tabel table-base border text-center mb-1 whitespace-nowrap', s)
    s = s.replace('am-table-bordered', 'border')
    s = s.replace('am-table-centered', 'text-center')
    s = s.replace('am-table-striped', '')
    s = s.replace('am-table-compact', '')
    s = s.replace('am-table ', 'table-base ')
    s = s.replace('am-table', 'table-base')

    # 14) 徽章
    s = s.replace('am-badge-success', 'badge-success')
    s = s.replace('am-badge-warning', 'badge-warning')
    s = s.replace('am-badge-secondary', 'badge-secondary')
    s = s.replace('am-badge ', 'badge ')
    s = s.replace('am-badge', 'badge')

    # 15) btn-group
    s = s.replace('am-btn-group-xs', 'btn-group-xs')
    s = s.replace('am-btn-group', 'btn-group')

    # 16) am-form-group 改名为 form-group（保留稳定钩子, 供内联 JS 选择器如 .form-group 使用）
    s = s.replace('am-form-group ', 'form-group ')
    s = s.replace('am-form-group"', 'form-group"')

    # 17) 整词移除容器/表单类（用边界避免误伤 data-am-selected / am-form-*）
    s = re.sub(r'(?<=["\s])am-selected(?=["\s])', '', s)
    s = re.sub(r'(?<=["\s])am-widget(?=["\s])', '', s)
    s = re.sub(r'(?<=["\s])am-form(?=["\s])', '', s)

    return s


def residuals(s: str):
    return collections.Counter(re.findall(r'am-[a-zA-Z0-9\-]+', s))


if __name__ == '__main__':
    for p in sys.argv[1:]:
        try:
            t = open(p, encoding='utf-8').read()
        except OSError as e:
            print(f'{p}: 读取失败 {e}')
            continue
        t2 = migrate(t)
        left = residuals(t2)
        non_icon = {k: v for k, v in left.items() if not k.startswith('am-icon')}
        open(p, 'w', encoding='utf-8').write(t2)
        print(f'{p}: 非图标 am-* 残留 -> {non_icon if non_icon else "NONE ✓"}')
