# amazeui → Tailwind 迁移映射字典（基准）

> 用途：本文件是 php_tp8 后台数百个业务页从 amazeui 迁移到 Tailwind 的**唯一基准**。
> 配套：`tailwind.config.js`（preflight 当前关闭）、`src/tailwind.css`（已定义 `.btn/.btn-default/.btn-primary/.form-input/.form-select/.table-base/.badge/.badge-success/.badge-warning` 组件层）。
> 扫描基线（2026-10-06）：全站 12499 次 `am-*` 引用，155 个去重类（含 175 次 `am-icon-*` 图标字体，属图标体系，非布局类，保留 iconfont 即可）。

---

## 0. 总原则

1. **共存推进**：当前 `layout.php` 同时引 `tailwind.css` 与 `amazeui.min.css`，preflight 关闭。逐页迁移，未迁移页继续由 amazeui 渲染，互不干扰。
2. **先改容器，再改列**：栅格必须先把 `.am-g` 容器改为 `grid`/`flex`，否则 `am-u-*` 列类替换后无效。
3. **float 是重点债务**：`am-cf(863)`、`am-fl(426)`、`am-fr(186)` 大量存在，源于 amazeui float 布局。迁移时优先把父容器改为 `flex`/`grid`，然后**删除** `am-cf`，`am-fl/am-fr` 改为 `float-left/right` 或 `flex` + `ml-auto`。
4. **断点近似**：amazeui(sm≥641/md≥1025/lg≥1280) 与 Tailwind(sm640/md768/lg1024/xl1280) 差几十 px，业务无碍，直接对应 `sm/md/lg/xl`；如需严格对齐，在 `tailwind.config.js` 的 `theme.screens` 覆盖。
5. **动态拼接类**：如 `am-badge am-badge-<?= $v ?>` 生成的 `badge-success/warning`，已在 safelist 兜底；新增动态类须同步加 safelist。
6. **JS 组件**：`ucheck/selected/datepicker/dropdown/tabs` 由 `amazeui.min.js` 提供，去掉该 JS 后必须替换为原生/Tailwind/轻量库（见第 11 章）。

---

## 1. 栅格（最高频，必须整容器替换）

| amazeui | 推荐 Tailwind | 备注 |
|---|---|---|
| `.am-g` | `grid grid-cols-12 gap-4`（推荐）或 `flex flex-wrap` | **容器必须改**，否则列类无效 |
| `.am-u-sm-N` | `sm:col-span-N`（N=1..12） | grid 容器内；`sm:w-[N/12]` 用于 flex 容器 |
| `.am-u-md-N` | `md:col-span-N` | |
| `.am-u-lg-N` | `lg:col-span-N` | |
| `.am-u-xl-N` | `xl:col-span-N` | |
| `.am-u-sm-push-N` | `sm:col-start-(N+1)` | 仅 grid 有效；或用 `sm:ml-[N/12]`(flex) |
| `.am-u-end` | `sm:justify-self-end` | 末尾列右对齐；多数配合 push 重排时可省略 |
| `.am-avg-sm-N` | `grid grid-cols-N gap-4` | 等分网格 |
| `.am-u-fl` | `float-left` 或改 flex | 优先改父容器 flex |
| `.am-u-fr` | `float-right` / `ml-auto` | |

**示例**：
```html
<!-- amazeui -->
<div class="am-g">
  <div class="am-u-sm-3">左</div>
  <div class="am-u-sm-9">右</div>
</div>
<!-- Tailwind (grid) -->
<div class="grid grid-cols-12 gap-4">
  <div class="sm:col-span-3">左</div>
  <div class="sm:col-span-9">右</div>
</div>
```

---

## 2. 浮动与清除（am-cf 863 次，最大债务）

| amazeui | 推荐 Tailwind | 备注 |
|---|---|---|
| `.am-cf` | **删除**（父容器改 flex/grid 后无需） | 若保留 float 布局，自定义 `.cf::after{clear:both;content:'';display:block}` |
| `.am-fl` | `float-left` | 优先改 flex |
| `.am-fr` | `float-right` / `ml-auto` | |

---

## 3. 间距（margin / padding）

通配规则：amazeui 间距后缀 `xs≈4px / sm≈8px / (无)≈16px / lg≈24px / xl≈32px`，映射：

