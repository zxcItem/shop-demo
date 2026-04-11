<?php

namespace app\data\service\account\oauth;

/**
 * Facebook登录驱动
 * @class FacebookService
 * @package app\data\service\oauth
 */
class FacebookService extends Contract
{
    /**
     * 使用 Authorization Code 换取 Token
     * @param string $code
     * @param string|null $redirectUri
     * @return array
     * @throws \Exception
     */
    public function exchangeCode(string $code, ?string $redirectUri = null): array
    {
        $appId = sysconf('login_facebook_app_id') ?: env('LOGIN_FACEBOOK_APP_ID');
        $appSecret = sysconf('login_facebook_app_secret') ?: env('LOGIN_FACEBOOK_APP_SECRET');
        $defaultRedirectUri = sysconf('login_facebook_redirect_uri') ?: env('LOGIN_FACEBOOK_REDIRECT_URI');
        
        $redirectUri = $redirectUri ?: $defaultRedirectUri;

        if (empty($appId) || empty($appSecret)) {
             throw new \Exception('Facebook App ID 或 Secret 未配置');
        }

        $url = "https://graph.facebook.com/v19.0/oauth/access_token";
        $data = [
            'client_id'     => $appId,
            'client_secret' => $appSecret,
            'redirect_uri'  => $redirectUri,
            'code'          => $code,
        ];

        $context = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET', // Facebook access_token supports GET
                'content' => http_build_query($data),
                'timeout' => 15
            ]
        ]);
        
        // Use full URL for GET
        $requestUrl = $url . '?' . http_build_query($data);
        $response = file_get_contents($requestUrl);

        if ($response === false) {
             throw new \Exception('连接 Facebook 授权服务失败');
        }

        $result = json_decode($response, true);
        if (isset($result['error'])) {
             throw new \Exception($result['error']['message'] ?? 'Facebook Token Exchange Error');
        }

        return $result;
    }

    public function verify(string $openid, string $token): array
    {
        if (empty($token)) {
            return ['openid' => $openid];
        }

        // 获取配置
        $appId = sysconf('login_facebook_app_id') ?: env('LOGIN_FACEBOOK_APP_ID');
        $appSecret = sysconf('login_facebook_app_secret') ?: env('LOGIN_FACEBOOK_APP_SECRET');

        try {
            // 生成 app_access_token (无需用户授权，用于后端查询)
            // 格式: APPID|APPSECRET
            $appAccessToken = "{$appId}|{$appSecret}";

            // 验证 User Access Token
            // 文档: https://developers.facebook.com/docs/graph-api/reference/v19.0/debug_token
            $url = "https://graph.facebook.com/debug_token?input_token={$token}&access_token={$appAccessToken}";
            
            $context = stream_context_create([
                'http' => ['timeout' => 10, 'ignore_errors' => true]
            ]);
            
            $response = file_get_contents($url, false, $context);
            if (!$response) throw new \Exception('连接 Facebook 验证服务失败');

            $payload = json_decode($response, true);
            
            // 检查 API 错误
            if (isset($payload['error'])) {
                throw new \Exception($payload['error']['message'] ?? 'Facebook API Error');
            }

            $data = $payload['data'] ?? [];
            
            // 验证 Token 有效性
            if (empty($data['is_valid'])) {
                throw new \Exception('Facebook Token 无效');
            }

            // 验证 App ID
            if (!empty($appId) && isset($data['app_id']) && $data['app_id'] != $appId) {
                throw new \Exception('Facebook App ID 不匹配');
            }

            // 验证 User ID
            if (isset($data['user_id']) && $data['user_id'] != $openid) {
                throw new \Exception('Facebook User ID 不匹配');
            }
            
            // 获取用户信息 (可选，补充昵称头像)
            // https://graph.facebook.com/me?fields=id,name,picture&access_token=TOKEN
            $infoUrl = "https://graph.facebook.com/me?fields=id,name,picture.type(large)&access_token={$token}";
            $infoResponse = file_get_contents($infoUrl, false, $context);
            $userInfo = $infoResponse ? json_decode($infoResponse, true) : [];

            return [
                'openid'   => $openid,
                'nickname' => $userInfo['name'] ?? '',
                'headimg'  => $userInfo['picture']['data']['url'] ?? '',
                'unionid'  => '', // Facebook 通常不返回 unionid，除非 Business 账号
            ];

        } catch (\Exception $e) {
            throw new \Exception("Facebook 登录验证失败: " . $e->getMessage());
        }
    }
}
