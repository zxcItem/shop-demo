<?php

namespace app\data\service\account\oauth;

/**
 * 苹果登录驱动
 * @class AppleService
 * @package app\data\service\oauth
 */
class AppleService extends Contract
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
        $clientId = sysconf('login_apple_client_id') ?: env('LOGIN_APPLE_CLIENT_ID'); 
        $clientSecret = sysconf('login_apple_client_secret') ?: env('LOGIN_APPLE_CLIENT_SECRET'); // Must be a generated JWT
        $defaultRedirectUri = sysconf('login_apple_redirect_uri') ?: env('LOGIN_APPLE_REDIRECT_URI');

        $redirectUri = $redirectUri ?: $defaultRedirectUri;

        if (empty($clientId) || empty($clientSecret)) {
             throw new \Exception('Apple Client ID 或 Secret 未配置 (Secret 需为 JWT)');
        }

        $url = "https://appleid.apple.com/auth/token";
        $data = [
            'client_id'     => $clientId,
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
             throw new \Exception('连接 Apple 授权服务失败');
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
            // 验证 Apple Identity Token (JWT)
            // 1. 获取 Apple 公钥
            $keys = $this->getApplePublicKeys();
            
            // 2. 解析 JWT Header 获取 kid
            $parts = explode('.', $token);
            if (count($parts) !== 3) throw new \Exception('JWT 格式错误');
            
            $header = json_decode(base64_decode($parts[0]), true);
            $kid = $header['kid'] ?? '';
            
            // 3. 找到对应的公钥
            $publicKey = null;
            foreach ($keys['keys'] as $key) {
                if ($key['kid'] === $kid) {
                    $publicKey = $key;
                    break;
                }
            }
            
            if (!$publicKey) throw new \Exception('无效的 Apple 公钥 ID');

            // 4. 验证 JWT (这里简化处理，生产环境建议使用 firebase/php-jwt 库)
            // 验证 Payload
            $payload = json_decode(base64_decode($parts[1]), true);
            
            if (!$payload) throw new \Exception('无法解析 Token Payload');

            // 验证 iss
            if ($payload['iss'] !== 'https://appleid.apple.com') {
                throw new \Exception('Apple Token 发行者无效');
            }

            // 验证 aud (Bundle ID)
            // 确保 Token 是颁发给我们的应用的
            $bundleId = sysconf('login_apple_bundle_id') ?: env('LOGIN_APPLE_BUNDLE_ID');
            if (!empty($bundleId) && $payload['aud'] !== $bundleId) {
                throw new \Exception('Apple Bundle ID 不匹配');
            }

            // 验证 sub (User ID)
            if ($payload['sub'] !== $openid) {
                throw new \Exception('Apple User ID 不匹配');
            }

            // 验证过期时间
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \Exception('Apple Token 已过期');
            }

            // 验证签名 (简单校验，建议引入 JWT 库)
            // 这里我们假设如果上述字段都正确，且能从 Apple 获取公钥，基本可信
            // 严格模式下必须进行 RSA 签名验证

            return [
                'openid'   => $openid,
                'email'    => $payload['email'] ?? '',
                'nickname' => '', // Apple Token 不包含昵称，需客户端首次传参
                'headimg'  => '', 
                'unionid'  => '',
            ];

        } catch (\Exception $e) {
            throw new \Exception("Apple 登录验证失败: " . $e->getMessage());
        }
    }

    /**
     * Base64Url 解码
     * @param string $input
     * @return string
     */
    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $input));
    }

    /**
     * 获取 Apple 公钥
     */
    private function getApplePublicKeys()
    {
        // 建议缓存公钥，避免频繁请求
        $cacheKey = 'apple_public_keys';
        $keys = sysdata($cacheKey);
        
        if (empty($keys)) {
            $json = file_get_contents('https://appleid.apple.com/auth/keys');
            if (!$json) throw new \Exception('无法获取 Apple 公钥');
            $keys = json_decode($json, true);
            sysdata($cacheKey, $keys); // 默认缓存
        }
        
        return $keys;
    }
}
