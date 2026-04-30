<?php

declare(strict_types=1);

namespace app\data\controller\api\shop\auth;

use app\data\controller\api\shop\Auth;
use app\data\model\shop\DataShopConfigLevel;
use app\data\model\shop\DataShopUserRebate;
use app\data\service\shop\UserRebate;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 代理返佣管理.
 * @class Rebate
 */
class Rebate extends Auth
{
    /**
     * 获取代理返佣记录.
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function get()
    {
        $query = DataShopUserRebate::mQuery()->where([
            'unid' => $this->unid, 'deleted' => 0,
        ]);
        $query->equal('type,status')->like('name|code|order_no#keys')->whereRaw('amount>0');
        $this->success('获取返佣统计', $query->order('id desc')->page(true, false, false, 15));
    }

    /**
     * 获取我的奖励.
     */
    public function prize()
    {
        [$map, $data] = [['number' => $this->levelCode], []];
        $prizes = DataShopUserRebate::mk()->group('name')->column('name');
        $rebate = DataShopConfigLevel::mk()->where($map)->value('rebate_rule', '');
        $codemap = array_merge($prizes, str2arr($rebate));
        foreach (UserRebate::prizes as $code => $prize) {
            if (in_array($code, $codemap)) {
                $data[$code] = $prize;
            }
        }
        $this->success('获取我的奖励', $data);
    }

    /**
     * 获取奖励配置.
     */
    public function prizes()
    {
        $this->success('获取系统奖励', array_values(UserRebate::prizes));
    }
}
