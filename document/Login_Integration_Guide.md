# 第三方登录集成指南

本指南汇总了系统支持的所有第三方登录方式、配置方法及集成文档索引。

## 1. 支持的登录方式

系统目前支持以下登录方式：

*   **Google 登录**: 国际通用，支持 Web/App。
*   **Facebook 登录**: 国际通用。
*   **Apple 登录**: iOS 应用强制要求。
*   **QQ 登录**: 国内通用。
*   **TikTok (抖音) 登录**: 支持国内抖音或国际 TikTok。
*   **微信小程序登录**: 专用于微信小程序环境。
*   **微信 App 登录**: 适用于 iOS/Android App。

## 2. 配置管理

系统采用双重配置机制，优先级如下：

1.  **数据库配置 (`sys_config`)**: 优先级最高。可在后台管理界面修改。
2.  **环境变量 (`.env`)**: 优先级次之。适用于不想修改数据库或需要快速部署的场景。

建议使用 `.env` 文件管理敏感信息（如 Client Secret），而将非敏感信息（如 App ID）或需经常变更的配置放在数据库中。

### .env 配置模板

请将以下内容添加到项目根目录的 `.env` 文件中：

```ini
# ==============================
# 第三方登录配置
# ==============================

# --- Google 登录 ---
# 支持多个 Client ID，用逗号分隔 (例如: web_id,app_id)
LOGIN_GOOGLE_CLIENT_ID=your_google_client_id
# 仅 Authorization Code 模式需要，当前 ID Token 模式可选
LOGIN_GOOGLE_CLIENT_SECRET=your_google_client_secret
LOGIN_GOOGLE_REDIRECT_URI=your_redirect_uri

# --- Facebook 登录 ---
LOGIN_FACEBOOK_APP_ID=your_facebook_app_id
LOGIN_FACEBOOK_APP_SECRET=your_facebook_app_secret

# --- Apple 登录 ---
LOGIN_APPLE_BUNDLE_ID=com.your.app.bundle.id
LOGIN_APPLE_TEAM_ID=your_team_id
# 仅需获取 refresh_token 时需要
LOGIN_APPLE_KEY_ID=your_key_id
LOGIN_APPLE_P8_FILE=/path/to/AuthKey_xxxx.p8

# --- QQ 登录 ---
LOGIN_QQ_APPID=your_qq_appid
LOGIN_QQ_APPKEY=your_qq_appkey

# --- TikTok 登录 ---
LOGIN_TIKTOK_CLIENT_KEY=your_client_key
LOGIN_TIKTOK_CLIENT_SECRET=your_client_secret

# --- 微信小程序 ---
WECHAT_MINI_APPID=your_mini_appid
WECHAT_MINI_APPSECRET=your_mini_secret

# --- 微信服务号/开放平台 ---
WECHAT_APPID=your_wechat_appid
WECHAT_APPSECRET=your_wechat_secret
```

## 3. 集成文档索引

详细的流程设计、接口参数及注意事项，请参考以下独立文档：

*   [Google 登录集成文档](./Google_Login.md)
*   [Facebook 登录集成文档](./Facebook_Login.md)
*   [Apple 登录集成文档](./Apple_Login.md)
*   [QQ 登录集成文档](./QQ_Login.md)
*   [TikTok 登录集成文档](./TikTok_Login.md)
*   [微信小程序登录集成文档](./WeChat_MiniProgram_Login.md)
*   [微信 App 登录集成文档](./WeChat_App_Login.md)

## 4. 接口通用规范

所有第三方登录接口均遵循统一的响应格式：

**成功响应**:
```json
{
    "code": 1,
    "info": "登录成功",
    "data": {
        "token": "eyJhbGciOiJIUzI1NiIs...",  // 系统 JWT Token
        "token_type": "Bearer",
        "user_info": {                        // (可选) 返回部分用户信息
            "id": 1001,
            "nickname": "User Name",
            "headimg": "https://..."
        }
    }
}
```

**失败响应**:
```json
{
    "code": 0,
    "info": "Google Client ID 不匹配",  // 具体错误信息
    "data": []
}
```
