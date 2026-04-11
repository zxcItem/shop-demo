<?php

declare(strict_types=1);

namespace app\data\model\wemall;

use app\data\model\Abs;

/**
 * 商城快递公司数据.
 *
 * @property int $deleted 删除状态(1已删,0未删)
 * @property int $id
 * @property int $sort 排序权重
 * @property int $status 激活状态(0无效,1有效)
 * @property string $code 公司代码
 * @property string $create_time 创建时间
 * @property string $name 公司名称
 * @property string $remark 公司描述
 * @property string $update_time 更新时间
 * @class DataWemallExpressCompany
 */
class DataWemallExpressCompany extends Abs
{
    /**
     * 获取快递公司数据.
     */
    public static function items(): array
    {
        $map = ['status' => 1, 'deleted' => 0];
        return self::mk()->where($map)->order('sort desc,id desc')->column('name', 'code');
    }
}
