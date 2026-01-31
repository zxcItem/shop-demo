<?php

declare (strict_types=1);

namespace app\data\controller\api\auth;

use app\data\controller\api\Auth;
use app\data\model\account\DataAccountAuth;
use app\data\model\account\DataAccountBind;
use app\data\service\Account;
use app\data\service\Message;
use app\data\service\Oauth;
use think\admin\Storage;
use think\exception\HttpResponseException;

/**
 * 用户账号管理
 * @class Center
 * @package app\data\controller\api\auth
 */
class Center extends Auth
{
    /**
     * 获取账号信息
     * @return void
     */
    public function get()
    {
        $this->success('获取资料', $this->account->get());
    }

    /**
     * 修改帐号信息
     * @return void
     */
    public function set()
    {
        try {
            $data = $this->checkUserStatus()->_vali([
                'headimg.default'     => '',
                'nickname.default'    => '',
                'password.default'    => '',
                'region_prov.default' => '',
                'region_city.default' => '',
                'region_area.default' => '',
            ]);
            // 保存用户头像
            if (!empty($data['headimg'])) {
                $data['headimg'] = Storage::saveImage($data['headimg'], 'headimg')['url'] ?? '';
            }
            // 修改登录密码
            if (!empty($data['password']) && strlen($data['password']) > 4) {
                $this->account->pwdModify($data['password']);
                unset($data['password']);
            }
            foreach ($data as $k => $v) if ($v === '') unset($data[$k]);
            if (empty($data)) $this->success('无需修改', $this->account->get());
            $this->success('修改成功', $this->account->bind(['id' => $this->unid], $data));
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 注销当前账号
     * @return void
     */
    public function forbid()
    {
        if (($user = $this->account->user())->isExists()) try {
            $this->app->db->transaction(function () use ($user) {
                $user->save(['deleted' => 1, 'remark' => '用户主动申请注销账号！']);
                DataAccountAuth::mk()->where(['usid' => $this->usid])->delete();
                DataAccountBind::mk()->where(['unid' => $this->unid])->delete();
            });
            $this->success('账号注销成功！');
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        } else {
            $this->error('未完成注册！');
        }
    }

    /**
     * 绑定主账号
     * @return void
     */
    public function bind()
    {
        try {
            $data = $this->_vali([
                'phone.mobile'   => '手机号错误',
                'phone.require'  => '手机号为空',
                'verify.require' => '验证码为空',
                'passwd.default' => ''
            ]);
            if (Message::checkVerifyCode($data['verify'], $data['phone'])) {
                Message::clearVerifyCode($data['phone']);
                $map = $bind = ['phone' => $data['phone']];
                if (!$this->account->isBind()) {
                    $user = $this->account->get();
                    $bind['headimg'] = $user['headimg'];
                    $bind['unionid'] = $user['unionid'];
                    $bind['nickname'] = $user['nickname'];
                }
                $this->account->set($map);
                $this->account->bind($map, $bind);
                if (!empty($data['passwd'])) {
                    $this->account->pwdModify($data['passwd']);
                }
                $this->success('关联成功!', $this->account->get(true));
            } else {
                $this->error('验证失败');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 解除账号关联
     * @return void
     */
    public function unbind()
    {
        $this->account->unBind();
        $this->success('关联成功', $this->account->get());
    }

    /**
     * 绑定第三方账号
     * @return void
     */
    public function bindOauth()
    {
        try {
            $data = $this->_vali([
                'type.require'         => '类型为空',
                'code.default'         => '',
                'token.default'        => '',
                'openid.default'       => '',
                'redirect_uri.default' => '',
            ]);
            // 获取并验证驱动
            $driver = Oauth::mk($data['type']);
            // 优先使用 code 换取 token
            if (!empty($data['code']) && method_exists($driver, 'exchangeCode')) {
                $tokenData = $driver->exchangeCode($data['code'], $data['redirect_uri']);
                // 尝试获取 id_token (OIDC) 或 access_token
                $data['token'] = $tokenData['id_token'] ?? $tokenData['access_token'] ?? '';
            }
            if (empty($data['token'])) {
                $this->error('无法获取授权令牌');
            }
            // 验证令牌并获取用户信息
            $userInfo = $driver->verify($data['openid'], $data['token']);
            // 执行账号绑定
            Account::bind($this->unid, $data['type'], $userInfo);
            $this->success('绑定成功', $this->account->get(true));
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * 解绑第三方账号
     * @return void
     */
    public function unbindOauth()
    {
        try {
            $data = $this->_vali(['type.require' => '类型为空']);
            Account::unBind($this->unid, $data['type']);
            $this->success('解绑成功', $this->account->get(true));
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}