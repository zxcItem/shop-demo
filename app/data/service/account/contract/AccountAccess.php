<?php


namespace app\data\service\account\contract;

use app\data\model\account\DataAccountAuth;
use app\data\model\account\DataAccountBind;
use app\data\model\account\DataAccountUser;
use app\data\service\account\Account;
use think\admin\Exception;
use think\admin\extend\CodeExtend;
use think\admin\extend\JwtExtend;
use think\admin\Storage;
use think\App;

/**
 * 用户账号通用类
 * @class AccountAccess
 * @package app\data\service\contract
 */
class AccountAccess implements AccountInterface
{
    /**
     * 当前应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 当前用户对象
     * @var DataAccountUser
     */
    protected $user;

    /**
     * 当前认证对象
     * @var DataAccountAuth
     */
    protected $auth;

    /**
     * 当前终端对象
     * @var DataAccountBind
     */
    protected $bind;

    /**
     * 当前通道类型
     * @var string
     */
    protected $type;

    /**
     * 授权检查字段
     * @var string
     */
    protected $field;

    /**
     * 是否JWT模式
     * @var boolean
     */
    protected $isjwt;

    /**
     * 令牌有效时间
     * @var integer
     */
    protected $expire = 3600;

    /**
     * 测试专用 TOKEN
     * 主要用于接口文档演示
     * @var string
     */
    public const tester = 'tester';

    /**
     * 通道构造方法
     * @param \think\App $app
     * @param string $type 通道类型
     * @param string $field 授权字段
     * @throws \think\admin\Exception
     */
    public function __construct(App $app, string $type, string $field)
    {
        $this->app = $app;
        $this->type = $type;
        $this->field = $field;
        $this->expire = Account::expire();
    }

    /**
     * 初始化通道
     * @param string|array $token 令牌或条件
     * @param boolean $isjwt 是否返回令牌
     * @return AccountInterface
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DbException
     */
    public function init($token = '', bool $isjwt = true): AccountInterface
    {
        $this->isjwt = $isjwt;
        $this->auth = DataAccountAuth::mk();
        $this->bind = DataAccountBind::mk();
        $this->user = DataAccountUser::mk();
        if (is_string($token)) {
            $map = ['type' => $this->type, 'token' => $token];
            $this->auth = DataAccountAuth::mk()->where($map)->findOrEmpty();
            $this->bind = $this->auth->client()->findOrEmpty();
            $this->user = $this->bind->user()->findOrEmpty();
        }
        elseif (is_array($token)) {
            // 返向查询终端账号
            $map = ['deleted' => 0];
            if ($this->type)
                $map['type'] = $this->type;
            $this->bind = DataAccountBind::mk()->where($map)->where($token)->findOrEmpty();
            $this->user = $this->bind->user()->findOrEmpty();
            if ($this->bind->isExists()) {
                if (empty($this->type))
                    $this->type = $this->bind->getAttr('type');
                if ($this->auth->isEmpty())
                    $this->token(false);
            }
        }
        return $this;
    }

    /**
     * 设置子账号资料
     * @param array $data 用户资料
     * @param boolean $rejwt 返回令牌
     * @return array
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DbException
     */
    public function set(array $data = [], bool $rejwt = false): array
    {
        // 如果传入授权验证字段
        if (isset($data[$this->field])) {
            if ($this->bind->isExists()) {
                if ($data[$this->field] !== $this->bind->getAttr($this->field)) {
                    throw new Exception('禁止强行关联！');
                }
            }
            else {
                $map = [$this->field => $data[$this->field]];
                if ($this->type)
                    $map['type'] = $this->type;
                $this->bind = DataAccountBind::mk()->where($map)->findOrEmpty();
            }
        }
        elseif ($this->bind->isEmpty()) {
            throw new Exception("字段 {$this->field} 为空！");
        }
        $this->bind = $this->save(array_merge($data, ['type' => $this->type]));
        if ($this->bind->isEmpty())
            throw new Exception('更新资料失败！');
        // 刷新更新用户模型
        $this->user = $this->bind->user()->findOrEmpty();
        return $this->token()->get($rejwt);
    }

