<?php

declare(strict_types=1);

namespace app\data\model\payment;

use app\data\model\Abs;
use app\data\model\account\DataAccountUser;
use think\model\relation\HasOne;

/**
 * 用户支付退款模型.
 *
 * @property float $refund_amount 退款金额
 * @property float $used_balance 退回余额
 * @property float $used_integral 退回积分
 * @property float $used_payment 退回金额
 * @property int $id
 * @property int $refund_status 支付状态(0未付,1已付,2取消)
 * @property int $unid 主账号编号
 * @property int $usid 子账号编号
 * @property string $code 发起支付号
 * @property string $create_time 创建时间
 * @property string $record_code 子支付编号
 * @property string $refund_account 退回账号
 * @property string $refund_notify 通知内容
 * @property string $refund_remark 退款备注
 * @property string $refund_scode 状态编码
 * @property string $refund_time 完成时间
 * @property string $refund_trade 交易编号
 * @property string $update_time 更新时间
 * @property DataAccountUser $user
 * @property DataPaymentRecord $record
 * @class DataPaymentRecord
 */
class DataPaymentRefund extends Abs
{
    /**
     * 关联用户数据.
     */
    public function user(): HasOne
    {
        return $this->hasOne(DataAccountUser::class, 'id', 'unid');
    }

    /**
     * 关联子支付订单.
     */
    public function record(): HasOne
    {
        return $this->hasOne(DataPaymentRecord::class, 'code', 'record_code');
    }

    /**
     * 格式化输出时间.
     * @param mixed $value
     */
    public function getRefundTimeAttr($value): string
    {
        return format_datetime($value);
    }

    /**
     * 格式化输入时间.
     * @param mixed $value
     */
    public function setRefundTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }
}
