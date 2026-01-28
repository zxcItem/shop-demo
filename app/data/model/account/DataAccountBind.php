<?php


namespace app\data\model\account;

use app\data\model\Abs;
use app\data\service\Account;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 账号-终端
 * Class DataAccountBind
 * @package app\data\model
 */
class DataAccountBind extends Abs
{
    /**
     * 关联主账号
     * @return \think\model\relation\HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(DataAccountUser::class, 'id', 'unid');
    }

    /**
     * 关联授权数据
     * @return \think\model\relation\HasMany
     */
    public function auths(): HasMany
    {
        return $this->hasMany(DataAccountAuth::class, 'usid', 'id');
    }

    /**
     * 增加通道名称显示
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        if (isset($data['type'])) {
            $data['type_name'] = Account::get($data['type'])['name'] ?? $data['type'];
        }
        return $data;
    }
}