| amazeui | Tailwind | | amazeui | Tailwind |
|---|---|---|---|---|
| `am-margin-top-xs` | `mt-1` | | `am-padding-top` | `pt-4` |
| `am-margin-top-sm` | `mt-3` | | `am-padding-top-sm` | `pt-3` |
| `am-margin-top` | `mt-4` | | `am-padding-top-xs` | `pt-1` |
| `am-margin-top-lg` | `mt-6` | | `am-padding` | `p-4` |
| `am-margin-bottom-xs` | `mb-1` | | `am-padding-bottom` | `pb-4` |
| `am-margin-bottom-sm` | `mb-3` | | `am-padding-left` | `pl-4` |
| `am-margin-bottom` | `mb-4` | | `am-padding-right` | `pr-4` |
| `am-margin-right` | `mr-4` | | `am-padding-horizontal` | `px-4` |
| `am-margin-left-xs` | `ml-1` | | `am-padding-vertical` | `py-4` |
| `am-margin` | `m-4` | | `am-padding-0` | `p-0` |

> 实际业务多用 `*-top-*` 系列，按上表换算即可。

---

## 4. 排版（text 类）

| amazeui | Tailwind | | amazeui | Tailwind |
|---|---|---|---|---|
| `am-text-left` | `text-left` | | `am-text-xs` | `text-xs` |
| `am-text-center` | `text-center` | | `am-text-sm` | `text-sm` |
| `am-text-right` | `text-right` | | `am-text-lg` | `text-lg` |
| `am-text-middle` | `align-middle` | | `am-text-muted` | `text-gray-500` |
| `am-vertical-align-middle` | `align-middle` | | `am-text-truncate` | `truncate` |
| `am-text-nowrap` | `whitespace-nowrap` | | `am-link-muted` | `text-gray-500 hover:text-gray-700` |
| `am-hide` | `hidden` | | `am-show` | `block` |

---

## 5. 表单（am-form-group 1169 / am-form-label 900）

| amazeui | 推荐 Tailwind | 备注 |
|---|---|---|
| `.am-form` | `space-y-4` 或保留块 | 表单容器 |
| `.am-form-group` | `mb-4` | 表单组下间距 |
| `.am-form-label` | `block mb-2 font-medium text-sm text-gray-700` | |
| `.am-form-field` / `.am-input` | `.form-input`（组件层）或 `w-full border border-gray-300 rounded px-3 py-2 text-sm` | |
| `.am-form--static` | `block py-2 text-gray-700` | 静态展示文本 |
| `.am-form-file` | `block` | 配合 webuploader，保留结构 |
| `.am-field-valid` | `border-green-500` | 校验成功态，保留语义 |
| `.am-input-group` | `flex` | |
| `.am-input-group-label` | `inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l text-sm text-gray-600` | 前缀标签 |
| `.am-input-group-btn` | `inline-flex` | 按钮挂件 |
| `.am-input-group-sm` | `text-sm` | 缩小尺寸 |

**示例（已在前 6 个 data 弹层验证）**：
```html
<div class="am-input-group">
  <span class="am-input-group-label">搜索</span>
  <input class="am-form-field" ...>
  <span class="am-input-group-btn"><button class="am-btn am-btn-default">Go</button></span>
</div>
<!-- Tailwind -->
<div class="flex">
  <span class="inline-flex items-center px-3 bg-gray-100 border border-r-0 border-gray-300 rounded-l text-sm text-gray-600">搜索</span>
  <input class="form-input rounded-l-none" ...>
  <span class="inline-flex"><button class="btn btn-default rounded-l-none">Go</button></span>
</div>
```

---

## 6. 按钮（am-btn 298）

