# 微信 App 登录集成文档

## 1. 概述
适用于移动应用 (iOS/Android) 的微信授权登录。

## 2. 流程设计
1.  **App端**: 集成微信 OpenSDK。
2.  **用户动作**: 拉起微信授权。
3.  **App端**: 获取 `code`。
4.  **请求后端**: POST `/api/oauth/wechat` (需自行实现或复用 OAuth 逻辑)。
    *   或者 App 端直接换取 `access_token` 和 `openid` 后传给后端 (不推荐，建议后端换取以保密 Secret)。
5.  **后端处理**:
    *   使用 `code` 换取 `access_token` 和 `openid`。
    *   获取 `unionid` (用于关联小程序/公众号账号)。
    *   登录/注册。

## 3. 配置项
支持在后台系统配置 (`sys_config`) 或环境变量 (`.env`) 中设置。优先读取 `sys_config`，若为空则读取 `.env`。

| 配置键名 (sys_config) | 环境变量 (.env) | 说明 |
| :--- | :--- | :--- |
| `login_wechat_appid` | `WECHAT_APPID` | 开放平台 AppID |
| `login_wechat_secret` | `WECHAT_APPSECRET` | 开放平台 AppSecret |

## 4. 接口规范

**接口地址**: `/api/oauth/wechat` (建议新增) 或 `/api/login/in` (需改造)
**当前推荐**: 复用 OAuth 结构，新增 `Wechat.php` 驱动。

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | 微信 OpenID |
| `token` | string | 否 | Access Token (如果是客户端换取模式) |
| `unionid` | string | 否 | 关键字段，用于跨应用识别 |

**响应示例**:
```json
{
    "code": 1,
    "info": "登录成功",
    "data": {
        "token": "eyJ..."
    }
}
```