    /**
     * 获取用户数据
     * @param boolean $rejwt 返回令牌
     * @param boolean $refresh 刷新数据
     * @return array
     */
    public function get(bool $rejwt = false, bool $refresh = false): array
    {
        if ($refresh) {
            $this->bind->isExists() && $this->bind->refresh();
            $this->user->isExists() && $this->user->refresh();
        }
        $data = $this->bind->hidden(['sort', 'password'], true)->toArray();
        if ($this->bind->isExists()) {
            if ($this->user->isEmpty()) {
                $this->user = $this->bind->user()->findOrEmpty();
            }
            $data['user'] = $this->user->hidden(['sort', 'password'], true)->toArray();
            if ($rejwt)
                $data['token'] = $this->isjwt ?JwtExtend::token([
                    'type' => $this->auth->getAttr('type'), 'token' => $this->auth->getAttr('token')
                ]) : $this->auth->getAttr('token');
        }
        return $data;
    }

    /**
     * 获取接口成功资料
     * @param boolean $refresh
     * @return array
     */
    public function getApiData(bool $refresh = false): array
    {
        $data = $this->get(true, $refresh);
        return [
            'user' => $data['user'] ?? [],
            'token' => $data['token'] ?? '',
        ];
    }

    /**
     * 验证终端密码
     * @param string $pass 待验证密码
     * @return boolean
     * @throws \think\admin\Exception
     */
    public function pwdVerify(string $pass): bool
    {
        $pass = md5($pass);
        if ($this->user->getAttr('password') === $pass)
            return !!$this->expire();
        return $this->bind->getAttr('password') === $pass && $this->expire();
    }

    /**
     * 修改终端密码
     * @param string $pass 待修改密码
     * @param boolean $event 触发事件
     * @return boolean
     */
    public function pwdModify(string $pass, bool $event = true): bool
    {
        if ($this->bind->isEmpty())
            return false;
        $data = ['password' => md5($pass)];
        $this->user->isExists() && $this->user->save($data);
        if (!$this->bind->save($data))
            return false;
        if ($event)
            $this->app->event->trigger('DataAccountChangePassword', [
                'unid' => $this->getUnid(), 'pass' => $pass
            ]);
        return true;
    }

    /**
     * 绑定主账号
     * @param array $map 主账号条件
     * @param array $data 主账号资料
     * @return array
     * @throws \think\admin\Exception
     */
    public function bind(array $map, array $data = []): array
    {
        if ($this->bind->isEmpty())
            throw new Exception('终端账号异常！');

        // 使用数据库事务确保绑定操作的原子性
        return $this->app->db->transaction(function () use ($map, $data) {
            $this->user = DataAccountUser::mk()->where(['deleted' => 0])->where($map)->findOrEmpty();
            // 检查账号是否已被绑定
            if (($unid = intval($this->bind->getAttr('unid'))) > 0) {
                if ($this->user->isExists() && $unid !== intval($this->user->getAttr('id'))) {
                    throw new Exception('该账号已被其他用户绑定，请解绑后再试！');
                }
            }
            // 检查用户是否已绑定同类账号
            if ($this->user->isExists()) {
                $count = DataAccountBind::mk()->where([
                    'unid' => $this->user->getAttr('id'),
                    'type' => $this->type,
                    'deleted' => 0,
                ])->where('id', '<>', $this->bind->getAttr('id'))->count();
                if ($count > 0)
                    throw new Exception('您已绑定该类型的其他账号，请先解绑！');
            }
            if (!empty($data['extra']))
                $this->user->setAttr('extra', array_merge($this->user->getAttr('extra'), $data['extra']));
            unset($data['id'], $data['code'], $data['extra']);
            // 生成新的用户编号
            if ($this->user->isEmpty())
                do
                    $check = ['code' => $data['code'] = $this->userCode()];
                while (DataAccountUser::mk()->master()->where($check)->findOrEmpty()->isExists());
            // 自动绑定默认头像
            if (empty($data['headimg']) && $this->user->isEmpty() || empty($this->user->getAttr('headimg'))) {
                if (empty($data['headimg'] = $this->bind->getAttr('headimg')))
                    $data['headimg'] = Account::headimg();
            }
            if (empty($data['invite_code']) && $this->user->isEmpty() || empty($this->user->getAttr('invite_code'))) {
                if (empty($data['invite_code'] = $this->bind->getAttr('invite_code')))
                    $data['invite_code'] = Account::invite_code();
            }
            // 自动生成用户昵称
            if (empty($data['nickname']) && empty($this->user->getAttr('nickname'))) {
                $prefix = Account::config('userPrefix') ?: (Account::get($this->type)['name'] ?? $this->type);
                if ($phone = $data['phone'] ?? $this->user->getAttr('phone')) {
                    $data['nickname'] = $prefix . substr($phone, -4);
                }
                else {
                    $data['nickname'] = "{$prefix}{$this->bind->getAttr('id')}";
                }
            }
            // 同步用户登录密码
            if (!empty($this->bind->getAttr('password'))) {
                $data['password'] = $this->bind->getAttr('password');
            }
            // 保存更新用户数据
            if ($this->user->save($data + $map)) {
                $this->bind->save(['unid' => $this->user['id']]);
                $this->app->event->trigger('DataAccountBind', [
                    'type' => $this->type,
                    'unid' => intval($this->user->getAttr('id')),
                    'usid' => intval($this->bind->getAttr('id')),
                ]);
                return $this->get();
            }
            else {
                throw new Exception('绑定用户失败！');
            }
        });
    }

