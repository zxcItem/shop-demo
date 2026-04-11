<?php

declare(strict_types=1);

namespace app\data\command;

use app\data\model\account\DataAccountUser;
use app\data\service\payment\Balance as BalanceAlias;
use app\data\service\payment\Integral as IntegralAlias;
use think\admin\Queue;
use think\db\exception\DbException;

/**
 * 刷新用户余额和积分.
 * @class Recount
 */
class Recount extends Queue
{
    /**
     * @throws \think\admin\Exception
     * @throws DbException
     */
    public function execute(array $data = [])
    {
        $this->balance()->setQueueSuccess('刷新用户余额及积分完成！');
    }

    /**
     * 刷新用户余额.
     * @return static
     * @throws \think\admin\Exception
     * @throws DbException
     */
    private function balance(): Recount
    {
        [$total, $count] = [DataAccountUser::mk()->count(), 0];
        foreach (DataAccountUser::mk()->field('id')->cursor() as $user) {
            try {
                $nick = $user['username'] ?: ($user['nickname'] ?: $user['email']);
                $this->setQueueMessage($total, ++$count, "开始刷新用户 [{$user['id']} {$nick}] 余额及积分");
                BalanceAlias::recount(intval($user['id'])) && IntegralAlias::recount(intval($user['id']));
                $this->setQueueMessage($total, $count, "刷新用户 [{$user['id']} {$nick}] 余额及积分", 1);
            } catch (\Exception $exception) {
                $this->setQueueMessage($total, $count, "刷新用户 [{$user['id']} {$nick}] 余额及积分失败, {$exception->getMessage()}", 1);
            }
        }
        return $this;
    }
}
