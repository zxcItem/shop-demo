# 第三方登录集成通用文档

## 1. 概述
本系统提供统一的第三方登录接口，支持 Apple, Facebook, Google, QQ, TikTok 以及 邮箱验证码登录。
所有登录渠道均支持 **Token 验证模式** (适用于 App/SDK) 和 **Authorization Code 模式** (适用于 Web/H5) (邮箱登录除外)。

## 2. 接口地址
统一接口路径: `/api/oauth/{type}`
其中 `{type}` 为渠道标识，支持:
*   `google`
*   `apple`
*   `facebook`
*   `qq`
*   `tiktok`
*   `email`

请求方式: `POST`

## 3. 通用参数说明

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `code` | string | 选填 | 授权码 (Authorization Code 模式必填) |
| `token` | string | 选填 | 授权凭证/Access Token/ID Token (Token 验证模式必填) |
| `redirect_uri` | string | 选填 | 回调地址 (Code 模式通常需要，需与前端跳转时一致) |
| `openid` | string | 选填 | 用户唯一标识 (Token 模式建议传递，Code 模式可自动获取) |
| `nickname` | string | 选填 | 用户昵称 (用于首次注册时保存) |
| `headimg` | string | 选填 | 用户头像 (用于首次注册时保存) |
| `unionid` | string | 选填 | 跨应用唯一标识 |

---

## 4. 登录模式详解

### 模式 A: Token 验证模式 (SDK 集成)
**适用场景**: iOS/Android 原生 App, 或集成了官方 SDK 的 H5 页面。
**流程**:
1.  客户端调用 SDK 登录 (如 `GoogleSignIn`, `Login with Facebook`, `Sign in with Apple`)。
2.  客户端获取 `access_token` 或 `id_token`。
3.  客户端将 Token 提交给后端接口。
4.  后端调用厂商 API 验证 Token 有效性并获取用户信息。

**各渠道 Token 说明**:
*   **Google**: 提交 `id_token` (JWT 格式)。
*   **Apple**: 提交 `identityToken` (JWT 格式)。
*   **Facebook**: 提交 `access_token`。
*   **QQ**: 提交 `access_token` (SDK 获取的)。
*   **TikTok**: 提交 `access_token`。

### 模式 B: Authorization Code 模式 (Web 集成)
**适用场景**: PC 网站, 普通 H5, 或需要后端获取 Refresh Token 的场景。
**流程**:
1.  前端引导用户跳转至厂商授权页面 (携带 `client_id`, `redirect_uri`, `response_type=code` 等)。
2.  用户授权后，厂商重定向回 `redirect_uri` 并附带 `code` 参数。
3.  前端将 `code` 提交给后端接口。
4.  后端使用 `code` + `client_secret` 换取 Access Token。
5.  后端获取用户信息并完成登录。

**注意**: 此模式必须在后端配置 `client_secret`。

---

## 5. 渠道特殊说明

### 5.1 邮箱登录 (Email)
邮箱登录与其他渠道略有不同，分为两步：

**第一步: 发送验证码**
*   接口: `/api/oauth/email/send` (实际路由可能为 `/api/oauth/email` 调用 `send` 方法)
*   参数: `email` (邮箱地址)
*   说明: 系统将发送 6 位数字验证码到指定邮箱。

**第二步: 验证码登录**
*   接口: `/api/oauth/email`
*   参数: 
    *   `email`: 邮箱地址
    *   `code`: 收到的验证码
*   说明: 验证通过即登录成功。

### 5.2 Apple 登录
*   **Token 模式**: 验证 `identityToken` 时，后端会自动获取 Apple 公钥进行 JWT 验签。
*   **Code 模式**: 换取 Token 时，Apple 要求 `client_secret` 必须是使用开发者私钥签名的 JWT。请确保配置项 `login_apple_client_secret` 中填入的是生成的 JWT 字符串，或者是通过其他方式生成的有效 Secret。

### 5.3 TikTok 登录
TikTok v2 API 区分 `client_key` (Client ID) 和 `client_secret`。验证 Token 时通常需要调用 `/v2/user/info/` 接口。

---

## 6. 系统配置项 (sys_config / .env)

请在系统后台或 `.env` 文件中配置对应参数。

**Google**:
*   `login_google_client_id`
*   `login_google_client_secret`
*   `login_google_redirect_uri`

**Facebook**:
*   `login_facebook_app_id`
*   `login_facebook_app_secret`
*   `login_facebook_redirect_uri`

**Apple**:
*   `login_apple_client_id` (Bundle ID 或 Service ID)
*   `login_apple_client_secret` (JWT)
*   `login_apple_redirect_uri`

**QQ**:
*   `login_qq_appid`
*   `login_qq_appkey`
*   `login_qq_redirect_uri`

**TikTok**:
*   `login_tiktok_client_key`
*   `login_tiktok_client_secret`
*   `login_tiktok_redirect_uri`

**邮箱 (Email)**:
需要配置 SMTP 发信服务:
*   `email_smtp_host`
*   `email_smtp_port`
*   `email_smtp_user`
*   `email_smtp_pass`
*   `email_smtp_secure`
