# VIP（会员）模块迁移完成报告

> 目标：将 `php_my`（TP5 源）的 VIP 会员模块迁移到 `php_tp8`（TP8 升级目标），补齐 10 个增长/垂直模块之一的「会员」。
> 日期：2026-09-06

## 一、新增 / 修改文件

### 新增
| 文件 | 说明 |
|------|------|
| `app/common/model/VipPlan.php` | VIP 套餐模型（common 层，ORM） |
| `app/common/model/VipOrder.php` | VIP 开通订单模型（common 层，ORM） |
| `app/api/model/Vip.php` | VIP 业务逻辑（轻量 `Db` 风格，不复用表，故不继承 `think\Model`） |
| `app/api/controller/user/Vip.php` | 会员中心 / 开通 / 记录三接口 |
| `app/api/service/VipPaySuccess.php` | VIP 订单支付成功收口（仿 `RechargePaySuccess`） |

### 修改
| 文件 | 改动 |
|------|------|
| `app/api/route/app.php` | Token 组内追加 `user.vip/index`、`user.vip/buy`、`user.vip/order` |
| `app/api/controller/Notify.php` | 微信回调挂接 `vip_order` 开通分支 |
| `app/api/controller/NotifyAlipay.php` | 支付宝回调挂接 `vip_order` 开通分支 |
| `app/api/controller/NotifyStripe.php` | Stripe 回调挂接 `vip_order` 开通分支 |
| `mybaixin_setting` | 插入默认 `vip` 配置（会员日/双倍积分/运费券占位） |

## 二、接口与验证结果

| 接口 | 方法 | 验证 |
|------|------|------|
| 会员中心 | `GET user.vip/index` | ✅ 返回 userInfo / plans(3个套餐，已隐藏内部字段) / vip状态 / setting |
| 开通/续费 | `POST user.vip/buy` | ✅ 余额支付：扣款+流水( scene=50 )+开通+订单落库 全通过；第三方支付正确调起 `Payment::getParams` |
| 开通记录 | `GET user.vip/order` | ✅ 返回该用户 vip_order 列表 |
| 支付回调开通 | `VipPaySuccess::paySuccess` | ✅ 待支付订单→会员开通、订单置已支付、幂等安全 |

**余额支付实测**（用户 10011，余额 100 → 80.10）：
- `user.balance` 扣 19.90，写 `user_balance_log`（scene=50, money=-19.90, describe=开通VIP会员(月度会员)）
- `user.is_vip=1`、`vip_expire_time` 正确累加
- `vip_order` 生成 `pay_status=20, pay_type=10`
- 测试数据已清理还原

**回调实测**：模拟待支付 vip_order → `paySuccess` 返回 true，用户开通、订单置 20，二次调用幂等返回 true。

## 三、TP5 → TP8 适配要点（关键坑）

1. **`Vip` 模型不能继承 `think\Model`**：未设 `$name` 时 think-orm 会按类名探测 `mybaixin_vip` 表导致 500。该模型只做业务逻辑容器（读写经 `Db` 门面或其它模型），故改纯类。
2. **`VipPlan::detail` 静态 `get()` 已废弃**：TP8 think-orm v4 中 `get()` 为非静态，改用静态代理 `static::find($planId)`。
3. **`VipOrder::orderNo` 的 `implode(NULL, …)` 在 PHP 8.5 已废弃**：被 thinkphp 错误处理器转异常，导致事务回滚；改为 `implode('', …)`。
4. **`vip_order` 表无 `pay_price` 列**（与 php_my 结构差异）：`buy` 的 `save()` 按表结构过滤会静默忽略，已从 `save` 移除该字段；第三方支付传给 `Payment` 的金额改用独立 `$order['pay_price']`，不受影响。
5. **支付服务接口不同**：php_my 的 `PaymentService::wechat/alipay(OrderType::VIP)` 已重构为 php_tp8 的 `Payment::getParams($payType,$order,$user)`，无需 `OrderType` 枚举。

## 四、已知缺口 / 说明

- **生产支付配置**：微信/支付宝返回「商家收款功能被限制」是商户沙箱/账户配置问题（升级报告已列明），非代码缺陷；配置就绪后第三方支付链路即通。
- **运费券赠送**：php_tp8 的 `coupon` 表无 `is_receive` 字段，赠送逻辑改为「券存在即赠送 + 重复防护」，与 php_my 语义略有差异但不影响开通。
- **会员日双倍积分**：`getPointsRatio` 已迁移，实际积分加权需结算链路调用，本次仅保证接口与字段正确。
