<?php

declare(strict_types=1);

namespace app\data\controller\api\shop\auth;

use app\data\controller\api\shop\Auth;
use app\data\service\shop\UserOrder;
use app\data\service\shop\UserRebate;
use app\data\service\shop\UserUpgrade;
use think\admin\Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 会员中心.
 * @class Center
 */
class Center extends Auth
{
    /**
     * 获取会员资料.
     * @throws Exception
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get()
    {
        $user = $this->account->user()->toArray();
        if (empty($user['extra']['level_name'])) {
            UserUpgrade::recount($this->unid);
        }
        $this->success('获取资料成功！', [
            'account' => $this->account->get(false, true),
            'relation' => $this->relation->toArray(),
        ]);
    }

    /**
     * 获取会员等级.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function levels()
    {
        $this->success('获取会员等级！', UserRebate::levels());
    }

    /**
     * 获取会员折扣.
     */
    public function discount()
    {
        $data = $this->_vali(['discount.require' => '折扣不能为空！']);
        [, $rate] = UserOrder::discount(intval($data['discount']), $this->levelCode);
        $this->success('获取会员折扣！', ['rate' => strval($rate)]);
    }
}
