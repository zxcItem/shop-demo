<?php

declare (strict_types=1);

namespace app\data\model;

use app\data\model\account\DataAccountUser;
use think\model\relation\HasOne;

/**
 * 用户基础模型
 * @class AbsUser
 * @package app\data\model
 */
abstract class AbsUser extends Abs
{
    /**
     * 关联当前用户
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(DataAccountUser::class, 'id', 'unid');
    }

    /**
     * 绑定用户数据
     * @return HasOne
     */
    public function bindUser(): HasOne
    {
        return $this->user()->bind([
            'user_phone'        => 'phone',
            'user_headimg'      => 'headimg',
            'user_username'     => 'username',
            'user_nickname'     => 'nickname',
            'user_email'        => 'email',
        ]);
    }
}