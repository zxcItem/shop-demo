<?php

declare(strict_types=1);

namespace app\data\controller\payment;

use app\data\model\account\DataAccountUser;
use app\data\model\payment\DataPaymentRefund;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 支付退款管理.
 * @class Refund
 */
class Refund extends Controller
{
    /**
     * 支付退款管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        $this->mode = $this->get['open_type'] ?? 'index';
        DataPaymentRefund::mQuery()->layTable(function () {
            if ($this->mode === 'index') {
                $this->title = '支付行为管理';
            }
        }, static function (QueryHelper $query) {
            $query->with(['user', 'record'])->like('order_no|order_name#orderinfo')->dateBetween('create_time');
            $db = DataAccountUser::mQuery()->like('email|nickname|username|phone#userinfo')->db();
            if ($db->getOptions('where')) {
                $query->whereRaw("unid in {$db->field('id')->buildSql()}");
            }
        });
    }
}
