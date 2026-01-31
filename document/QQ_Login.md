# QQ 登录集成文档

## 1. 概述
QQ 互联登录，适用于国内用户群体。

## 2. 流程设计
1.  **客户端**: 集成 QQ 互联 SDK。
2.  **用户动作**: 唤起 QQ 应用授权。
3.  **客户端**: 获取 `accessToken` 和 `openid`。
4.  **请求后端**: POST `/api/oauth/qq`。
5.  **后端验证**:
    *   调用 `https://graph.qq.com/oauth2.0/me` 验证 Access Token 并获取 OpenID。
    *   验证返回的 OpenID 是否与请求参数一致。
    *   (可选) 使用 App ID 和 Token 调用 `get_user_info` 获取昵称和头像。
    *   (可选) 如有 UnionID 权限，同时记录 UnionID。

## 3. 配置项

| 配置键名 | 说明 |
| :--- | :--- |
| `login_qq_appid` | QQ 互联 App ID |
| `login_qq_appkey` | QQ 互联 App Key |

## 4. 接口规范

**接口地址**: `/api/oauth/qq`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | QQ OpenID |
| `token` | string | 否 | Access Token |
| `unionid` | string | 否 | QQ UnionID (需申请权限) |
| `nickname` | string | 否 | 昵称 |
| `headimg` | string | 否 | 头像 |

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
