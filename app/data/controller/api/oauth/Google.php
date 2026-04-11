<?php

namespace app\data\controller\api\oauth;

use app\data\service\account\Account;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 谷歌登录接口
 * @class Google
 * @package app\data\controller\api\oauth
 */
class Google extends Controller
{
    /**
     * 执行登录
     */
    public function index()
    {
        try {
            // 获取参数，支持 token 或 code
            $code = $this->request->post('code');
            $token = $this->request->post('token');
            $openid = $this->request->post('openid', '');
            $redirectUri = $this->request->post('redirect_uri');

            // 检查通道是否有效
            if (empty(Account::get(Account::GOOGLE)['status'])) {
                $this->error('登录通道未开通');
            }

            $driver = \app\data\service\account\Oauth::mk(Account::GOOGLE);

            // 1. 如果有 Code，先换取 Token (适用于 Web/PC 服务端模式)
            if (!empty($code)) {
                if (method_exists($driver, 'exchangeCode')) {
                    $tokenData = $driver->exchangeCode($code, $redirectUri);
                    $token = $tokenData['id_token'] ?? '';
                } else {
                    $this->error('当前通道不支持 Authorization Code 模式');
                }
            }

            // 2. 验证参数
            if (empty($token)) {
                $this->error('登录凭证(Token)不能为空');
            }

            // 3. 调用服务验证 Token
            // 注意：如果 openid 为空，verify 会跳过比对，直接返回解析出的 openid
            $oauthUser = $driver->verify($openid, $token);

            // 4. 构建账号数据 (合并服务端返回的用户信息) - 包含email用于自动关联
            $authData = [
                'openid'  => $oauthUser['openid'],
                'email'   => $oauthUser['email'] ?? ($this->request->post('email', '')),
                'unionid' => $this->request->post('unionid', $oauthUser['unionid'] ?? ''),
                'nickname'=> $this->request->post('nickname', $oauthUser['nickname'] ?? ''),
                'headimg' => $this->request->post('headimg', $oauthUser['headimg'] ?? ''),
            ];

            // 5. 实例化账号服务
            $account = Account::mk(Account::GOOGLE);
            
            // 6. 设置账号资料 (会自动处理注册/绑定/更新，包括通过email自动关联)
            $account->set($authData);

            // 7. 返回登录结果 (包含 token)
            $this->success('登录成功', $account->token()->get(true));

        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
