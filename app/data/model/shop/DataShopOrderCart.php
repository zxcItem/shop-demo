<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\AbsUser;
use think\model\relation\HasOne;

/**
 * Class plugin\wemall\model\DataShopOrderCart.
 *
 * @property int $id
 * @property int $number 商品数量
 * @property int $ssid 所属商家
 * @property int $unid 用户编号
 * @property string $create_time 创建时间
 * @property string $gcode 商品编号
 * @property string $ghash 规格哈希
 * @property string $gspec 商品规格
 * @property string $update_time 更新时间
 * @property DataShopGoods $goods
 * @property DataShopGoodsItem $specs
 */
class DataShopOrderCart extends AbsUser
{
    /**
     * 关联产品数据.
     */
    public function goods(): HasOne
    {
        return $this->hasOne(DataShopGoods::class, 'code', 'gcode');
    }

    /**
     * 关联规格数据.
     */
    public function specs(): HasOne
    {
        return $this->hasOne(DataShopGoodsItem::class, 'ghash', 'ghash');
    }
}