| amazeui | Tailwind | 备注 |
|---|---|---|
| `.am-btn`（基础） | `.btn`（组件层）或 `inline-flex items-center justify-center px-4 py-2 rounded text-sm font-medium` | |
| `.am-btn-default` | `.btn-default`（`bg-white border text-gray-700`） | |
| `.am-btn-primary` | `.btn-primary`（`bg-blue-600 text-white`） | |
| `.am-btn-secondary` | `bg-gray-100 text-gray-700 hover:bg-gray-200` | |
| `.am-btn-success` | `bg-green-600 text-white hover:bg-green-700` | |
| `.am-btn-warning` | `bg-yellow-500 text-white hover:bg-yellow-600` | |
| `.am-btn-danger` | `bg-red-600 text-white hover:bg-red-700` | |
| `.am-btn-link` | `text-blue-600 hover:underline` | |
| `.am-btn-xs` | `text-xs px-2 py-1` | |
| `.am-btn-sm` | `text-sm px-3 py-1.5` | |
| `.am-btn-lg` | `text-base px-5 py-2.5` | |
| `.am-btn-block` | `w-full` | |
| `.am-btn-group` | `inline-flex` + 子按钮去圆角（`rounded-none`，首尾 `rounded-l/r`） | |
| `.am-btn-group-xs/sm` | 配合尺寸类 | |
| `.am-btn-toolbar` | `flex items-center gap-2` | |
| `.am-radius` | `rounded` | |

---

## 7. 表格（am-table 100）

| amazeui | Tailwind | 备注 |
|---|---|---|
| `.am-table` | `.table-base`（组件层） | 已定义边框/内边距/奇偶行 |
| `.am-table-bordered` | `.table-base` + `border` | 或 `border border-gray-200` |
| `.am-table-striped` | `.table-base` 已含 `tbody tr:nth-child(odd)` 底色 | 无需额外类 |
| `.am-table-compact` | `.table-base text-sm` | |
| `.am-table-centered` | `.table-base text-center` | |
| `.am-scrollable-horizontal` | `overflow-x-auto` | 外层包裹 |

---

## 8. 面板 / 列表 / 徽章 / 提示

| amazeui | Tailwind | 备注 |
|---|---|---|
| `.am-panel` | `rounded border border-gray-200 bg-white` | |
| `.am-panel-default` / `.am-panel-primary` | 同上 + 顶部色条可选 | |
| `.am-panel-hd` | `px-4 py-3 border-b border-gray-200 font-medium` | |
| `.am-panel-bd` | `p-4` | |
| `.am-panel-footer` | `px-4 py-3 border-t border-gray-200` | |
| `.am-list` | `divide-y divide-gray-200` | |
| `.am-list-item` | `py-3` | |
| `.am-badge` | `.badge`（组件层） | |
| `.am-badge-success` | `.badge-success`（**safelist 已兜底**） | |
| `.am-badge-warning` | `.badge-warning`（**safelist 已兜底**） | |
| `.am-badge-secondary` | `.badge bg-gray-100 text-gray-600` | |
| `.am-alert` | `rounded border px-4 py-3` + 配色 | |
| `.am-close` | `text-gray-400 hover:text-gray-600 cursor-pointer text-xl leading-none` | |
| `.am-icon-*`（图标字体） | **保留 iconfont**（项目统一用 `iconfont icon-*`） | 不属 amazeui，迁移时勿动 |

---

## 9. 单选 / 复选（am-radio-inline 383 / am-checkbox-inline 27）

| amazeui | Tailwind | 备注 |
|---|---|---|
| `.am-radio` / `.am-checkbox` | 原生 `<input type="radio/checkbox" class="w-4 h-4 accent-blue-600">` | 去掉 `data-am-ucheck` |
| `.am-radio-inline` / `.am-checkbox-inline` | `inline-flex items-center gap-2 mr-4` | 行内排列 |
| `.am-ucheck`（JS 钩子） | 删除该类与 `data-am-ucheck` 属性 | 见第 11 章 |

> ⚠️ **致命坑（已实战踩中）**：radio/checkbox 标签内若含有 `<?= ... ?>`（如 `<?= $model['x']==10?'checked':'' ?>`），**绝不能**用 `<input ... data-am-ucheck ...>` 整体正则替换——`[^>]*?>` 会误吞 input 的闭合 `>`，编译后变成 `<?= ... ? class="...">` 触发 PHP `unexpected token "class"` 500。正确做法：仅删除 `data-am-ucheck` 令牌 + 在 `<input type="radio"` 后插入 `class="w-4 h-4 accent-blue-600"`（避开 `<?= ?>`）。已固化到 `scripts/migrate_amazeui.py`。破坏签名两种：`? class="w-4 h-4 accent-blue-600">` 与 `? class="w-4 h-4 accent-blue-600" >`，全局替换为 `?> class="w-4 h-4 accent-blue-600"` 即可修复。

