# 抖音 (TikTok) 登录集成文档

## 1. 概述
支持抖音（国内）或 TikTok（国际）账号登录。

## 2. 流程设计
1.  **客户端**: 集成抖音/TikTok SDK。
2.  **用户动作**: 授权登录。
3.  **客户端**: 获取 `code` (授权码) 或 `access_token`。
4.  **请求后端**: POST `/api/oauth/tiktok`。
5.  **后端验证**:
    *   调用 TikTok V2 API `https://open.tiktokapis.com/v2/user/info/`。
    *   验证 Access Token 是否有效。
    *   获取并验证 `open_id` 是否一致。
    *   获取用户信息（头像、昵称、UnionID）。

## 3. 配置项
支持在后台系统配置 (`sys_config`) 或环境变量 (`.env`) 中设置。优先读取 `sys_config`，若为空则读取 `.env`。

| 配置键名 (sys_config) | 环境变量 (.env) | 说明 |
| :--- | :--- | :--- |
| `login_tiktok_client_key` | `LOGIN_TIKTOK_CLIENT_KEY` | Client Key (App Key) |
| `login_tiktok_client_secret` | `LOGIN_TIKTOK_CLIENT_SECRET` | Client Secret |

## 4. 接口规范

**接口地址**: `/api/oauth/tiktok`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | OpenID |
| `token` | string | 否 | Access Token |
| `unionid` | string | 否 | UnionID |

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
