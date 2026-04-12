<?php


namespace app\data\model\account;

use app\data\model\Abs;
use think\model\relation\HasMany;

/**
 * 账号-资料
 * Class DataAccountUser
 * @package app\data\model
 */
class DataAccountUser extends Abs
{
    /**
     * 关联子账号
     * @return HasMany
     */
    public function clients(): HasMany
    {
        return $this->hasMany(DataAccountBind::class, 'unid', 'id');
    }
}