---

## 10. 其它工具类

| amazeui | Tailwind |
|---|---|
| `.am-active` | 按上下文：导航用 `bg-blue-50 text-blue-600`；标签页用 `border-b-2 border-blue-600` |
| `.am-disabled` | `opacity-50 pointer-events-none` 或 `disabled:` 变体 |
| `.am-center` | `mx-auto` / `text-center` |
| `.am-scrollable-vertical` | `overflow-y-auto` |
| `.am-comment` / `.am-comment-*` | `flex gap-3` + 头像/内容块 |
| `.am-thumbnail` | `rounded border border-gray-200 p-1` |

---

## 11. JS 组件替代方案（去掉 amazeui.min.js 后必须替换）

### 11.1 uCheck（data-am-ucheck / am-ucheck，294 处）
**原**：amazeui 美化 checkbox/radio。
**替**：去掉 `data-am-ucheck` 与 `am-ucheck` 类，用原生控件 + `accent-*`：
```html
<label class="inline-flex items-center gap-2 mr-4">
  <input type="checkbox" class="w-4 h-4 accent-blue-600" name="x" value="1">
  <span>选项</span>
</label>
```

### 11.2 Selected（data-am-selected / am-selected，69 处）
**原**：amazeui 美化原生 select（含搜索）。
**替**：原生 `<select class="form-select">`（组件层已定义），去掉 `data-am-selected`：
```html
<select name="cat" class="form-select">
  <option value="1">分类一</option>
</select>
```
> 若需原搜索能力，接 Tom Select（`tom-select` CDN + `new TomSelect('.form-select')`）。

### 11.3 DatePicker（data-am-datepicker / am-datepicker，14 处）
**原**：amazeui 日期选择。
**替（推荐轻量）flatpickr**（jQuery 友好、中文包）：
```html
<input type="text" class="form-input datepicker" name="date">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/zh.js"></script>
<script>
  flatpickr('.datepicker', { locale: 'zh', dateFormat: 'Y-m-d' });
</script>
```
> 或最简：原生 `<input type="date" class="form-input">`（无依赖，但 UI 简陋）。

### 11.4 Dropdown（data-am-dropdown / am-dropdown，13 处）
**原**：点击/悬停下拉菜单。
**替**：Tailwind + 极简 JS 切换 `hidden`：
```html
<div class="relative inline-block" data-dropdown>
  <button class="btn btn-default" data-dropdown-toggle>菜单 ▾</button>
  <div class="absolute right-0 mt-1 hidden w-40 rounded border border-gray-200 bg-white shadow-lg" data-dropdown-menu>
    <a class="block px-4 py-2 hover:bg-gray-50" href="#">项一</a>
  </div>
</div>
<script>
  document.querySelectorAll('[data-dropdown]').forEach(d=>{
    const t=d.querySelector('[data-dropdown-toggle]'), m=d.querySelector('[data-dropdown-menu]');
    t.addEventListener('click',e=>{e.stopPropagation();m.classList.toggle('hidden');});
    document.addEventListener('click',()=>m.classList.add('hidden'));
  });
</script>
```

### 11.5 Tabs（data-am-tabs / am-tabs，7 处）
**原**：选项卡切换。
**替**：按钮组 + 面板 `hidden` 切换：
```html
<div class="flex border-b border-gray-200 mb-4" data-tabs>
  <button class="px-4 py-2 border-b-2 border-blue-600 text-blue-600" data-tab="a">标签A</button>
  <button class="px-4 py-2" data-tab="b">标签B</button>
</div>
<div data-panel="a">内容A</div>
<div data-panel="b" class="hidden">内容B</div>
<script>
  const root=document.querySelector('[data-tabs]');
  root.querySelectorAll('[data-tab]').forEach(b=>b.addEventListener('click',()=>{
    const id=b.dataset.tab;
    root.querySelectorAll('[data-tab]').forEach(x=>x.classList.remove('border-blue-600','text-blue-600'));
    b.classList.add('border-blue-600','text-blue-600');
    document.querySelectorAll('[data-panel]').forEach(p=>p.classList.toggle('hidden',p.dataset.panel!==id));
  }));
</script>
```

