<?php

declare(strict_types=1);

namespace app\data\controller\marketing\integral;


use app\data\controller\marketing\Base;

/**
 * 积分兑换记录
 * @class Order
 */
class Order extends Base
{
    protected $menus = [
        ['title' => '商城设置', 'url' => 'data/marketing.integral.config/index'],
        ['title' => '商品管理', 'url' => 'data/marketing.integral.goods/index'],
        ['title' => '兑换记录', 'url' => 'data/marketing.integral.order/index'],
    ];

    /**
     * 兑换记录
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '兑换记录';
        $this->fetch();
    }
}
