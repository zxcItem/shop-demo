<?php

declare(strict_types=1);

namespace app\data\model\wemall;

use app\data\model\AbsUser;

/**
 * 用户搜索行为数据.
 *
 * @property int $id
 * @property int $sort 排序权重
 * @property int $times 搜索次数
 * @property int $unid 用户编号
 * @property string $create_time 创建时间
 * @property string $keys 关键词
 * @property string $update_time 更新时间
 * @class DataWemallUserActionSearch
 */
class DataWemallUserActionSearch extends AbsUser {}
