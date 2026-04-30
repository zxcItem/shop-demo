<?php

declare(strict_types=1);

namespace app\data\controller\marketing\coupon;

use app\data\controller\marketing\Base;
use app\data\model\shop\DataShopUserCoupon;
use think\admin\helper\QueryHelper;

/**
 * 优惠券领取记录
 * @class Record
 */
class Record extends Base
{
    protected $menus = [
        ['title' => '营销设置', 'url' => 'data/marketing.coupon.config/index'],
        ['title' => '优惠券列表', 'url' => 'data/marketing.coupon.coupon/index'],
        ['title' => '领取记录', 'url' => 'data/marketing.coupon.record/index'],
    ];

    /**
     * 领取记录
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '领取记录';
        DataShopUserCoupon::mQuery()->layTable(function () {
        }, function (QueryHelper $query) {
            $query->like('coupon_name#name')->equal('status');
            $query->dateBetween('create_time');
        });
    }
}
