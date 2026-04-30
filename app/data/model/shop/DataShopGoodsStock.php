<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\Abs;

/**
 * 商城商品库存数据.
 *
 * @property int $deleted 删除状态(0未删,1已删)
 * @property int $gstock 入库数量
 * @property int $id
 * @property int $status 数据状态(1使用,0禁用)
 * @property string $batch_no 操作批量
 * @property string $create_time 创建时间
 * @property string $gcode 商品编号
 * @property string $ghash 商品哈希
 * @property string $gspec 商品规格
 * @property string $update_time 更新时间
 * @class DataShopGoodsStock
 */
class DataShopGoodsStock extends Abs {}
