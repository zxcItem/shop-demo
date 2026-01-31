<?php

namespace app\data\service\oauth;

/**
 * 抖音/TikTok登录驱动
 * @class Tiktok
 * @package app\data\service\oauth
 */
class Tiktok extends Contract
{
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
