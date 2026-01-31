<?php

namespace app\data\service\oauth;

/**
 * 谷歌登录驱动
 * @class Google
 * @package app\data\service\oauth
 */
class Google extends Contract
{
    public function verify(string $openid, string $token): array
    {
        if (empty($token)) {
            // 开发环境允许空Token，直接返回
            return ['openid' => $openid];
        }

        // 获取配置的 Client ID
        $clientId = sysconf('login_google_client_id');

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

            // 验证 OpenID (Subject)
            if ($payload['sub'] !== $openid) {
                throw new \Exception('Google 账号标识不匹配');
            }
            
            // 检查是否过期
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \Exception('Google 授权已过期');
            }

            return [
                'openid'   => $payload['sub'],
                'nickname' => $payload['name'] ?? '',
                'headimg'  => $payload['picture'] ?? '',
                'unionid'  => '', 
            ];

        } catch (\Exception $e) {
            throw new \Exception("Google 登录验证失败: " . $e->getMessage());
        }
    }
}
