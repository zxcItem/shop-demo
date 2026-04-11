<?php

declare(strict_types=1);

namespace app\data\model\wemall;

use think\admin\model\SystemUser;
use think\model\relation\HasOne;

/**
 * 意见反馈数据模型.
 *
 * @property int $deleted 删除状态(0未删,1已删)
 * @property int $id
 * @property int $reply_by 回复用户
 * @property int $reply_st 回复状态
 * @property int $sort 排序权重
 * @property int $status 展示状态(1使用,0禁用)
 * @property int $sync 同步至常见问题状态(1已同步,0未同步)
 * @property int $unid 反馈用户
 * @property string $content 反馈内容
 * @property string $create_time 创建时间
 * @property string $images 反馈图片
 * @property string $phone 联系电话
 * @property string $reply 回复内容
 * @property string $reply_time 回复时间
 * @property string $update_time 更新时间
 * @property SystemUser $bind_admin
 * @class DataWemallHelpFeedback
 */
class DataWemallHelpFeedback extends AbsUser
{
    /**
     * 绑定回复用户数据.
     */
    public function bindAdmin(): HasOne
    {
        return $this->hasOne(SystemUser::class, 'id', 'reply_by')->bind([
            'reply_headimg' => 'headimg',
            'reply_username' => 'username',
            'reply_nickname' => 'nickname',
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

    /**
     * 获取回复时间.
     * @param mixed $value
     */
    public function getReplyTimeAttr($value): string
    {
        return parent::getCreateTimeAttr($value);
    }

    /**
     * 设置回复时间.
     * @param mixed $value
     */
    public function setReplyTimeAttr($value): string
    {
        return parent::setCreateTimeAttr($value);
    }
}
