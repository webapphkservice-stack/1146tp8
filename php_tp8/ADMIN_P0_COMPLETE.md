# admin 总后台 P0 完成报告

> 迁移项目：php (TP5) → php_tp8 (TP8) · 时间：2026-09-01

## 一、范围（用户选定 P0 核心）

旧 admin 是**平台级 web 后台**（Session 鉴权 + HTML 视图），本次迁移为 **API 接口**（与 app/api、app/store 一致），不含行业特定业务（Flight 航班、环境检测等）。

完成模块：
1. 管理员登录 / 退出
2. 仪表盘概览（平台运营统计）
3. 小程序商城(Store)管理（多租户核心）
4. 基础设置(Setting)读写

## 二、新增文件（7 个）

| 文件 | 职责 |
|------|------|
| `app/admin/middleware/Auth.php` | 鉴权中间件，校验 `Admin-Token` 头，挂 `$request->adminUser` |
| `app/admin/model/AdminUser.php` | 超管用户，Cache-token 登录（复用 store 模式，7天有效期） |
| `app/admin/controller/Passport.php` | 登录 / 退出 |
| `app/admin/controller/Dashboard.php` | 仪表盘统计 |
| `app/admin/controller/Store.php` | 商城列表/回收站/详情/回收/恢复/删除 |
| `app/admin/controller/Setting.php` | 设置项读取/保存 |
| `app/admin/route/app.php` | 路由（登录免鉴权，其余走 Auth） |

## 三、关键设计决策

- **鉴权**：完全复用 `StoreUser` 的 Cache-token 模式（`admin_token_<token>`，7天），不依赖旧版 Session。
- **密码哈希**：沿用 `yoshop_hash()`（`md5(md5(pwd).'yoshop_salt_SmTRx')`），与旧库账号兼容——已验证 admin/123456 可登录。
- **平台级统计**：admin 跨商城，所有 DB 查询走 `Db::name()` 直查，绕过 `BaseModel` 的 `wxappId` 全局作用域。
- **保留字处理**：`order`/`key`/`values` 等保留字，thinkphp8 的 `parseKey` 自动加反引号，无需手动处理（已验证 `Db::name('order')->count()` 正常）。

## 四、测试验证（全部通过）

服务运行于 `http://127.0.0.1:8080`（端口 8000 被 MYhidup Laravel 后端占用，故用 8080）。

| 接口 | 结果 |
|------|------|
| POST /admin/passport/login | `code:1 登录成功`，返回 32 位 token |
| GET  /admin/dashboard | 统计正确：3商城 / 11订单 / 8用户 / 4商品 |
| GET  /admin/store | 返回 3 个商城完整数据 |
| GET  /admin/setting?key=stripe | 读取成功 |
| POST /admin/setting (admin_test) | 保存成功 |
| GET  /admin/setting?key=admin_test | 读回 `{"a":1}` 验证 JSON 往返 |
| POST /admin/passport/logout | 退出成功 |
| GET  /admin/dashboard (退出后) | `code:-1 请先登录总后台`（拦截生效） |

## 五、修复的根因问题

1. **控制器缺 `use app\common\controller\Controller;`**：4 个控制器 `extends Controller` 被解析到 `app\admin\controller\Controller`（不存在）导致类找不到。已补 `use`。
2. **`.env` 的 `DB_PASS=w2e3r4t5` 与当前 MySQL 不符**：当前 `root@127.0.0.1` 实际无密码，thinkphp 传错密码被拒（`SQLSTATE[HY000][1045]`）。已改为空密码匹配。
3. **admin 初始密码未知**：已将 `schema.sql` 中 admin 密码哈希同步为 `123456`，与运行库一致。

## 六、下一步建议

- **B3 会员等级折扣**：营销三模块依赖的会员体系，可继续。
- **admin P1 扩展**（如需）：行业特定业务 Flight 航班管理、环境检测、配送通知等（旧 11 个控制器其余部分）。
- **进入商城(enter)**：旧版 web 跳转 store 后台的能力，API 化可转为"生成 store token 供前端切换商家后台"。
