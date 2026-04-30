<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\Abs;

/**
 * 商城商品标签数据.
 *
 * @property int $id
 * @property int $sort 排序权重
 * @property int $status 标签状态(1使用,0禁用)
 * @property string $create_time 创建时间
 * @property string $name 标签名称
 * @property string $remark 标签描述
 * @property string $update_time 更新时间
 * @class DataShopGoodsMark
 */
class DataShopGoodsMark extends Abs
{
    /**
     * 获取所有标签.
     */
    public static function items(): array
    {
        return static::mk()->where(['status' => 1])->order('sort desc,id desc')->column('name');
    }
}
