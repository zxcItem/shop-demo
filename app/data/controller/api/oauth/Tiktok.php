<?php

namespace app\data\controller\api\oauth;

use app\data\service\Account;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 抖音/TikTok登录接口
 * @class Tiktok
 * @package app\data\controller\api\oauth
 */
class Tiktok extends Controller
{
    /**
     * 执行登录
     */
    public function index()
    {
        try {
            $data = $this->_vali([
                'openid.require' => '账号标识为空',
            ]);

            // 检查通道是否有效
            if (empty(Account::get(Account::TIKTOK)['status'])) {
                $this->error('登录通道未开通');
            }

            $driver = \app\data\service\Oauth::mk(Account::TIKTOK);

            // 1. 如果有 Code，先换取 Token
            $code = $this->request->post('code');
            $redirectUri = $this->request->post('redirect_uri');
            $token = $this->request->post('token');

            if (!empty($code)) {
                if (method_exists($driver, 'exchangeCode')) {
                    $tokenData = $driver->exchangeCode($code, $redirectUri);
                    $token = $tokenData['access_token'] ?? '';
                } else {
                    $this->error('当前通道不支持 Authorization Code 模式');
                }
            }

            // 调用服务验证 Token
            $oauthUser = $driver->verify(
                $data['openid'] ?? '', 
                $token
            );

            // 构建账号数据
            $authData = [
                'openid'  => $oauthUser['openid'],
                'unionid' => $this->request->post('unionid', $oauthUser['unionid'] ?? ''),
                'nickname'=> $this->request->post('nickname', $oauthUser['nickname'] ?? ''),
                'headimg' => $this->request->post('headimg', $oauthUser['headimg'] ?? ''),
            ];

            // 实例化账号服务
            $account = Account::mk(Account::TIKTOK);
            
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
}
