<?php

namespace app\data\service\oauth;

/**
 * QQ登录驱动
 * @class Qq
 * @package app\data\service\oauth
 */
class Qq extends Contract
{
    public function verify(string $openid, string $token): array
    {
        if (empty($token)) {
            return ['openid' => $openid];
        }

        try {
            // 验证 QQ Access Token 并获取 OpenID
            // 文档: https://wiki.connect.qq.com/get_user_info
            // 注意：QQ 需要调用 graph.qq.com/oauth2.0/me 来验证 token 对应的 openid
            $url = "https://graph.qq.com/oauth2.0/me?access_token={$token}&fmt=json";
            
            $context = stream_context_create([
                'http' => ['timeout' => 10, 'ignore_errors' => true]
            ]);
            
            $response = file_get_contents($url, false, $context);
            if (!$response) throw new \Exception('连接 QQ 验证服务失败');

            $data = json_decode($response, true);
            
            if (isset($data['error'])) {
                throw new \Exception($data['error_description'] ?? 'QQ Token 验证失败');
            }

            // 验证 OpenID 是否一致
            if (!isset($data['openid']) || $data['openid'] !== $openid) {
                throw new \Exception('QQ OpenID 不匹配');
            }

            // 获取用户信息 (需要 AppID)
            $appId = sysconf('login_qq_appid');
            $userInfo = [];
            
            if (!empty($appId)) {
                $infoUrl = "https://graph.qq.com/user/get_user_info?access_token={$token}&oauth_consumer_key={$appId}&openid={$openid}";
                $infoResp = file_get_contents($infoUrl, false, $context);
                if ($infoResp) {
                    $infoData = json_decode($infoResp, true);
                    if (isset($infoData['ret']) && $infoData['ret'] === 0) {
                        $userInfo = $infoData;
                    }
                }
            }

            return [
                'openid'   => $openid,
                'nickname' => $userInfo['nickname'] ?? '',
                'headimg'  => $userInfo['figureurl_qq_2'] ?? ($userInfo['figureurl_qq_1'] ?? ''),
                'unionid'  => $data['unionid'] ?? '', // 需要申请 unionid 权限才会有
            ];

        } catch (\Exception $e) {
            throw new \Exception("QQ 登录验证失败: " . $e->getMessage());
        }
    }
}
