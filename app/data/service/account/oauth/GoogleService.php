<?php

namespace app\data\service\account\oauth;

/**
 * 谷歌登录驱动
 * @class GoogleService
 * @package app\data\service\oauth
 */
class GoogleService extends Contract
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
        $clientId = sysconf('login_google_client_id') ?: env('LOGIN_GOOGLE_CLIENT_ID');
        $clientSecret = sysconf('login_google_client_secret') ?: env('LOGIN_GOOGLE_CLIENT_SECRET');
        $defaultRedirectUri = sysconf('login_google_redirect_uri') ?: env('LOGIN_GOOGLE_REDIRECT_URI');

        $redirectUri = $redirectUri ?: $defaultRedirectUri;

        if (empty($clientId) || empty($clientSecret)) {
             throw new \Exception('Google Client ID 或 Secret 未配置');
        }

        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'code'          => $code,
            'client_id'     => explode(',', $clientId)[0], // 取第一个作为主要 ID
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ];

        $context = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 15
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
             throw new \Exception('连接 Google 授权服务失败');
        }

        $result = json_decode($response, true);
        if (isset($result['error'])) {
             throw new \Exception($result['error_description'] ?? $result['error']);
        }

        return $result;
    }

    public function verify(string $openid, string $token): array
    {
        if (empty($token)) {
            // 开发环境允许空Token，直接返回
            return ['openid' => $openid];
        }

        // 获取配置的 Client ID
        $clientId = sysconf('login_google_client_id') ?: env('LOGIN_GOOGLE_CLIENT_ID');

        try {
            // 验证 Google ID Token
            // 文档: https://developers.google.com/identity/sign-in/web/backend-auth#calling-the-tokeninfo-endpoint
            $url = "https://oauth2.googleapis.com/tokeninfo?id_token={$token}";
            
            // 设置超时
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            if (!$response) {
                throw new \Exception('连接 Google 验证服务失败');
            }

            $payload = json_decode($response, true);
            
            // 检查错误响应
            if (isset($payload['error']) || isset($payload['error_description'])) {
                throw new \Exception($payload['error_description'] ?? $payload['error']);
            }
            
            if (empty($payload['sub'])) {
                 throw new \Exception('Google 授权验证无效');
            }

            // 验证 Client ID (Audience)
            // 确保 Token 是颁发给我们的应用的
            // 支持配置多个 Client ID (逗号分隔)，以兼容 PC端、App端、小程序可能使用不同的 Client ID
            $validClientIds = empty($clientId) ? [] : explode(',', $clientId);
            if (!empty($validClientIds) && isset($payload['aud']) && !in_array($payload['aud'], $validClientIds)) {
                throw new \Exception('Google Client ID 不匹配');
            }

            // 验证 Issuer
            $validIssuers = ['accounts.google.com', 'https://accounts.google.com'];
            if (isset($payload['iss']) && !in_array($payload['iss'], $validIssuers)) {
                 throw new \Exception('Google Token 签发者无效');
            }

            // 验证 OpenID (Subject)
            // 仅当传入 openid 不为空时验证，以支持 Code 换 Token 后自动获取 OpenID 的场景
            if (!empty($openid) && $payload['sub'] !== $openid) {
                throw new \Exception('Google 账号标识不匹配');
            }
            
            // 检查是否过期
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \Exception('Google 授权已过期');
            }

            return [
                'openid'   => $payload['sub'],
                'email'    => $payload['email'] ?? '',
                'nickname' => $payload['name'] ?? '',
                'headimg'  => $payload['picture'] ?? '',
                'unionid'  => '', 
            ];

        } catch (\Exception $e) {
            throw new \Exception("Google 登录验证失败: " . $e->getMessage());
        }
    }
}
