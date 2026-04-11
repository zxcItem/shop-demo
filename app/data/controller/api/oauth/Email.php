<?php

namespace app\data\controller\api\oauth;

use app\data\service\account\Account;
use app\data\service\account\Oauth;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 邮箱验证码登录
 * @class Email
 * @package app\data\controller\api\oauth
 */
class Email extends Controller
{
    /**
     * 发送验证码
     */
    public function send()
    {
        try {
            $email = $this->request->post('email');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('邮箱格式不正确');
            }

            // 检查通道是否有效
            if (empty(Account::get(Account::EMAIL)['status'])) {
                $this->error('登录通道未开通');
            }

            $driver = Oauth::mk(Account::EMAIL);
            if (method_exists($driver, 'sendCode')) {
                $driver->sendCode($email);
                $this->success('验证码发送成功');
            } else {
                $this->error('当前通道不支持发送验证码');
            }

        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 邮箱验证码登录
     */
    public function index()
    {
        try {
            $email = $this->request->post('email');
            $code = $this->request->post('code');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('邮箱格式不正确');
            }
            if (empty($code)) {
                $this->error('验证码不能为空');
            }
            
            // 检查通道是否有效
            if (empty(Account::get(Account::EMAIL)['status'])) {
                $this->error('登录通道未开通');
            }

            // 调用服务验证
            $driver = Oauth::mk(Account::EMAIL);
            $oauthUser = $driver->verify($email, $code);

            // 实例化账号服务
            $account = Account::mk(Account::EMAIL);
            
            // 设置账号资料
            $account->set([
                'openid'   => $oauthUser['openid'],
                'nickname' => $oauthUser['nickname'],
                'headimg'  => $oauthUser['headimg'] ?? '', 
            ]);

            $this->success('登录成功', $account->token()->get(true));

        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
