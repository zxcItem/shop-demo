# Facebook 登录集成文档

## 1. 概述
Facebook 登录允许用户使用其 Facebook 账号快速登录。

## 2. 流程设计
1.  **客户端**: 集成 Facebook SDK。
2.  **用户动作**: 授权登录。
3.  **客户端**: 获取 `accessToken` 和 `userID` (即 openid)。
4.  **请求后端**: POST `/api/oauth/facebook`，提交 `openid` 和 `token` (accessToken)。
5.  **后端验证**:
    *   后端使用 App ID 和 App Secret 生成 `app_access_token`。
    *   调用 Graph API `/debug_token` 端点验证 User Access Token 的有效性。
    *   验证 `is_valid` 是否为 true。
    *   验证 `app_id` 是否匹配。
    *   验证 `user_id` 是否匹配请求的 `openid`。
    *   验证通过后，自动注册或登录。

## 3. 配置项
支持在后台系统配置 (`sys_config`) 或环境变量 (`.env`) 中设置。优先读取 `sys_config`，若为空则读取 `.env`。

| 配置键名 (sys_config) | 环境变量 (.env) | 说明 |
| :--- | :--- | :--- |
| `login_facebook_app_id` | `LOGIN_FACEBOOK_APP_ID` | Meta Developers App ID |
| `login_facebook_app_secret` | `LOGIN_FACEBOOK_APP_SECRET` | App Secret (用于后端生成 app_access_token) |

## 4. 接口规范

**接口地址**: `/api/oauth/facebook`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | Facebook User ID |
| `token` | string | 否 | User Access Token |
| `nickname` | string | 否 | 用户昵称 |
| `headimg` | string | 否 | 用户头像URL |

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