    /**
     * 解绑主账号
     * @return array
     * @throws \think\admin\Exception
     */
    public function unBind(): array
    {
        if ($this->bind->isEmpty()) {
            throw new Exception('终端账号异常！');
        }
        if (($unid = $this->bind->getAttr('unid')) > 0) {
            $this->bind->save(['unid' => 0]);
            $this->app->event->trigger('DataAccountUnbind', [
                'type' => $this->type,
                'unid' => intval($unid),
                'usid' => intval($this->bind->getAttr('id')),
            ]);
        }
        return $this->get();
    }

    /**
     * 判断绑定主账号
     * @return boolean
     */
    public function isBind(): bool
    {
        return $this->user->isExists();
    }

    /**
     * 判断是否空账号
     * @return boolean
     */
    public function isNull(): bool
    {
        return $this->bind->isEmpty();
    }

    /**
     * 获取关联终端
     * @return array
     */
    public function allBind(): array
    {
        try {
            if ($this->isNull())
                return [];
            if ($this->isBind() && ($unid = $this->bind->getAttr('unid'))) {
                $map = ['unid' => $unid, 'deleted' => 0];
                return DataAccountBind::mk()->where($map)->select()->toArray();
            }
            else {
                return [$this->bind->refresh()->toArray()];
            }
        }
        catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * 解除终端关联
     * @param integer $usid 终端编号
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function delBind(int $usid): array
    {
        if ($this->isBind() && ($unid = $this->bind->getAttr('unid'))) {
            $map = ['id' => $usid, 'unid' => $unid];
            DataAccountBind::mk()->where($map)->update(['unid' => 0]);
        }
        return $this->allBind();
    }

    /**
     * 刷新账号序号
     * @return array
     */
    public function recode(): array
    {
        if ($this->bind->isEmpty())
            return $this->get();
        if ($this->user->isExists()) {
            do
                $check = ['code' => $this->userCode()];
            while (DataAccountUser::mk()->master()->where($check)->findOrEmpty()->isExists());
            $this->user->save($check);
        }
        return $this->get();
    }

    /**
     * 检查是否有效
     * @return array
     * @throws \think\admin\Exception
     */
    public function check(): array
    {
        if ($this->bind->isEmpty()) {
            throw new Exception('请重新登录！', 401);
        }
        if ($this->expire > 0 && $this->auth->getAttr('time') < time()) {
            throw new Exception('登录已超时！', 403);
        }
        return static::expire()->get();
    }

    /**
     * 获取用户模型
     * @return DataAccountUser
     */
    public function user(): DataAccountUser
    {
        return $this->user->hidden(['sort', 'password'], true);
    }

    /**
     * 获取用户编号
     * @return string
     */
    public function getCode(): string
    {
        return $this->user->getAttr('code') ?: '';
    }

    /**
     * 获取终端类型
     * @return string
     */
    public function getType(): string
    {
        return $this->bind->getAttr('type') ?: '';
    }

    /**
     * 获取用户编号
     * @return integer
     */
    public function getUnid(): int
    {
        return intval($this->bind->getAttr('unid'));
    }

    /**
     * 获取终端编号
     * @return integer
     */
    public function getUsid(): int
    {
        return intval($this->bind->getAttr('id'));
    }

    /**
     * 生成授权令牌
     * @param boolean $expire
     * @return AccountInterface
     * @throws \think\admin\Exception
     * @throws \think\db\exception\DbException
     */
    public function token(bool $expire = true): AccountInterface
    {
        // 百分之一概率清理令牌
        if (mt_rand(1, 1000) < 10) {
            DataAccountAuth::mk()->whereBetween('time', [1, time()])->delete();
        }
        $usid = $this->bind->getAttr('id');
        // 查询该通道历史授权记录
        if ($this->auth->isEmpty()) {
            $where = ['usid' => $usid, 'type' => $this->type];
            $this->auth = DataAccountAuth::mk()->where($where)->findOrEmpty();
        }
        // 生成新令牌数据
        if ($this->auth->isEmpty()) {
            do
                $check = ['type' => $this->type, 'token' => md5(uniqid(strval(rand(0, 999))))];
            while (DataAccountAuth::mk()->master()->where($check)->findOrEmpty()->isExists());
            $time = $this->expire > 0 ? $this->expire + time() : 0;
            $this->auth->save($check + ['usid' => $usid, 'time' => $time]);
        }
        return $expire ? $this->expire() : $this;
    }

    /**
     * 延期令牌时间
     * @return AccountInterface
     * @throws \think\admin\Exception
     */
    public function expire(): AccountInterface
    {
        if ($this->auth->isEmpty())
            throw new Exception('无授权记录！');
        $this->auth->save(['time' => $this->expire > 0 ? $this->expire + time() : 0]);
        return $this;
    }

    /**
     * 更新用户资料
     * @param array $data
     * @return DataAccountBind
     * @throws \think\admin\Exception
     */
    private function save(array $data): DataAccountBind
    {
        if (empty($data))
            throw new Exception('资料不能为空！');

        // 收集所有可能的关联ID，统一处理避免冲突
        $candidateUnid = null;

        // 1. 通过 UnionId 查找可能的主账号 (微信体系、QQ等)
        if (empty($this->bind->getAttr('unid')) && !empty($data['unionid'])) {
            $lockKey = "account:bind:unionid:{$data['unionid']}";
            if ($this->app->cache->has($lockKey)) {
                throw new Exception('账号关联处理中，请稍后重试！');
            }
            $this->app->cache->set($lockKey, 1, 10);

            try {
                // 查找主账号
                $user = DataAccountUser::mk()->where(['unionid' => $data['unionid'], 'deleted' => 0])->findOrEmpty();
                if ($user->isExists()) {
                    $candidateUnid = $user->getAttr('id');
                }
                else {
                    // 查找其他已绑定的终端账号
                    $bind = DataAccountBind::mk()->where(['unionid' => $data['unionid'], 'deleted' => 0])->where('unid', '>', 0)->findOrEmpty();
                    if ($bind->isExists()) {
                        $candidateUnid = $bind->getAttr('unid');
                    }
                }
            }
            finally {
                $this->app->cache->delete($lockKey);
            }
        }

        // 2. 通过 Email 查找可能的主账号 (Apple/Google/Facebook/邮箱登录等)
        // 仅当未通过unionid找到关联时才继续
        if (empty($candidateUnid) && empty($this->bind->getAttr('unid')) && !empty($data['email'])) {
            $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
            if ($email !== false) {
                $lockKey = "account:bind:email:" . md5($email);
                if ($this->app->cache->has($lockKey)) {
                    throw new Exception('账号关联处理中，请稍后重试！');
                }
                $this->app->cache->set($lockKey, 1, 10);

                try {
                    // 查找已绑定该邮箱的主账号
                    $user = DataAccountUser::mk()->where(['email' => $email, 'deleted' => 0])->findOrEmpty();
                    if ($user->isExists()) {
                        $candidateUnid = $user->getAttr('id');
                    }
                    else {
                        // 查找其他终端是否已用此邮箱关联了主账号
                        $bind = DataAccountBind::mk()
                            ->where('email', $email)
                            ->where('unid', '>', 0)
                            ->where('deleted', 0)
                            ->findOrEmpty();
                        if ($bind->isExists()) {
                            $candidateUnid = $bind->getAttr('unid');
                        }
                    }
                }
                finally {
                    $this->app->cache->delete($lockKey);
                }
            }
        }

        // 3. 通过手机号查找可能的主账号 (仅当用户主动绑定时)
        // 注意：手机号绑定通常在 bind() 方法中处理，这里只处理自动关联场景
        // 如果传入了phone字段且当前终端未绑定，尝试关联
        if (empty($candidateUnid) && empty($this->bind->getAttr('unid')) && !empty($data['phone'])) {
            $phone = $data['phone'];
            if (preg_match("/^1[3-9]\d{9}$/", $phone)) {
                $lockKey = "account:bind:phone:{$phone}";
                if ($this->app->cache->has($lockKey)) {
                    throw new Exception('账号关联处理中，请稍后重试！');
                }
                $this->app->cache->set($lockKey, 1, 10);

                try {
                    // 查找已绑定该手机号的主账号
                    $user = DataAccountUser::mk()->where(['phone' => $phone, 'deleted' => 0])->findOrEmpty();
                    if ($user->isExists()) {
                        $candidateUnid = $user->getAttr('id');
                    }
                }
                finally {
                    $this->app->cache->delete($lockKey);
                }
            }
        }

        // 4. 统一处理关联结果
        if (!empty($candidateUnid)) {
            // 检查该主账号是否已被同类型终端绑定
            $existBind = DataAccountBind::mk()->where([
                'unid' => $candidateUnid,
                'type' => $this->type,
                'deleted' => 0,
            ])->where('id', '<>', $this->bind->getAttr('id'))->findOrEmpty();

            if (!$existBind->isExists()) {
                // 该类型终端未被绑定，可以自动关联
                $data['unid'] = $candidateUnid;
            }
        // 如果已被绑定，不报错，继续作为独立账号存在
        }

        $data['extra'] = array_merge($this->bind->getAttr('extra'), $data['extra'] ?? []);

        // 统一默认图像地址 (当新图像为空且原图像也为空时，赋予默认图像)
        if (empty($data['headimg']) && empty($this->bind->getAttr('headimg'))) {
            $data['headimg'] = Account::headimg();
        }

        // 自动生成账号昵称
        if (empty($data['nickname']) && empty($this->bind->getAttr('nickname'))) {
            $name = Account::get($this->type)['name'] ?? $this->type;
            $data['nickname'] = "{$name}{$this->bind->getAttr('id')}";
        }
        // 更新写入终端账号
        if ($this->bind->save($data) && $this->bind->isExists()) {
            // 触发绑定事件
            if (!empty($data['unid'])) {
                $this->app->event->trigger('DataAccountBind', [
                    'type' => $this->type,
                    'unid' => intval($data['unid']),
                    'usid' => intval($this->bind->getAttr('id')),
                ]);
            }
            return $this->bind->refresh();
        }
        else {
            throw new Exception('资料保存失败！');
        }
    }

    /**
     * 生成用户编号
     * @return string
     */
    private function userCode(): string
    {
        return CodeExtend::uniqidNumber(12, 'U');
    }
}