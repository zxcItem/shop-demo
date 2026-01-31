# Google 登录集成文档

## 1. 概述
Google 登录允许用户使用其 Google 账号登录您的应用。系统支持 **ID Token** (前端直接获取凭证) 和 **Authorization Code** (后端换取凭证) 两种模式，以适应不同的业务场景。

## 2. 流程设计与场景选择

### 模式 A: ID Token 模式 (推荐)
**适用场景**: 
*   SPA (单页应用)
*   普通 H5 网页
*   移动端 App (iOS/Android)
*   PC 端网页

**流程**:
1.  **客户端**: 使用 Google SDK 登录，直接获取 JWT 格式的 `id_token`。
2.  **客户端**: 将 `id_token` 发送给后端 `/api/oauth/google`。
3.  **后端**: 校验 `id_token` 的签名、有效期和 `aud` (Client ID)。校验通过即视为登录成功。

**优点**: 简单、快速，后端无需再次请求 Google 服务器 (利用本地公钥验签)，性能高。

### 模式 B: Authorization Code 模式
**适用场景**: 
*   需要获取 Refresh Token 以便长期访问 Google API (如访问用户日历、云盘)。
*   对安全性有极高要求，希望所有 Token 交换都在后端完成。

**流程**:
1.  **客户端**: 请求获取 `code` (授权码)。
2.  **客户端**: 将 `code` 发送给后端 `/api/oauth/google`。
3.  **后端**: 使用 `code` + `client_secret` 向 Google 换取 `access_token` 和 `id_token`。
4.  **后端**: 解析身份信息完成登录。

**注意**: 此模式需要在后端配置 `client_secret`。

## 3. 配置项
支持在后台系统配置 (`sys_config`) 或环境变量 (`.env`) 中设置。优先读取 `sys_config`，若为空则读取 `.env`。

| 配置键名 (sys_config) | 环境变量 (.env) | 说明 | 示例值 |
| :--- | :--- | :--- | :--- |
| `login_google_client_id` | `LOGIN_GOOGLE_CLIENT_ID` | Google Client ID (支持多个，逗号分隔) | `123-abc.apps.googleusercontent.com` |
| `login_google_client_secret` | `LOGIN_GOOGLE_CLIENT_SECRET` | Google Client Secret (可选，仅 Authorization Code 模式需要) | `GOCSPX-xxxxxx` |
| `login_google_redirect_uri` | `LOGIN_GOOGLE_REDIRECT_URI` | 回调地址 (可选，仅 Authorization Code 模式需要) | `https://your.domain/api/callback/google` |

## 4. 接口规范

**接口地址**: `/api/oauth/google`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `token` | string | 选填 | Google `idToken` (模式 A 必填) |
| `code` | string | 选填 | Google Authorization Code (模式 B 必填) |
| `openid` | string | 选填 | Google `sub` 字段 (模式 A 推荐传，模式 B 可自动获取) |
| `redirect_uri` | string | 选填 | 回调地址 (模式 B 必填，需与前端一致) |
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

## 5. 前端集成示例 (Web)

使用 Google Identity Services (GIS) SDK 进行集成。

### 5.1 引入 SDK
在 HTML `<head>` 或 `<body>` 中引入：
```html
<script src="https://accounts.google.com/gsi/client" async defer></script>
```

### 5.2 实现模式 A: ID Token 登录 (推荐)

```javascript
// 1. 初始化
window.onload = function () {
    google.accounts.id.initialize({
        client_id: "YOUR_GOOGLE_CLIENT_ID",
        callback: handleCredentialResponse
    });

    // 2. 渲染登录按钮
    google.accounts.id.renderButton(
        document.getElementById("buttonDiv"),
        { theme: "outline", size: "large" }  // 定制样式
    );
    
    // 或者显示一键登录提示 (One Tap)
    google.accounts.id.prompt(); 
};

// 3. 处理回调
function handleCredentialResponse(response) {
    console.log("Encoded JWT ID token: " + response.credential);
    
    // 发送给后端
    fetch('/api/oauth/google', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: response.credential  // 直接将 ID Token 传给后端
        })
    })
    .then(res => res.json())
    .then(data => {
        if(data.code === 1){
            alert("登录成功");
            // 保存系统 Token
            localStorage.setItem('token', data.data.token);
        } else {
            alert("登录失败: " + data.info);
        }
    });
}
```

### 5.3 实现模式 B: Authorization Code 登录

```javascript
// 1. 初始化 Code Client
const client = google.accounts.oauth2.initCodeClient({
    client_id: "YOUR_GOOGLE_CLIENT_ID",
    scope: "openid profile email",
    ux_mode: "popup", // 或 'redirect'
    callback: (response) => {
        if (response.code) {
            // 发送 Code 给后端
            fetch('/api/oauth/google', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    code: response.code,
                    // 如果是 popup 模式，后端 redirect_uri 可不传或传 postmessage，视具体库而定
                    // 这里通常不需要 redirect_uri，除非后端显式校验
                })
            })
            .then(res => res.json())
            .then(data => {
                // 处理登录成功
            });
        }
    },
});

// 2. 绑定按钮点击事件
function signIn() {
    client.requestCode();
}
```

## 6. App 端集成 (Flutter/Native)

App 端建议使用官方或社区成熟的 SDK 获取 `idToken`，然后调用模式 A 接口。

*   **Flutter**: 使用 `google_sign_in` 插件。
    ```dart
    final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();
    final GoogleSignInAuthentication googleAuth = await googleUser!.authentication;
    // 获取 idToken
    final String? idToken = googleAuth.idToken;
    
    // 调用后端接口
    // POST /api/oauth/google { "token": idToken }
    ```
*   **Android/iOS Native**: 使用 Google Sign-In SDK 获取 `requestIdToken`。
