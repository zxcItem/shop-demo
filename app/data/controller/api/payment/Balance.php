<?php

declare(strict_types=1);

namespace app\data\controller\api\payment;

use app\data\controller\api\Auth;
use app\data\model\payment\DataPaymentBalance;
use think\admin\helper\QueryHelper;

/**
 * 余额数据接口.
 * @class Balance
 */
class Balance extends Auth
{
    /**
     * 获取余额记录.
     */
    public function get()
    {
        DataPaymentBalance::mQuery(null, function (QueryHelper $query) {
            $query->where(['unid' => $this->unid, 'deleted' => 0, 'cancel' => 0])->order('id desc');
            $this->success('获取余额记录！', $query->page(intval(input('page', 1)), false, false, 20));
        });
    }
}
