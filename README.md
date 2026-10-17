# 1146 多商户商城系统

基于 **ThinkPHP 8.1** 的多商户商城（SaaS 多租户）+ **微信小程序** 前端。

## 仓库结构

```
├── php_tp8/        # 后端：ThinkPHP 8.1（商家后台 + API）
└── 1146weixin/     # 前端：微信小程序（原生开发）
```

## 功能模块

### 后端（php_tp8）

| 模块 | 说明 |
|------|------|
| `app/store` | 商家后台（管理端），路径 `/store/` |
| `app/api` | 小程序/H5 接口端 |
| `app/common` | 公共模型与服务层 |
| `app/h5` / `app/index` | H5 / 门户页面 |

业务应用：商城商品与订单、拼团（sharing）、砍价（bargain）、秒杀（sharp）、分销代理商（dealer / regionagent）、直播间（live）、营销（market）、代币充值（coin）、VIP 会员、优惠券、积分等。

支付支持：微信支付（JSAPI / H5-MWEB，v2 + v3）、支付宝、Stripe。

### 小程序（1146weixin）

原生微信小程序，共 72 个页面：首页、分类、商品详情、购物车、订单流程、会员中心、拼团/砍价/秒杀活动页、分销中心等。

## 环境要求

- PHP >= 8.0（生产环境 PHP 8.1）
- MySQL 5.7+（表前缀 `mybaixin_`）
- 微信开发者工具（小程序端）

## 快速开始

### 后端

```bash
cd php_tp8
composer install            # 安装依赖（vendor 已被 git 忽略）
cp .example.env .env        # 配置数据库等信息
php think run               # 本地开发服务器 http://localhost:8000
```

数据库结构见 `php_tp8/database/schema.sql`（含基础种子数据，其中第三方 API 密钥已脱敏为 `xxx`，部署时需在商家后台重新填入）。

### 小程序

1. 微信开发者工具导入 `1146weixin/` 目录
2. 修改 `siteinfo.js` 中的接口域名
3. AppID 在 `project.config.json` 中配置

## 部署注意

- 服务器部署需配置 nginx 指向 `php_tp8/public`，PHP-FPM 传递 `X-Forwarded-For`（H5 支付回跳依赖真实 IP）
- `.env`、`vendor/`、`runtime/`、`public/uploads/` 均不入库（见 `.gitignore`）
- 上线前在后台「支付配置」填入真实微信/支付宝/Stripe 密钥

## 目录约定

- 后台页面模板：`php_tp8/app/store/view/`
- 路由：`php_tp8/route/`
- 数据库迁移/结构：`php_tp8/database/`
