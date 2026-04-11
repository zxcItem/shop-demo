<?php

declare(strict_types=1);


namespace app\data\controller\api\payment;

use app\data\controller\api\Auth;
use app\data\model\payment\DataPaymentIntegral;
use think\admin\helper\QueryHelper;

/**
 * 积分数据接口.
 * @class Integral
 */
class Integral extends Auth
{
    /**
     * 获取余额记录.
     */
    public function get()
    {
        DataPaymentIntegral::mQuery(null, function (QueryHelper $query) {
            $query->where(['unid' => $this->unid, 'deleted' => 0, 'cancel' => 0])->order('id desc');
            $this->success('获取积分记录！', $query->page(intval(input('page', 1)), false, false, 20));
        });
    }
}
