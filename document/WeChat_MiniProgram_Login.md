# 微信小程序登录集成文档

## 1. 概述
专用于微信小程序环境的登录方式，支持获取手机号。

## 2. 流程设计
1.  **小程序端**: 调用 `wx.login()` 获取临时登录凭证 `code`。
2.  **小程序端**: (可选) 调用 `wx.getUserProfile` 或手机号授权按钮，获取 `iv` 和 `encryptedData`。
3.  **请求后端**: POST `/api/wxapp/login`。
4.  **后端验证**:
    *   调用 `auth.code2Session` 换取 `openid` 和 `session_key`。
    *   如果提供了加密数据，使用 `session_key` 解密获取手机号/UnionID。
    *   自动注册/登录，绑定手机号。

## 3. 配置项
配置位于 `sys_config` 或 `app/data/service/ConfigService.php` 中：

| 配置键名 | 说明 |
| :--- | :--- |
| `wxapp_appid` | 小程序 AppID |
| `wxapp_appsecret` | 小程序 AppSecret |

## 4. 接口规范

**接口地址**: `/api/wxapp/login`
**请求方式**: `POST`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| `code` | string | 是 | wx.login 获取的 code |
| `iv` | string | 否 | 加密算法的初始向量 (用于解密手机号) |
| `encryptedData` | string | 否 | 加密数据 (用于解密手机号) |

**响应示例**:
```json
{
    "code": 1,
    "info": "登录成功",
    "data": {
        "token": "eyJ...",
        "openid": "oX...",
        "phone": "138..."
    }
}
```
