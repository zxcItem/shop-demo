<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\AbsUser;
use think\model\relation\HasOne;

/**
 * 用户收藏行为数据.
 *
 * @property int $id
 * @property int $sort 排序权重
 * @property int $times 记录次数
 * @property int $unid 用户编号
 * @property string $create_time 创建时间
 * @property string $gcode 商品编号
 * @property string $update_time 更新时间
 * @property DataShopGoods $goods
 * @class DataShopUserActionCollect
 */
class DataShopUserActionCollect extends AbsUser
{
    /**
     * 关联商品信息.
     */
    public function goods(): HasOne
    {
        return $this->hasOne(DataShopGoods::class, 'code', 'gcode');
    }
}
