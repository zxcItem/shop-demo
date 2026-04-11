<?php

declare(strict_types=1);

namespace app\data\model\wemall;

use app\data\model\AbsUser;

/**
 * 用户签到数据.
 *
 * @property float $balance 赠送余额
 * @property float $integral 赠送积分
 * @property int $deleted 删除状态(0未删除,1已删除)
 * @property int $id
 * @property int $status 生效状态(0未生效,1已生效)
 * @property int $timed 奖励天数
 * @property int $times 连续天数
 * @property int $unid 用户UNID
 * @property string $create_time 创建时间
 * @property string $date 签到日期
 * @property string $update_time 更新时间
 * @class DataWemallUserCheckin
 */
class DataWemallUserCheckin extends AbsUser
{
    /**
     * 配置存储名称.
     * @var string
     */
    public static $ckcfg = 'plugin.normal.event.checkin';
}
