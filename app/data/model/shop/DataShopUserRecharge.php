<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\AbsUser;

/**
 * 会员充值数据.
 *
 * @property float $amount 操作金额
 * @property int $create_by 系统用户
 * @property int $deleted 删除状态(0未删除,1已删除)
 * @property int $deleted_by 系统用户
 * @property int $id
 * @property int $unid 账号编号
 * @property string $code 操作编号
 * @property string $create_time 创建时间
 * @property string $deleted_time 删除时间
 * @property string $name 操作名称
 * @property string $remark 操作备注
 * @property string $update_time 更新时间
 * @class DataShopUserRecharge
 */
class DataShopUserRecharge extends AbsUser {}