### 11.6 其它（data-am-widget 8 / data-am-modal / data-am-popover / data-am-collapse）
- `data-am-widget`：amazeui 通用组件容器标记，**删除**即可，按具体组件（面板/列表等）用 Tailwind 重写。
- `modal`：用 layer.js（项目已引 `assets/common/plugins/layer/layer.js`）替代弹窗，无需 amazeui。
- `collapse`：用 `<details>/<summary>` 原生折叠，或上述 `hidden` 切换。

---

## 12. 按模块迁移步骤

1. **建立基线**：本字典 + `tailwind.css` 组件层已就绪。
2. **分模块**：建议顺序 `goods` → `order` → `user` → `setting` → `apps/*`（每个模块一个 PR/批次）。
3. **每页操作**：
   - 替换 `.am-g` → `grid grid-cols-12`；列 `am-u-*` → `col-span-*`。
   - 删 `am-cf`，父容器改 `flex`/`grid`，`am-fl/fr` 改 `float-left/right` 或 `ml-auto`。
   - 表单/按钮/表格/面板按第 5–8 章替换；JS 组件按第 11 章替换。
   - 删除该类后 `npm run build:css` 让 JIT 收录新类。
4. **动态类**：新增 `badge-*` 等动态拼接类 → 同步 `tailwind.config.js` 的 `safelist`。
5. **回归**：登录后台逐页访问，确认无 `am-*` 残留、无样式塌陷、JS 交互正常。

---

## 13. 校验清单（每页迁移后）

- [ ] `grep -oE 'am-[a-zA-Z0-9-]+' <file>` 仅剩预期（`am-icon` 图标字体字符串除外，本项目少用）
- [ ] `am-cf` 已删，父容器已改 flex/grid
- [ ] JS 组件（`ucheck/selected/datepicker/dropdown/tabs`）已替换为原生/Tailwind/轻量库
- [ ] `npm run build:css` 已重跑，新类已编入 `public/assets/common/css/tailwind.css`
- [ ] 浏览器登录访问该页：布局正常、无塌陷、交互可用
- [ ] 未迁移页不受影响（amazeui.min.css 仍在 layout 引入）

---

## 14. 批量迁移脚本与全站进度（2026-10-06 收尾）

**不要手工改类**——`scripts/migrate_amazeui.py` 已覆盖全站用到的全部类，幂等、逐文件报「非图标 am-* 残留」：
```bash
python3 scripts/migrate_amazeui.py $(find app/store/view -name '*.php')
npm run build:css
find runtime/store/temp -type f -delete   # 清 ThinkPHP 模板缓存后再 curl 校验
```
脚本要点（避免重蹈覆辙）：
- 通用栅格 `am-u-{sm,md,lg}-{N}`→`{bp}:w-N/12`（12→`w-full`）、`am-u-{bp}-push-{N}`→`{bp}:ml-[pct%]`（任意值）。
- `am-form-group` **改名为 `form-group`**（不是删除）——保留内联 JS 选择器钩子（如 `icon.php` 的 `$this.parents('.form-group')`）。
- radio 内含 `<?= ?>` 时禁用整标签正则（会吞 `>`）；改为删 `data-am-ucheck` 令牌 + 在 `<input type="radio"` 后插 `class`（见第 9 章警告）。
- 删除 `data-am-selected`/`data-am-widget`/`data-am-datepicker`（含布尔/任意值形态）。
- 新增映射：`am-checkbox-inline`/`am-text-{success,danger,primary}`/`am-padding*`/`am-block`/`am-hide`/`am-avg-sm-N`/`am-divider*`/`am-container`/`am-scrollable-vertical`/`am-disabled`/`am-badge-secondary`（`.badge-secondary` 已加进 `src/tailwind.css`）。

**全站进度**：182 个 store 视图已迁移，非图标 `am-*` 从 ~18000+ 降至 **38**（约 99.8%）。

**已知有意残留（留"替换 JS 组件"阶段，amazeui 未移除前仍可用）**：
1. JS 交互组件类 `am-dropdown*`/`am-tabs*`/`am-nav*`/`am-active`：仅 `user/index`、`apps/dealer/user`、`apps/dealer/setting` 共 3 文件。当前靠 amazeui.min.js 工作。
2. 动态列数 `am-avg-sm-{{var}}`：`wxapp/page/tpl/diy.php`、`wxapp/page/edit.php`。Tailwind JIT 无法静态生成任意列数，只能靠 amazeui 渲染（后续改内联 `grid-template-columns` 或 JS 控制）。

