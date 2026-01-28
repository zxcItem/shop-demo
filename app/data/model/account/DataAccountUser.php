<?php


namespace app\data\model\account;

use app\data\model\Abs;
use app\data\model\user\DataUserStore;
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

    public function store()
    {
        return $this->hasOne(DataUserStore::class, 'unid', 'id')->where('check_status',1);
    }

    public function storeStatus()
    {
        return $this->belongsTo(DataUserStore::class, 'id', 'unid')->bind(['store_status'=>'check_status']);
    }

    public function storeLevel()
    {
        return $this->belongsTo(DataUserStore::class, 'id', 'unid')->with(['level'=>function($level){
//            $level->withOutfield('status,sort,create_time');
        }])->where('check_status',2)->bind(['level_id'=>'level_id']);
    }
}
