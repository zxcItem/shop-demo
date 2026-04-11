<?php

declare(strict_types=1);

namespace app\data\model\wemall;

use app\data\model\AbsUser;
use think\model\relation\HasOne;

/**
 * 用户访问行为数据.
 *
 * @property int $id
 * @property int $sort 排序权重
 * @property int $ssid 所属商家
 * @property int $times 记录次数
 * @property int $unid 用户编号
 * @property string $create_time 创建时间
 * @property string $gcode 商品编号
 * @property string $update_time 更新时间
 * @property DataWemallGoods $goods
 * @class DataWemallUserActionHistory
 */
class DataWemallUserActionHistory extends AbsUser
{
    /**
     * 关联商品信息.
     */
    public function goods(): HasOne
    {
        return $this->hasOne(DataWemallGoods::class, 'code', 'gcode');
    }
}
