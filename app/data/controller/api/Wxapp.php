<?php

declare (strict_types=1);

namespace app\data\controller\api;

use app\data\service\account\Account;
use app\data\service\ConfigService;
use think\admin\Controller;
use think\admin\Exception;
use think\exception\HttpResponseException;
use WeMini\Crypt;

/**
 * 微信小程序入口
 * @class Wxapp
 * @package app\data\controller\api
 */
class Wxapp extends Controller
{
    /**
     * 接口通道类型
     * @var string
     */
    private $type = Account::WXAPP;

    /**
     * 小程序配置参数
     * @var array
     */
    private $params;

    /**
     * 接口初始化
     * @throws Exception
     */
    protected function initialize()
    {
        $wxapp = ConfigService::get();
        if (Account::field($this->type)) {
            $this->params = [
                'appid'      => env('WECHAT_MINI_APPID') ?: ($wxapp['appid'] ?? ''),
                'appsecret'  => env('WECHAT_MINI_APPSECRET') ?: ($wxapp['appkey'] ?? ''),
                'cache_path' => syspath('runtime/wechat'),
            ];
        } else {
            $this->error('接口未开通');
        }
    }

    /**
     * 换取会话
     */
    public function session()
    {
        try {
            $input = $this->_vali(['code.require' => '凭证编码为空']);
            [$openid, $unionid, $sesskey] = $this->applySesskey($input['code']);
            $data = [
                'appid'       => $this->params['appid'],
                'openid'      => $openid,
                'unionid'     => $unionid,
                'session_key' => $sesskey,
            ];
            $this->success('授权换取成功', Account::mk($this->type)->set($data, true));
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 数据解密
     */
    public function decode()
    {
        try {
            $input = $this->_vali([
                'iv.require'        => '解密向量为空',
                'code.require'      => '授权编码为空',
                'encrypted.require' => '密文内容为空',
            ]);
            [$openid, $unionid, $input['session_key']] = $this->applySesskey($input['code']);
            $result = Crypt::instance($this->params)->decode($input['iv'], $input['session_key'], $input['encrypted']);
            if (is_array($result) && isset($result['avatarUrl']) && isset($result['nickName'])) {
                $data = [
                    'extra'    => $result,
                    'appid'    => $this->params['appid'],
                    'openid'   => $openid,
                    'unionid'  => $unionid,
                    'headimg'  => $result['avatarUrl'],
                    'nickname' => $result['nickName'],
                ];
                if ($data['nickname'] === '微信用户') unset($data['headimg'], $data['nickname']);
                $this->success('解密成功', Account::mk($this->type)->set($data, true));
            } elseif (is_array($result)) {
                if (!empty($result['phoneNumber'])) {
                    $data = ['appid' => $this->params['appid'], 'openid' => $openid, 'unionid' => $unionid];
                    ($account = Account::mk($this->type))->set($data);
                    $account->bind(['phone' => $result['phoneNumber']], $data);
                    $this->success('绑定成功', $account->get(true));
                } else {
                    $this->success('解密成功', $result);
                }
            } else {
                $this->error('解析失败');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 快速获取手机号
     * @return void
     */
    public function phone()
    {
        try {
            $input = $this->_vali([
                'code.require'   => '授权编码为空',
                'openid.require' => '用户编号为空'
            ]);
            $result = Crypt::instance($this->params)->getPhoneNumber($input['code']);
//            if (is_array($result)) {
//                $this->success('解密成功', $result);
            if (is_array($result) && !empty($result['phoneNumber'])) {
                $data = ['appid' => $this->params['appid'], 'openid' => $input['openid']];
                ($account = Account::mk($this->type))->set($data);
                $account->bind(['phone' => $result['phoneNumber']]);
                $this->success('绑定成功', $account->get(true));
            } else {
                $this->error('解析失败');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("处理失败，{$exception->getMessage()}");
        }
    }

    /**
     * 换取会话授权
     * @param string $code 授权编号
     * @return void|array [openid, unionid, sessionkey]
     */
    private function applySesskey(string $code): array
    {
        try {
            $cache = $this->app->cache->get($code, []);
            if (isset($cache['openid']) && isset($cache['session_key'])) {
                return [$cache['openid'], $cache['unionid'] ?? '', $cache['session_key']];
            }
            $result = Crypt::instance($this->params)->session($code);
            if (isset($result['openid']) && isset($result['session_key'])) {
                $this->app->cache->set($code, $result, 7200);
                return [$result['openid'], $result['unionid'] ?? '', $result['session_key']];
            } else {
                $this->error($result['errmsg'] ?? '换取失败');
            }
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            trace_file($exception);
            $this->error("授权失败，{$exception->getMessage()}");
        }
    }
}