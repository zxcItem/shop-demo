<?php

declare(strict_types=1);

namespace app\data\command;

use app\data\model\account\DataAccountUser;
use app\data\service\wemall\UserAgent;
use app\data\service\wemall\UserOrder;
use app\data\service\wemall\UserUpgrade;
use think\admin\Command;
use think\console\Input;
use think\console\Output;
use think\db\exception\DbException;

/**
 * 同步计算用户信息.
 * @class Users
 */
class Users extends Command
{
    /**
     * 指令参数配置.
     */
    public function configure()
    {
        $this->setName('xdata:mall:users')->setDescription('同步用户关联数据');
    }

    /**
     * 执行指令.
     * @throws \think\admin\Exception
     * @throws DbException
     */
    protected function execute(Input $input, Output $output)
    {
        [$total, $count] = [DataAccountUser::mk()->count(), 0];
        foreach (DataAccountUser::mk()->field('id')->order('id desc')->cursor() as $user) {
            try {
                $this->queue->message($total, ++$count, "刷新用户 [{$user['id']}] 数据...");
                UserUpgrade::upgrade(UserAgent::upgrade(UserOrder::entry(intval($user['id']))));
                UserUpgrade::recount(intval($user['id']), true);
                $this->queue->message($total, $count, "刷新用户 [{$user['id']}] 数据成功", 1);
            } catch (\Exception $exception) {
                $this->queue->message($total, $count, "刷新用户 [{$user['id']}] 数据失败, {$exception->getMessage()}", 1);
            }
        }
        $this->setQueueSuccess("此次共处理 {$total} 个刷新操作。");
    }
}