**验证结论**：迁移未引入任何 PHP 500（全局 radio 破坏签名=0；能解析的页均 200）。已现 500 全部与迁移无关——控制器缺配置键（`setting/store`、`market.vip/setting` 的 `kuaidi100`/`return_freight`）、缺参数（`apps.sharp.active_time/add` 缺 `active_id`、`wxapp.page/edit` 缺 `page_id`）、控制器静态方法误调用（`wxapp.page/category`）。大量 404 是路由点记法/URL 形态（如订单列表真实路由 `shop.order/index`，`order/index` 会 404），非迁移问题。

**下一步**：①替换 JS 组件（dropdown/tabs/datepicker/DIY 动态列）②图标 `am-icon-*` 统一换 iconfont ③全迁完后从 layout 撤 `amazeui.min.css/js` + 开 preflight + 删死文件。

---

## 15. 移除 amazeui 收尾（2026-10-06 完成）

**全站非图标 `am-*` 已归零**（=0），amazeui 框架 CSS/JS 已彻底移除。

### 15.1 JS 组件替换（脚本 `scripts/migrate_components.py`）
- **dropdown** → CSS 悬停下拉：`am-dropdown`→`relative inline-block group`、`am-dropdown-toggle` 删、`am-dropdown-content`→`dropdown-menu absolute right-0 z-10 mt-1 hidden w-44 rounded border border-gray-200 bg-white shadow-lg group-hover:block`、`am-dropdown-item`→``。菜单项统一样式抽到 `src/tailwind.css` 的 `.dropdown-menu a`（@apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100），故无需逐个改 `<a>`。涉及 `user/index`、`apps/dealer/user/index`。
- **tabs** → Tailwind + jQuery：`am-tabs`→`js-tabs`、`am-tabs-nav am-nav am-nav-tabs`→`flex flex-wrap border-b border-gray-200 mb-4`、active link=`<a class="...border-blue-500 text-blue-600" href="javascript:;" data-target="#tabX">`、inactive=`border-transparent text-gray-500 hover:text-gray-700`、`am-tabs-bd`→``、`am-tab-panel`→`js-tab-panel`（非激活加 `hidden`）。切换脚本注入 `layouts/layout.php`（绑定 `.js-tabs`，复用已加载 jQuery）。涉及 `user/index`、`apps/dealer/setting/index`。注意 `user/index` 的 `<script id="tpl-grade">` 模板串里另有一处单面板 `am-tab-panel p-0 am-active`，直接去 am-* 变 `<div class="p-0">`。
- **DIY 动态列** `am-avg-sm-{{var}}`（diy.php / edit.php）→ `class="grid"` + 内联 `grid-template-columns: repeat(var, minmax(0,1fr)); gap:1rem;`（Tailwind JIT 无法静态生成任意列数）。

### 15.2 图标解耦（关键发现）
amazeui 的 `am-icon-*` 实际复用 **FontAwesome 码点**（如 `\f1f8`=fa-trash、`\f002`=fa-search），项目本地**已有 FontAwesome 字体**（`public/assets/common/fonts/fontawesome-webfont.*`）。故新建 `public/assets/common/css/amazeui-icons.css`：`@font-face{FontAwesome}` + 10 个 `.am-icon-*:before`（trash/cloud-upload/pencil/plus/search/caret-down/undo/refresh/list/calendar-check-o），类名 `am-icon-*` 保留、零视图改动。layout 撤 `amazeui.min.css` 改引此文件。

> 为何不直改 `iconfont icon-X`：项目 iconfont.cn 集（font_783249）仅含 search/refresh 等 2 个命中，其余 8 个 amazeui 图标在该集**不存在**，盲改名会变空白。若要真改名，需在 iconfont.cn 项目补这 8 个字形。

### 15.3 收尾改动
- `layouts/layout.php`：撤 `amazeui.min.css`（改 `amazeui-icons.css`）、撤 `amazeui.min.js`（改注入 tabs 脚本）。
- `tailwind.config.js`：开启 preflight（删 `preflight:false`，`corePlugins:{}`）。
- 删死文件：`amazeui.min.css`、`amazeui.min.js`、`amazeui.datatables.min.css`（已确认全项目仅 store layout 引用过）。
- 重建：`npm run build:css`（preflight 开启后 tailwind.css ~15.8KB）。

