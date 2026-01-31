# Google 登录集成文档

## 1. 概述
Google 登录允许用户使用其 Google 账号登录您的应用。

## 2. 流程设计
1.  **客户端 (App/Web)**: 集成 Google Sign-In SDK。
2.  **用户动作**: 点击“使用 Google 登录”。
3.  **客户端**: 获取 `idToken` (Identity Token)。
4.  **客户端**: 解析 `idToken` 获得 `sub` (即 openid)。
5.  **请求后端**: POST `/api/oauth/google`，提交 `openid` 和 `token` (idToken)。
6.  **后端验证**:
    *   验证 `idToken` 签名有效性。
    *   验证 `aud` (Audience) 是否匹配配置的 `Client ID`。
    *   验证通过后，自动注册或登录，返回系统 JWT。

## 3. 配置项
建议在后台系统配置 (`sys_config`) 中添加以下参数：

| 配置键名 | 说明 | 示例值 |
| :--- | :--- | :--- |
| `login_google_client_id` | Google Cloud Console 中的 Client ID | `123456-abcde.apps.googleusercontent.com` |

## 4. 接口规范

**接口地址**: `/api/oauth/google`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | 用户唯一标识 (Google `sub` 字段) |
| `token` | string | 否 | Google `idToken` (用于后端验证) |
| `unionid` | string | 否 | 跨应用标识 (如有) |
| `nickname` | string | 否 | 用户昵称 |
| `headimg` | string | 否 | 用户头像URL |

**响应示例**:
```json
{
    "code": 1,
    "info": "登录成功",
    "data": {
        "token": "eyJ...",
        "token_type": "Bearer"
    }
}
```
