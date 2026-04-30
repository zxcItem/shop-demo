<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\AbsUser;

/**
 * 常见问题数据模型.
 *
 * @property int $deleted 删除状态(0未删,1已删)
 * @property int $fid 来自反馈
 * @property int $id
 * @property int $num_er 未解决数
 * @property int $num_ok 已解决数
 * @property int $num_read 阅读次数
 * @property int $sort 排序权重
 * @property int $status 展示状态(1使用,0禁用)
 * @property string $content 问题内容
 * @property string $create_time 创建时间
 * @property string $name 问题标题
 * @property string $update_time 更新时间
 * @class DataShopHelpProblem
 */
class DataShopHelpProblem extends AbsUser {}
