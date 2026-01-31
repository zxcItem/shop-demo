# Apple 登录 (Sign in with Apple) 集成文档

## 1. 概述
Apple 登录是 iOS 应用上架必须支持的登录方式（如果应用支持其他第三方登录）。

## 2. 流程设计
1.  **客户端**: 使用 AuthenticationServices 框架发起请求。
2.  **用户动作**: FaceID/TouchID 验证。
3.  **客户端**: 获取 `identityToken` (JWT) 和 `user` (User ID)。
4.  **请求后端**: POST `/api/oauth/apple`，提交 `openid` 和 `token`。
5.  **后端验证**:
    *   从 `https://appleid.apple.com/auth/keys` 获取 Apple 公钥 (JWK)。
    *   解析 JWT Header 获取 `kid`，匹配对应的公钥。
    *   验证 JWT 签名、过期时间 (`exp`)。
    *   验证 `iss` 是否为 `https://appleid.apple.com`。
    *   验证 `aud` 是否匹配配置的 `Bundle ID`。
    *   验证 `sub` 是否匹配请求的 `openid`。
    *   **注意**: Apple User ID 较长，数据库字段需支持 128 位。

## 3. 配置项

| 配置键名 | 说明 |
| :--- | :--- |
| `login_apple_bundle_id` | App ID (Bundle ID) |
| `login_apple_team_id` | Team ID |
| `login_apple_key_id` | Key ID (用于私钥签名，如果需要获取 refresh_token) |
| `login_apple_p8_file` | P8 私钥文件路径 |

## 4. 接口规范

**接口地址**: `/api/oauth/apple`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `openid` | string | 是 | Apple User Identifier |
| `token` | string | 否 | Identity Token (JWT) |
| `nickname` | string | 否 | 仅首次登录可获取 (客户端需缓存提交) |
| `headimg` | string | 否 | 无 (Apple 不直接提供头像) |

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
