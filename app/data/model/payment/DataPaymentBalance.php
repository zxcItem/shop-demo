<?php

declare(strict_types=1);

namespace app\data\model\payment;

/**
 * 用户余额模型.
 *
 * @property float $amount 操作金额
 * @property float $amount_next 操作后金额
 * @property float $amount_prev 操作前金额
 * @property int $cancel 作废状态(0未作废,1已作废)
 * @property int $create_by 系统用户
 * @property int $deleted 删除状态(0未删除,1已删除)
 * @property int $id
 * @property int $unid 账号编号
 * @property int $unlock 解锁状态(0锁定中,1已生效)
 * @property string $cancel_time 作废时间
 * @property string $code 操作编号
 * @property string $create_time 创建时间
 * @property string $deleted_time 删除时间
 * @property string $name 操作名称
 * @property string $remark 操作备注
 * @property string $unlock_time 解锁时间
 * @property string $update_time 更新时间
 * @class DataPaymentBalance
 */
class DataPaymentBalance extends DataPaymentIntegral {}
