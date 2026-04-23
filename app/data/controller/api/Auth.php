<?php


namespace app\data\controller\api;

use app\data\service\account\Account;
use app\data\service\account\contract\AccountInterface;
use think\admin\Controller;
use think\exception\HttpResponseException;

/**
 * 接口授权抽象类
 * @class Auth
 * @package app\data\controller\api
 */
abstract class Auth extends Controller
{

    /**
     * 接口类型
     * @var string
     */
    protected $type;

    /**
     * 主账号编号
     * @var integer
     */
    protected $unid;

    /**
     * 子账号编号
     * @var integer
     */
    protected $usid;

    /**
     * 终端账号接口
     * @var AccountInterface
     */
    protected $account;

    /**
     * 免登录方法
     * @var array
     */
    protected $ignoreAuth = [];

    /**
     * 控制器初始化
     */
    protected function initialize()
    {
        try {
            // 获取请求令牌内容
            // 优先识别 Bearer Token 机制，再识别 api-token 字段
            $token = $this->request->header('Authorization', '');
            if (!empty($token) && stripos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            if (empty($token)) {
                $token = $this->request->header('api-token', '');
            }
            if (empty($token)) {
                $token = $this->request->header('token', '');
            }
            if (empty($token)) {
                if (in_array($this->request->action(), $this->ignoreAuth)) {
                    $this->unid = 0;
                    $this->usid = 0;
                    return;
                }
                $this->error('需要登录授权', [], 401);
            }
            // 读取用户账号数据
            $this->account = Account::mk('', $token);
            $login = $this->account->check();
            $this->usid = intval($login['id'] ?? 0);
            $this->unid = intval($login['unid'] ?? 0);
            $this->type = strval($login['type'] ?? '');
            // 临时缓存登录数据
            sysvar('data_account_object', $this->account);
            sysvar('data_account_user_type', $this->type);
            sysvar('data_account_user_usid', $this->usid);
            sysvar('data_account_user_unid', $this->unid);
            sysvar('data_account_user_code', $this->account->getCode());
            $this->checkUserStatus();
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage(), [], $exception->getCode());
        }
    }

    /**
     * 检查用户状态
     * @param boolean $isBind
     * @return $this
     */
    protected function checkUserStatus(bool $isBind = true): Auth
    {
        $login = $this->account->get();
        if (empty($login['status'])) {
            $this->error('终端已冻结', $login, 403);
        } elseif ($isBind) {
            if (empty($login['user'])) {
                $this->error('请绑定账号', $login, 402);
            }
            if (empty($login['user']['status'])) {
                $this->error('账号已冻结', $login, 403);
            }
        }
        return $this;
    }
}