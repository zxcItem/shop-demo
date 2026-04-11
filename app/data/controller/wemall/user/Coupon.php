<?php

declare(strict_types=1);


namespace app\data\controller\wemall\user;

use app\data\model\account\DataAccountUser;
use app\data\model\wemall\DataWemallUserCoupon;
use think\admin\Controller;
use think\admin\helper\QueryHelper;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 用户卡券管理.
 * @class Coupon
 */
class Coupon extends Controller
{
    /**
     * 用户卡券管理.
     * @auth true
     * @menu true
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index()
    {
        DataWemallUserCoupon::mQuery()->layTable(function () {
            $this->title = '用户卡券管理';
        }, function (QueryHelper $query) {
            // 数据关联
            $query->with(['coupon', 'bindUser']);
            // 代理条件查询
            $query->like('code')->dateBetween('create_time');
            // 会员条件查询
            $db = DataAccountUser::mQuery()->like('nickname|phone#user')->db();
            if ($db->getOptions('where')) {
                $query->whereRaw("order_unid in {$db->field('id')->buildSql()}");
            }
        });
    }
}
