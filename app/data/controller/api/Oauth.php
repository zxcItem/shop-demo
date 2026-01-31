<?php

namespace app\data\controller\api;

use app\data\service\Account;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 第三方授权登录接口
 * @class Oauth
 * @package app\data\controller\api
 */
class Oauth extends Controller
{
    /**
     * 统一授权登录
     * @return void
     */
    public function login()
    {
        try {
            $data = $this->_vali([
                'type.require'   => '登录类型为空',
                'openid.require' => '账号标识为空',
                // 'token'       => '可选: 授权令牌(用于后端校验)',
                // 'nickname'    => '可选: 用户昵称',
                // 'headimg'     => '可选: 用户头像',
                // 'unionid'     => '可选: 跨应用标识',
            ]);

            // 检查通道是否有效
            if (empty(Account::get($data['type'])['status'])) {
                $this->error('登录通道未开通');
            }

            // 验证 Token (建议在生产环境中实现具体的校验逻辑)
            $this->checkToken($data['type'], $data['openid'], $this->request->post('token'));

            // 构建账号数据
            $authData = [
                'openid'  => $data['openid'],
                'unionid' => $this->request->post('unionid', ''),
                'nickname'=> $this->request->post('nickname', ''),
                'headimg' => $this->request->post('headimg', ''),
            ];

            // 实例化账号服务
            $account = Account::mk($data['type']);
            
            // 设置账号资料 (会自动处理注册/绑定/更新)
            $account->set($authData);

            // 返回登录结果 (包含 token)
            $this->success('登录成功', $account->token()->get(true));

        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 校验第三方 Token (桩代码)
     * @param string $type 通道类型
     * @param string $openid 用户标识
     * @param string|null $token 授权令牌
     * @throws \Exception
     */
    private function checkToken(string $type, string $openid, ?string $token)
    {
        if (empty($token)) {
            // 开发阶段暂允许空 Token，生产环境建议强制校验
            return;
        }

        switch ($type) {
            case Account::GOOGLE:
                // TODO: 调用 Google API 验证 id_token
                // $client = new \Google_Client(['client_id' => 'YOUR_CLIENT_ID']);
                // $payload = $client->verifyIdToken($token);
                // if ($payload['sub'] !== $openid) throw new \Exception('Google 授权验证失败');
                break;
            
            case Account::FACEBOOK:
                // TODO: 调用 Facebook Graph API 验证 access_token
                break;

            case Account::APPLE:
                // TODO: 验证 Apple Identity Token
                break;

            case Account::TIKTOK:
                // TODO: 验证 TikTok access_token
                break;
                
            // 其他平台...
        }
    }
}
