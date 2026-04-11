<?php


declare(strict_types=1);


namespace app\data\model\payment;

use app\data\model\Abs;

/**
 * 用户支付参数模型.
 *
 * @property int $deleted 删除状态
 * @property int $id
 * @property int $sort 排序权重
 * @property int $status 支付状态(1使用,0禁用)
 * @property string $code 通道编号
 * @property string $content 支付参数
 * @property string $cover 支付图标
 * @property string $create_time 创建时间
 * @property string $name 支付名称
 * @property string $remark 支付说明
 * @property string $type 支付类型
 * @property string $update_time 更新时间
 * @class DataPaymentConfig
 */
class DataPaymentConfig extends Abs
{
    protected $oplogName = '商城支付配置';

    protected $oplogType = '商城支付配置';

    /**
     * 格式化数据格式.
     * @param mixed $value
     */
    public function setContentAttr($value): string
    {
        return $this->setExtraAttr($value);
    }

    /**
     * 格式化数据格式.
     * @param mixed $value
     */
    public function getContentAttr($value): array
    {
        return $this->getExtraAttr($value);
    }
}