### 15.4 验证与遗留
- 组件页（user/index、dealer/user、dealer/setting）与 shop.order/index、market.coupon/index、index/index 均 **200**，渲染输出非图标 `am-*`=0，`am-icon` 经 amazeui-icons.css 渲染。
- 非迁移问题（与本次无关）：`wxapp.page/edit` 控制器 `think\Model::get()` 静态调用 500（既有 bug）；`wxapp.page/tpl/editor` 404（template 片段不可直接路由）。
- **待办**：①全站视觉走查（preflight 开启后全局重置可能影响 admin 外观，需人工核对）②可选 iconfont 真改名 ③归档脚本/清潜在死文件。

---

## 16. 图标改名 am-icon-* → icon-* + 归档 + 清死文件（2026-10-06 完成）

### 16.1 图标改名（用户第二步）
- 全站视图 **174 处** `am-icon-*` → `icon-*`（脚本 `scripts/archive/rename_icons.py`）。
- **易漏点·菜单图标数据**：`app/store/config/menus.php` 有 6 个菜单图标值是 `am-icon-rmb/film/plane/motorcycle/car/credit-card`（属配置数据，文件扫描会漏，且上一轮删 `amazeui.min.css` 后这 6 个其实已空白）。已一并改名为 `icon-*`。
- 新建 `public/assets/common/css/iconfont-business.css`：用**本地 FontAwesome** 自包含定义全部 **16 个** `.icon-*:before`（10 业务 + 6 菜单），`font-family` 内联在 `:before`，`@font-face FontAwesome` 引用本地 `public/assets/common/fonts/fontawesome-webfont.*`。layout 在远程 iconfont（font_783249 css）**之后**引入，确保这 16 个由本地 FontAwesome 兜底渲染。
- 删除旧 `amazeui-icons.css`（被取代）。
- 验证：`am-icon-*` 在 view 文件 / 非 view 源码 / 渲染输出 **全 =0**；16 个 `icon-*` 渲染；各页 200。

### 16.2 为什么不是「纯 iconfont」
远程 iconfont.cn 集（font_783249）只含 `search`/`refresh` 这 2 个业务图标，其余 14 个（8 业务 + 6 菜单）在该集**不存在**，盲改名会变空白。故用本地 FontAwesome 兜底（与之前 `amazeui-icons.css` 机制一致，只是类名换成了 `icon-*`）。**真正纯 iconfont 的做法**：在 iconfont.cn 项目 font_783249 补这 14 个字形并重发字体，然后删除 `iconfont-business.css` + 本地 FontAwesome 字体即可（届时 `icon-*` 全由远程 iconfont 提供）。

### 16.3 归档与清死文件
- 迁移脚本 `migrate_amazeui.py` / `migrate_components.py` / `rename_icons.py` 移入 `scripts/archive/`；QA 脚本 `scripts/regression/check-pages.mjs` 保留。
- 删除死文件：`amazeui.min.css` / `amazeui.min.js` / `amazeui.datatables.min.css` / `amazeui-icons.css`。`public` 下 amazeui 文件数 = **0**，全项目无 `amazeui.*` / `datatables` 引用残留。
- umeditor 仅留注释（"原生 textarea，不再依赖 umeditor"），无死文件，无需处理。

### 16.4 结论
amazeui 框架已从 store 后台**完全移除**（CSS/JS/字体/死文件），`am-*` 类全站清零；图标以 `icon-*` 命名、由 `iconfont-business.css` 本地兜底，待用户在 iconfont.cn 补字形后可彻底脱离 FontAwesome。

---

## 14. 收尾（全站迁完后）

1. `layout.php` 删除 `<link .../amazeui.min.css>` 与 `<script .../amazeui.min.js>`。
2. `tailwind.config.js` 开启 `corePlugins.preflight: true`（此时无 amazeui 页，preflight 不再冲突）。
3. `rm public/assets/common/css/amazeui.min.css public/assets/common/js/amazeui.min.js`（删死文件）。
4. 全量回归 + 浏览器核对。
