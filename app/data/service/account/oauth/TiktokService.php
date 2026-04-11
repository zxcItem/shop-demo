<?php

namespace app\data\service\account\oauth;

/**
 * 抖音/TikTok登录驱动
 * @class TiktokService
 * @package app\data\service\oauth
 */
class TiktokService extends Contract
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
        $clientKey = sysconf('login_tiktok_client_key') ?: env('LOGIN_TIKTOK_CLIENT_KEY');
        $clientSecret = sysconf('login_tiktok_client_secret') ?: env('LOGIN_TIKTOK_CLIENT_SECRET');
        $defaultRedirectUri = sysconf('login_tiktok_redirect_uri') ?: env('LOGIN_TIKTOK_REDIRECT_URI');

        $redirectUri = $redirectUri ?: $defaultRedirectUri;

        if (empty($clientKey) || empty($clientSecret)) {
             throw new \Exception('TikTok Client Key 或 Secret 未配置');
        }
        
        $url = "https://open.tiktokapis.com/v2/oauth/token/";
        $data = [
            'client_key'    => $clientKey,
            'client_secret' => $clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
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
             throw new \Exception('连接 TikTok 授权服务失败');
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
            return ['openid' => $openid];
        }

        try {
            // 验证 TikTok Access Token 并获取用户信息
            // 文档: https://developers.tiktok.com/doc/login-kit-user-info-basic
            // 注意：TikTok API 需要使用 POST 请求
            $url = "https://open.tiktokapis.com/v2/user/info/?fields=open_id,union_id,avatar_url,display_name";
            
            $options = [
                'http' => [
                    'method'  => 'GET', // V2 接口使用 GET
                    'header'  => "Authorization: Bearer {$token}\r\n",
                    'timeout' => 10,
                    'ignore_errors' => true
                ]
            ];
            
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
            
            if (!$response) throw new \Exception('连接 TikTok 验证服务失败');

            $payload = json_decode($response, true);
            
            // 检查 API 错误
            if (isset($payload['error'])) {
                 $msg = $payload['error']['message'] ?? 'TikTok API Error';
                 throw new \Exception($msg);
            }

            $data = $payload['data']['user'] ?? [];
            
            if (empty($data['open_id'])) {
                throw new \Exception('TikTok Token 无效或无权限');
            }

            // 验证 OpenID
            if ($data['open_id'] !== $openid) {
                throw new \Exception('TikTok OpenID 不匹配');
            }

            return [
                'openid'   => $data['open_id'],
                'nickname' => $data['display_name'] ?? '',
                'headimg'  => $data['avatar_url'] ?? '',
                'unionid'  => $data['union_id'] ?? '',
            ];

        } catch (\Exception $e) {
            throw new \Exception("TikTok 登录验证失败: " . $e->getMessage());
        }
    }
}
