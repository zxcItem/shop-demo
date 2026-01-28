<?php


namespace app\data\model\account;

use app\data\model\Abs;
use think\model\relation\HasOne;

/**
 * 账号-授权
 * Class DataAccountAuth
 * @package app\data\model
 */
class DataAccountAuth extends Abs
{
    /**
     * 关联子账号
     * @return HasOne
     */
    public function client(): HasOne
    {
        return $this->hasOne(DataAccountBind::class, 'id', 'usid')->with(['user']);
    }
}