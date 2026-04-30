<?php

declare(strict_types=1);

namespace app\data\model\shop;

use app\data\model\AbsUser;
use think\model\relation\HasOne;

/**
 * 用户评论数据模型.
 *
 * @property float $rate 评论分数
 * @property int $deleted 删除状态(0未删,1已删)
 * @property int $id
 * @property int $ssid 所属商家
 * @property int $status 评论状态(0隐藏,1显示)
 * @property int $unid 用户编号
 * @property string $code 评论编号
 * @property string $content 评论内容
 * @property string $create_time 创建时间
 * @property string $gcode 商品编号
 * @property string $ghash 商品哈希
 * @property string $images 评论图片
 * @property string $order_no 订单单号
 * @property string $update_time 更新时间
 * @property DataShopGoods $bind_goods
 * @property DataShopGoods $goods
 * @property DataShopOrder $orderinfo
 * @class DataShopUserActionComment
 */
class DataShopUserActionComment extends AbsUser
{
    /**
     * 关联商品信息.
     */
    public function goods(): HasOne
    {
        return $this->hasOne(DataShopGoods::class, 'code', 'gcode');
    }

    /**
     * 关联订单数据.
     */
    public function orderinfo(): HasOne
    {
        return $this->hasOne(DataShopOrder::class, 'order_no', 'order_no');
    }

    /**
     * 绑定商品信息.
     */
    public function bindGoods(): HasOne
    {
        return $this->goods()->bind([
            'goods_name' => 'name',
            'goods_code' => 'code',
        ]);
    }

    /**
     * 格式化图片格式.
     * @param mixed $value
     */
    public function getImagesAttr($value): array
    {
        return str2arr($value ?? '', '|');
    }
}
