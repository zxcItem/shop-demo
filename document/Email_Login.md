# 邮箱验证码登录集成文档

## 1. 概述
邮箱登录允许用户使用邮箱接收验证码的方式进行登录。该方式无需密码，既安全又便捷。

## 2. 流程设计
1.  **客户端**: 用户输入邮箱地址。
2.  **客户端**: 请求发送验证码接口 `/api/oauth/email/send`。
3.  **服务端**: 校验邮箱，生成随机验证码，并通过 SMTP 服务发送邮件。
4.  **客户端**: 用户收到邮件后，输入验证码。
5.  **客户端**: 请求登录接口 `/api/oauth/email`，提交邮箱和验证码。
6.  **服务端**: 校验验证码，通过后自动注册或登录，返回 Token。

## 3. 配置项
请在后台系统配置 (`sys_config`) 或环境变量 (`.env`) 中设置 SMTP 邮件服务信息。优先读取 `sys_config`。

| 配置键名 (sys_config) | 环境变量 (.env) | 说明 | 示例值 |
| :--- | :--- | :--- | :--- |
| `email_smtp_host` | `EMAIL_SMTP_HOST` | SMTP 服务器地址 | `smtp.qq.com` |
| `email_smtp_port` | `EMAIL_SMTP_PORT` | SMTP 端口 | `465` (SSL) 或 `587` (TLS) |
| `email_smtp_user` | `EMAIL_SMTP_USER` | SMTP 账号/邮箱 | `yourname@qq.com` |
| `email_smtp_pass` | `EMAIL_SMTP_PASS` | SMTP 授权码/密码 | `abcdefghijk` |
| `email_smtp_secure` | `EMAIL_SMTP_SECURE` | 加密方式 | `ssl` 或 `tls` |
| `email_from_addr` | `EMAIL_FROM_ADDR` | 发件人邮箱 (可选) | `yourname@qq.com` |
| `email_from_name` | `EMAIL_FROM_NAME` | 发件人名称 (可选) | `My App` |

## 4. 接口规范

### 4.1 发送验证码

**接口地址**: `/api/oauth/email/send`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `email` | string | 是 | 用户邮箱地址 |

**响应示例**:
```json
{
    "code": 1,
    "info": "验证码发送成功",
    "data": []
}
```

### 4.2 邮箱登录

**接口地址**: `/api/oauth/email`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `email` | string | 是 | 用户邮箱地址 |
| `code` | string | 是 | 收到的验证码 |

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

## 5. 前端调用示例 (JavaScript)

```javascript
// 1. 发送验证码
function sendCode(email) {
    fetch('/api/oauth/email/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    })
    .then(res => res.json())
    .then(data => {
        if(data.code === 1) {
            alert("验证码已发送");
        } else {
            alert(data.info);
        }
    });
}

// 2. 登录
function login(email, code) {
    fetch('/api/oauth/email', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, code: code })
    })
    .then(res => res.json())
    .then(data => {
        if(data.code === 1) {
            alert("登录成功");
            localStorage.setItem('token', data.data.token);
        } else {
            alert(data.info);
        }
    });
}
```
